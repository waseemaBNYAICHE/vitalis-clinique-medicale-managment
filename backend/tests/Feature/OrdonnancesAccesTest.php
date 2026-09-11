<?php

namespace Tests\Feature;

use App\Models\Consultation;
use App\Models\LigneOrdonnance;
use App\Models\Ordonnance;
use App\Models\Patient;
use App\Models\RendezVous;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * SCRUM-564 / SCRUM-566 - Acces aux ordonnances.
 *
 * Les routes ajoutees par SCRUM-68/70/71 n'exigeaient qu'un compte valide.
 * Comme /api/register est publique et attribue le role 'patient', n'importe
 * qui pouvait s'inscrire puis lire, creer, modifier et SUPPRIMER l'ordonnance
 * de n'importe quel patient, ainsi que consulter son historique
 * medicamenteux en changeant l'identifiant dans l'URL.
 *
 * Ces tests figent la fermeture de cette porte : permission d'abord,
 * cloisonnement du dossier ensuite.
 */
class OrdonnancesAccesTest extends TestCase
{
    use RefreshDatabase;

    private function utilisateur(string $role, array $attributs = []): User
    {
        return User::factory()->create(['role' => $role] + $attributs);
    }

    private function creerMedecin(string $nom): int
    {
        $idSpecialite = DB::table('specialites')->insertGetId([
            'nom_specialite' => 'Specialite '.$nom.random_int(100, 999),
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_specialite');

        return DB::table('medecins')->insertGetId([
            'matricule' => 'MAT'.random_int(100000, 999999),
            'nom' => $nom,
            'prenom' => 'Docteur',
            'telephone' => '0600000000',
            'email' => strtolower($nom).random_int(1000, 9999).'@example.com',
            'date_embauche' => '2020-01-01',
            'tarif_consultation' => 300,
            'id_specialite' => $idSpecialite,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_medecin');
    }

    private function creerPatient(string $prenom): Patient
    {
        return Patient::create([
            'nom' => 'Dossier',
            'prenom' => $prenom,
            'date_naissance' => '1990-01-01',
            'sexe' => 'F',
            'cin' => 'CIN'.random_int(100000, 999999),
            'telephone' => '0600000000',
            'email' => strtolower($prenom).random_int(1000, 9999).'@example.com',
            'groupe_sanguin' => 'O+',
        ]);
    }

    private function creerMedicament(): int
    {
        return DB::table('medicaments')->insertGetId([
            'nom_medicament' => 'Medicament'.random_int(100, 999),
            'forme' => 'comprime',
            'dosage' => '500mg',
            'fabricant' => 'Labo',
            'description' => 'Description',
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_medicament');
    }

    /**
     * Ordonnance complete rattachee a un patient et a un medecin.
     *
     * @return array{ordonnance: Ordonnance, ligne: LigneOrdonnance, consultation: Consultation}
     */
    private function creerOrdonnance(int $idPatient, int $idMedecin, string $instructions = 'Traitement'): array
    {
        $rendezVous = RendezVous::create([
            'date_rendez_vous' => now()->toDateString(),
            'heure_debut' => '09:00:00',
            'heure_fin' => '09:30:00',
            'motif' => 'Motif',
            'statut' => 'confirme',
            'id_patient' => $idPatient,
            'id_medecin' => $idMedecin,
        ]);

        $consultation = Consultation::create([
            'motif' => 'Motif',
            'diagnostic' => 'Diagnostic',
            'id_rendez_vous' => $rendezVous->id_rendez_vous,
        ]);

        $ordonnance = Ordonnance::create([
            'date_ordonnance' => now()->toDateString(),
            'instructions_generales' => $instructions,
            'duree_traitement' => '7 jours',
            'type' => 'medicament',
            'id_consultation' => $consultation->id_consultation,
        ]);

        $ligne = LigneOrdonnance::create([
            'dosologie' => '1 comprime',
            'frequence' => '3 fois par jour',
            'duree' => '7 jours',
            'quantite' => 21,
            'instructions' => 'Pendant les repas',
            'id_ordonnance' => $ordonnance->id_ordonnance,
            'id_medicament' => $this->creerMedicament(),
        ]);

        return compact('ordonnance', 'ligne', 'consultation');
    }

    // ------------------------------------------------ 401 sans jeton

    /**
     * @return array<string, array{string, string}>
     */
    public static function routesDesOrdonnances(): array
    {
        return [
            'liste' => ['get', '/api/ordonnances'],
            'detail' => ['get', '/api/ordonnances/1'],
            'creation' => ['post', '/api/ordonnances'],
            'modification' => ['put', '/api/ordonnances/1'],
            'suppression' => ['delete', '/api/ordonnances/1'],
            'lignes' => ['get', '/api/ordonnances/1/lignes'],
            'ajout de medicament' => ['post', '/api/ordonnances/1/lignes'],
            'modification de ligne' => ['put', '/api/lignes-ordonnance/1'],
            'suppression de ligne' => ['delete', '/api/lignes-ordonnance/1'],
            'historique patient' => ['get', '/api/patients/1/ordonnances/historique'],
        ];
    }

    #[DataProvider('routesDesOrdonnances')]
    public function test_un_visiteur_non_authentifie_est_refuse(string $methode, string $uri): void
    {
        $this->json(strtoupper($methode), $uri)->assertStatus(401);
    }

    /**
     * La secretaire n'a aucun acces aux donnees cliniques (SCRUM-524) : elle
     * ne detient aucune permission sur les ordonnances.
     */
    #[DataProvider('routesDesOrdonnances')]
    public function test_la_secretaire_na_aucun_acces_aux_ordonnances(string $methode, string $uri): void
    {
        Sanctum::actingAs($this->utilisateur('secretaire'));

        $this->json(strtoupper($methode), $uri)->assertStatus(403);
    }

    // -------------------------------------- cloisonnement cote patient

    /**
     * Le coeur du ticket : un patient ne doit atteindre que SON ordonnance.
     * Avant correction, toutes ces requetes repondaient 200.
     */
    public function test_un_patient_natteint_pas_lordonnance_dun_autre(): void
    {
        $idMedecin = $this->creerMedecin('Alami');
        $sien = $this->creerPatient('Sien');
        $autre = $this->creerPatient('Autre');

        $dossierAutre = $this->creerOrdonnance($autre->id_patient, $idMedecin, 'Traitement confidentiel');

        Sanctum::actingAs($this->utilisateur('patient', ['id_patient' => $sien->id_patient]));

        $idOrdonnance = $dossierAutre['ordonnance']->id_ordonnance;

        $this->getJson("/api/ordonnances/{$idOrdonnance}")->assertStatus(403);
        $this->getJson("/api/ordonnances/{$idOrdonnance}/lignes")->assertStatus(403);
    }

    public function test_un_patient_atteint_sa_propre_ordonnance(): void
    {
        $idMedecin = $this->creerMedecin('Alami');
        $patient = $this->creerPatient('Sien');
        $dossier = $this->creerOrdonnance($patient->id_patient, $idMedecin);

        Sanctum::actingAs($this->utilisateur('patient', ['id_patient' => $patient->id_patient]));

        $idOrdonnance = $dossier['ordonnance']->id_ordonnance;

        $this->getJson("/api/ordonnances/{$idOrdonnance}")->assertStatus(200);
        $this->getJson("/api/ordonnances/{$idOrdonnance}/lignes")->assertStatus(200);
    }

    /**
     * La liste ne doit pas non plus servir de contournement : elle renvoyait
     * Ordonnance::all(), soit toute la clinique.
     */
    public function test_la_liste_ne_montre_que_les_ordonnances_du_perimetre(): void
    {
        $idMedecin = $this->creerMedecin('Alami');
        $sien = $this->creerPatient('Sien');
        $autre = $this->creerPatient('Autre');

        $this->creerOrdonnance($sien->id_patient, $idMedecin, 'Traitement du demandeur');
        $this->creerOrdonnance($autre->id_patient, $idMedecin, 'Traitement confidentiel');

        Sanctum::actingAs($this->utilisateur('patient', ['id_patient' => $sien->id_patient]));

        $reponse = $this->getJson('/api/ordonnances');

        $reponse->assertStatus(200)->assertJsonCount(1, 'ordonnances');
        $this->assertStringContainsString('Traitement du demandeur', $reponse->getContent());
        $this->assertStringNotContainsString('Traitement confidentiel', $reponse->getContent());
    }

    /**
     * L'historique prenait l'identifiant du patient dans l'URL sans le
     * verifier : le changer suffisait a lire l'historique medicamenteux d'un
     * autre.
     */
    public function test_lhistorique_dun_autre_patient_reste_vide(): void
    {
        $idMedecin = $this->creerMedecin('Alami');
        $sien = $this->creerPatient('Sien');
        $autre = $this->creerPatient('Autre');

        $this->creerOrdonnance($autre->id_patient, $idMedecin, 'Traitement confidentiel');

        Sanctum::actingAs($this->utilisateur('patient', ['id_patient' => $sien->id_patient]));

        $reponse = $this->getJson("/api/patients/{$autre->id_patient}/ordonnances/historique");

        $reponse->assertStatus(200)->assertJsonCount(0, 'historique');
        $this->assertStringNotContainsString('Traitement confidentiel', $reponse->getContent());
    }

    public function test_un_patient_consulte_son_propre_historique(): void
    {
        $idMedecin = $this->creerMedecin('Alami');
        $patient = $this->creerPatient('Sien');
        $this->creerOrdonnance($patient->id_patient, $idMedecin, 'Mon traitement');

        Sanctum::actingAs($this->utilisateur('patient', ['id_patient' => $patient->id_patient]));

        $this->getJson("/api/patients/{$patient->id_patient}/ordonnances/historique")
            ->assertStatus(200)
            ->assertJsonCount(1, 'historique');
    }

    // -------------------------------------- cloisonnement cote medecin

    public function test_un_medecin_natteint_pas_lordonnance_dun_confrere(): void
    {
        $idMedecinA = $this->creerMedecin('Alami');
        $idMedecinB = $this->creerMedecin('Bennani');
        $patient = $this->creerPatient('Patient');

        $dossierDeB = $this->creerOrdonnance($patient->id_patient, $idMedecinB);

        Sanctum::actingAs($this->utilisateur('medecin', ['id_medecin' => $idMedecinA]));

        $idOrdonnance = $dossierDeB['ordonnance']->id_ordonnance;

        $this->getJson("/api/ordonnances/{$idOrdonnance}")->assertStatus(403);
        $this->putJson("/api/ordonnances/{$idOrdonnance}", ['type' => 'modifie'])->assertStatus(403);
    }

    public function test_un_medecin_gere_les_ordonnances_de_ses_patients(): void
    {
        $idMedecin = $this->creerMedecin('Alami');
        $patient = $this->creerPatient('Patient');
        $dossier = $this->creerOrdonnance($patient->id_patient, $idMedecin);

        Sanctum::actingAs($this->utilisateur('medecin', ['id_medecin' => $idMedecin]));

        $idOrdonnance = $dossier['ordonnance']->id_ordonnance;

        $this->getJson("/api/ordonnances/{$idOrdonnance}")->assertStatus(200);
        $this->putJson("/api/ordonnances/{$idOrdonnance}", ['type' => 'renouvellement'])
            ->assertStatus(200);
    }

    // ----------------------------------------------- ecriture et suppression

    /**
     * Un patient detient ORDONNANCES_READ, jamais CREATE ni UPDATE : il ne
     * doit pas pouvoir se prescrire un traitement, meme sur son dossier.
     */
    public function test_un_patient_ne_peut_ni_creer_ni_modifier_une_ordonnance(): void
    {
        $idMedecin = $this->creerMedecin('Alami');
        $patient = $this->creerPatient('Sien');
        $dossier = $this->creerOrdonnance($patient->id_patient, $idMedecin);

        Sanctum::actingAs($this->utilisateur('patient', ['id_patient' => $patient->id_patient]));

        $idOrdonnance = $dossier['ordonnance']->id_ordonnance;
        $idLigne = $dossier['ligne']->id_ligne_ordonnance;

        $this->postJson('/api/ordonnances', [
            'date_ordonnance' => now()->toDateString(),
            'instructions_generales' => 'Auto-prescription',
            'duree_traitement' => '30 jours',
            'type' => 'medicament',
            'id_consultation' => $dossier['consultation']->id_consultation,
        ])->assertStatus(403);

        $this->putJson("/api/ordonnances/{$idOrdonnance}", ['type' => 'autre'])->assertStatus(403);
        $this->deleteJson("/api/lignes-ordonnance/{$idLigne}")->assertStatus(403);

        $this->assertDatabaseMissing('ordonnances', ['instructions_generales' => 'Auto-prescription']);
    }

    /**
     * La suppression d'un dossier medical reste reservee a l'administrateur,
     * comme partout ailleurs (SCRUM-524).
     */
    public function test_seul_ladministrateur_supprime_une_ordonnance(): void
    {
        $idMedecin = $this->creerMedecin('Alami');
        $patient = $this->creerPatient('Patient');

        foreach (['medecin', 'infirmier', 'patient'] as $role) {
            $dossier = $this->creerOrdonnance($patient->id_patient, $idMedecin);
            $id = $dossier['ordonnance']->id_ordonnance;

            $this->app['auth']->forgetGuards();
            Sanctum::actingAs($this->utilisateur($role, [
                'id_medecin' => $role === 'medecin' ? $idMedecin : null,
                'id_patient' => $role === 'patient' ? $patient->id_patient : null,
            ]));

            $this->deleteJson("/api/ordonnances/{$id}")->assertStatus(403);
            $this->assertDatabaseHas('ordonnances', ['id_ordonnance' => $id]);
        }

        $dossier = $this->creerOrdonnance($patient->id_patient, $idMedecin);
        $id = $dossier['ordonnance']->id_ordonnance;

        $this->app['auth']->forgetGuards();
        Sanctum::actingAs($this->utilisateur('administrateur'));

        // Une ordonnance qui contient encore des medicaments n'est pas
        // supprimable : voir le test suivant.
        $dossier['ligne']->delete();

        $this->deleteJson("/api/ordonnances/{$id}")->assertStatus(200);
        $this->assertDatabaseMissing('ordonnances', ['id_ordonnance' => $id]);
    }

    /**
     * SCRUM-566 : la contrainte de cle etrangere des lignes remontait en
     * PDOException, donc en 500 - avec la trace SQL quand APP_DEBUG est
     * actif. Une dependance qui empeche la suppression est un conflit metier.
     */
    public function test_supprimer_une_ordonnance_non_vide_repond_409_et_non_500(): void
    {
        $idMedecin = $this->creerMedecin('Alami');
        $patient = $this->creerPatient('Patient');
        $dossier = $this->creerOrdonnance($patient->id_patient, $idMedecin);
        $id = $dossier['ordonnance']->id_ordonnance;

        Sanctum::actingAs($this->utilisateur('administrateur'));

        $reponse = $this->deleteJson("/api/ordonnances/{$id}");

        $reponse->assertStatus(409);
        $this->assertDatabaseHas('ordonnances', ['id_ordonnance' => $id]);

        // Aucun detail technique ne doit filtrer dans la reponse.
        $corps = $reponse->getContent();
        $this->assertStringNotContainsString('SQLSTATE', $corps);
        $this->assertStringNotContainsString('PDOException', $corps);
        $this->assertStringNotContainsString('FOREIGN KEY', $corps);
    }

    // ------------------------------------------------------- validation

    /**
     * SCRUM-564 : les entrees etaient reprises telles quelles. Un
     * id_consultation inexistant partait en base et provoquait une erreur SQL
     * au lieu d'un 422.
     */
    public function test_la_creation_valide_ses_entrees(): void
    {
        Sanctum::actingAs($this->utilisateur('administrateur'));

        $this->postJson('/api/ordonnances', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['date_ordonnance', 'id_consultation']);

        $this->postJson('/api/ordonnances', [
            'date_ordonnance' => now()->toDateString(),
            'instructions_generales' => 'Instructions',
            'duree_traitement' => '7 jours',
            'type' => 'medicament',
            'id_consultation' => 999999,
        ])->assertStatus(422)->assertJsonValidationErrors('id_consultation');
    }

    public function test_lajout_dun_medicament_valide_ses_entrees(): void
    {
        $idMedecin = $this->creerMedecin('Alami');
        $patient = $this->creerPatient('Patient');
        $dossier = $this->creerOrdonnance($patient->id_patient, $idMedecin);
        $id = $dossier['ordonnance']->id_ordonnance;

        Sanctum::actingAs($this->utilisateur('administrateur'));

        $this->postJson("/api/ordonnances/{$id}/lignes", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['dosologie', 'id_medicament']);

        $this->postJson("/api/ordonnances/{$id}/lignes", [
            'dosologie' => '1 comprime',
            'frequence' => '2 fois par jour',
            'duree' => '5 jours',
            'quantite' => 10,
            'id_medicament' => 999999,
        ])->assertStatus(422)->assertJsonValidationErrors('id_medicament');
    }

    /**
     * SCRUM-563 : meme defaut sur le referentiel. Un medecin encore rattache
     * a des rendez-vous, et une specialite encore portee par un medecin,
     * faisaient remonter la contrainte de cle etrangere en 500.
     */
    public function test_supprimer_un_medecin_ou_une_specialite_utilise_repond_409(): void
    {
        $idMedecin = $this->creerMedecin('Alami');
        $patient = $this->creerPatient('Patient');
        $this->creerOrdonnance($patient->id_patient, $idMedecin);

        $idSpecialite = DB::table('medecins')
            ->where('id_medecin', $idMedecin)
            ->value('id_specialite');

        Sanctum::actingAs($this->utilisateur('administrateur'));

        foreach ([
            "/api/medecins/{$idMedecin}",
            "/api/specialites/{$idSpecialite}",
        ] as $uri) {
            $reponse = $this->deleteJson($uri);

            $this->assertSame(409, $reponse->status(), "{$uri} devrait repondre 409, pas 500");

            $corps = $reponse->getContent();
            $this->assertStringNotContainsString('SQLSTATE', $corps);
            $this->assertStringNotContainsString('PDOException', $corps);
            $this->assertStringNotContainsString('FOREIGN KEY', $corps);
        }

        $this->assertDatabaseHas('medecins', ['id_medecin' => $idMedecin]);
    }

    // ------------------------- SCRUM-567 : bornes sur les entrees

    /**
     * Les champs du referentiel n'avaient aucune borne alors que les colonnes
     * sont des VARCHAR(255) : une valeur surdimensionnee passait la validation
     * et n'echouait qu'en base, en 500 sur PostgreSQL. Meme borne que celle
     * posee sur l'authentification par SCRUM-511.
     */
    public function test_les_entrees_du_referentiel_sont_bornees(): void
    {
        Sanctum::actingAs($this->utilisateur('administrateur'));

        $idSpecialite = DB::table('medecins')->value('id_specialite')
            ?? DB::table('specialites')->insertGetId([
                'nom_specialite' => 'Specialite',
                'created_at' => now(),
                'updated_at' => now(),
            ], 'id_specialite');

        $this->postJson('/api/medecins', [
            'matricule' => str_repeat('M', 300),
            'nom' => str_repeat('N', 300),
            'prenom' => str_repeat('P', 300),
            'telephone' => str_repeat('0', 300),
            'email' => str_repeat('e', 300).'@example.com',
            'date_embauche' => '2020-01-01',
            'tarif_consultation' => 300,
            'id_specialite' => $idSpecialite,
        ])->assertStatus(422)->assertJsonValidationErrors([
            'matricule', 'nom', 'prenom', 'telephone', 'email',
        ]);

        $this->postJson('/api/specialites', [
            'nom_specialite' => str_repeat('S', 300),
            'description' => str_repeat('D', 5000),
        ])->assertStatus(422)->assertJsonValidationErrors(['nom_specialite', 'description']);
    }

    /**
     * Le tarif est un DECIMAL(10,2) : une valeur negative ou hors capacite
     * n'a pas de sens et depasse la colonne.
     */
    public function test_le_tarif_de_consultation_est_borne(): void
    {
        Sanctum::actingAs($this->utilisateur('administrateur'));

        $idSpecialite = DB::table('specialites')->insertGetId([
            'nom_specialite' => 'Cardiologie',
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_specialite');

        $base = [
            'matricule' => 'MAT123456',
            'nom' => 'Alami',
            'prenom' => 'Docteur',
            'telephone' => '0600000000',
            'email' => 'tarif@example.com',
            'date_embauche' => '2020-01-01',
            'id_specialite' => $idSpecialite,
        ];

        $this->postJson('/api/medecins', $base + ['tarif_consultation' => -100])
            ->assertStatus(422)->assertJsonValidationErrors('tarif_consultation');

        $this->postJson('/api/medecins', $base + ['tarif_consultation' => 999999999999])
            ->assertStatus(422)->assertJsonValidationErrors('tarif_consultation');

        $this->postJson('/api/medecins', $base + ['tarif_consultation' => 300])
            ->assertStatus(201);
    }

    // ------------------------- SCRUM-568 : acces direct par identifiant

    /**
     * L'oracle d'existence. findOrFail() repondait 404 pour un identifiant
     * inexistant et 403 pour une ordonnance appartenant a autrui : la
     * difference suffisait a enumerer les ordonnances reellement presentes
     * dans la clinique en faisant varier l'identifiant.
     */
    public function test_un_role_restreint_ne_distingue_pas_absente_de_pas_la_sienne(): void
    {
        $idMedecin = $this->creerMedecin('Alami');
        $sien = $this->creerPatient('Sien');
        $autre = $this->creerPatient('Autre');

        $dossierAutre = $this->creerOrdonnance($autre->id_patient, $idMedecin);
        $idExistante = $dossierAutre['ordonnance']->id_ordonnance;

        Sanctum::actingAs($this->utilisateur('patient', ['id_patient' => $sien->id_patient]));

        $surExistante = $this->getJson("/api/ordonnances/{$idExistante}");
        $surInexistante = $this->getJson('/api/ordonnances/999999');

        $this->assertSame(403, $surExistante->status());
        $this->assertSame(
            403,
            $surInexistante->status(),
            'Un identifiant inexistant ne doit pas se distinguer d un dossier interdit.'
        );
        $this->assertSame($surExistante->json('message'), $surInexistante->json('message'));
    }

    public function test_un_medecin_ne_distingue_pas_non_plus_les_deux_cas(): void
    {
        $idMedecinA = $this->creerMedecin('Alami');
        $idMedecinB = $this->creerMedecin('Bennani');
        $patient = $this->creerPatient('Patient');

        $dossierDeB = $this->creerOrdonnance($patient->id_patient, $idMedecinB);
        $idExistante = $dossierDeB['ordonnance']->id_ordonnance;

        Sanctum::actingAs($this->utilisateur('medecin', ['id_medecin' => $idMedecinA]));

        $this->assertSame(403, $this->getJson("/api/ordonnances/{$idExistante}")->status());
        $this->assertSame(403, $this->getJson('/api/ordonnances/999999')->status());
    }

    /**
     * Un role au perimetre global garde un 404 : "introuvable" est pour lui
     * une information legitime, et la masquer nuirait au diagnostic.
     */
    public function test_un_role_global_recoit_bien_404_sur_un_identifiant_inexistant(): void
    {
        Sanctum::actingAs($this->utilisateur('administrateur'));

        $this->getJson('/api/ordonnances/999999')->assertStatus(404);
        $this->getJson('/api/ordonnances/999999/lignes')->assertStatus(404);
    }

    /**
     * Acces direct a une LIGNE par son identifiant, sans passer par son
     * ordonnance : c'est la route la plus exposee a une manipulation
     * d'identifiant, et le cloisonnement doit y jouer aussi.
     */
    public function test_un_medecin_natteint_pas_la_ligne_dun_confrere(): void
    {
        $idMedecinA = $this->creerMedecin('Alami');
        $idMedecinB = $this->creerMedecin('Bennani');
        $patient = $this->creerPatient('Patient');

        $dossierDeB = $this->creerOrdonnance($patient->id_patient, $idMedecinB);
        $idLigne = $dossierDeB['ligne']->id_ligne_ordonnance;

        Sanctum::actingAs($this->utilisateur('medecin', ['id_medecin' => $idMedecinA]));

        $this->putJson("/api/lignes-ordonnance/{$idLigne}", ['duree' => '30 jours'])
            ->assertStatus(403);
        $this->deleteJson("/api/lignes-ordonnance/{$idLigne}")->assertStatus(403);

        $this->assertDatabaseHas('ligne_ordonnances', [
            'id_ligne_ordonnance' => $idLigne,
            'duree' => '7 jours',
        ]);
    }

    public function test_un_medecin_gere_les_lignes_de_ses_propres_ordonnances(): void
    {
        $idMedecin = $this->creerMedecin('Alami');
        $patient = $this->creerPatient('Patient');
        $dossier = $this->creerOrdonnance($patient->id_patient, $idMedecin);
        $idLigne = $dossier['ligne']->id_ligne_ordonnance;

        Sanctum::actingAs($this->utilisateur('medecin', ['id_medecin' => $idMedecin]));

        $this->putJson("/api/lignes-ordonnance/{$idLigne}", ['duree' => '30 jours'])
            ->assertStatus(200);
    }

    // ------------------------------------------- scenario d attaque complet

    /**
     * Le chemin complet, joue de bout en bout : inscription publique, puis
     * tentative d'acces au traitement d'un patient de la clinique.
     */
    public function test_un_compte_cree_librement_natteint_aucune_ordonnance(): void
    {
        $idMedecin = $this->creerMedecin('Alami');
        $patient = $this->creerPatient('Victime');
        $dossier = $this->creerOrdonnance($patient->id_patient, $idMedecin, 'Traitement confidentiel');

        $this->postJson('/api/register', [
            'name' => 'Visiteur',
            'email' => 'visiteur@example.com',
            'password' => 'MotDePasse123',
            'password_confirmation' => 'MotDePasse123',
        ])->assertStatus(201);

        Sanctum::actingAs(User::where('email', 'visiteur@example.com')->firstOrFail());

        $id = $dossier['ordonnance']->id_ordonnance;

        // Le compte a le role 'patient' mais aucun id_patient : il n'est
        // proprietaire d'aucun dossier.
        $this->getJson("/api/ordonnances/{$id}")->assertStatus(403);
        $this->deleteJson("/api/ordonnances/{$id}")->assertStatus(403);

        $liste = $this->getJson('/api/ordonnances');
        $liste->assertStatus(200)->assertJsonCount(0, 'ordonnances');

        $historique = $this->getJson("/api/patients/{$patient->id_patient}/ordonnances/historique");
        $historique->assertStatus(200)->assertJsonCount(0, 'historique');

        $this->assertStringNotContainsString('Traitement confidentiel', $liste->getContent());
        $this->assertStringNotContainsString('Traitement confidentiel', $historique->getContent());
    }
}
