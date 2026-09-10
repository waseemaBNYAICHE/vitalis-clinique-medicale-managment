<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * SCRUM-527 - Protection des endpoints sensibles.
 *
 * Un endpoint est traite ici comme sensible s'il expose des donnees de sante,
 * des donnees financieres, ou s'il modifie les privileges d'un compte.
 *
 * Le point de depart du ticket : les routes /api/dashboard/* exigeaient un
 * compte valide mais aucune permission. Comme /api/register est publique et
 * attribue le role 'patient', n'importe qui pouvait s'inscrire puis lire le
 * chiffre d'affaires de la clinique et la liste nominative des rendez-vous du
 * jour. Ces tests figent la fermeture de cette porte.
 *
 * La limitation de debit, l'exposition de /api/health et l'enumeration des
 * comptes relevent de SCRUM-526 et sont testees dans ApiSecurityTest et
 * HealthCheckSecurityTest.
 */
class EndpointsSensiblesTest extends TestCase
{
    use RefreshDatabase;

    private function utilisateur(string $role, array $attributs = []): User
    {
        return User::factory()->create(['role' => $role] + $attributs);
    }

    // ---------------------------------------------- donnees de gestion

    /**
     * Chiffre d'affaires, tendances sur six mois et export CSV : trois vues
     * de la meme donnee financiere, reservees a l'administrateur.
     *
     * @return array<string, array{string}>
     */
    public static function routesDeGestion(): array
    {
        return [
            'chiffre d affaires' => ['/api/dashboard/chiffre-affaires'],
            'statistiques mensuelles' => ['/api/dashboard/statistiques-mensuelles'],
            'export CSV' => ['/api/dashboard/export-statistiques'],
        ];
    }

    #[DataProvider('routesDeGestion')]
    public function test_les_donnees_de_gestion_refusent_un_visiteur_non_authentifie(string $uri): void
    {
        $this->getJson($uri)->assertStatus(401);
    }

    /**
     * Le coeur du ticket : avant SCRUM-527 ces trois routes repondaient 200 a
     * tout compte authentifie, role 'patient' compris.
     */
    #[DataProvider('routesDeGestion')]
    public function test_les_donnees_de_gestion_refusent_tout_role_sauf_administrateur(string $uri): void
    {
        foreach (['medecin', 'secretaire', 'infirmier', 'patient'] as $role) {
            Sanctum::actingAs($this->utilisateur($role));

            $this->getJson($uri)->assertStatus(403);
        }
    }

    #[DataProvider('routesDeGestion')]
    public function test_ladministrateur_accede_aux_donnees_de_gestion(string $uri): void
    {
        Sanctum::actingAs($this->utilisateur('administrateur'));

        $this->getJson($uri)->assertStatus(200);
    }

    /**
     * Un compte cree librement via l'inscription publique ne doit atteindre
     * aucune donnee de gestion. C'est le scenario d'attaque complet, joue de
     * bout en bout plutot que simule avec une factory.
     */
    public function test_un_compte_cree_via_linscription_publique_natteint_pas_les_donnees_de_gestion(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Visiteur',
            'email' => 'visiteur@example.com',
            'password' => 'MotDePasse123',
            'password_confirmation' => 'MotDePasse123',
        ])->assertStatus(201);

        Sanctum::actingAs(User::where('email', 'visiteur@example.com')->firstOrFail());

        $this->getJson('/api/dashboard/chiffre-affaires')->assertStatus(403);
        $this->getJson('/api/dashboard/statistiques-mensuelles')->assertStatus(403);
        $this->getJson('/api/dashboard/export-statistiques')->assertStatus(403);
        $this->getJson('/api/dashboard/rendez-vous-du-jour')->assertStatus(403);
        $this->getJson('/api/dashboard/patients-count')->assertStatus(403);
    }

    // ------------------------------------------ indicateurs d activite

    /**
     * @return array<string, array{string}>
     */
    public static function routesDIndicateurs(): array
    {
        return [
            'nombre de patients' => ['/api/dashboard/patients-count'],
            'nombre de rendez-vous' => ['/api/dashboard/rendez-vous-count'],
            'nombre de consultations' => ['/api/dashboard/consultations-count'],
            'examens en attente' => ['/api/dashboard/examens-en-attente'],
            'agenda du jour' => ['/api/dashboard/rendez-vous-du-jour'],
        ];
    }

    #[DataProvider('routesDIndicateurs')]
    public function test_les_indicateurs_refusent_un_visiteur_non_authentifie(string $uri): void
    {
        $this->getJson($uri)->assertStatus(401);
    }

    /**
     * Ces compteurs portent sur toute la clinique. Le role patient detient
     * bien CONSULTATIONS_READ et EXAMENS_READ, mais pour SES dossiers : cela
     * ne doit pas lui ouvrir les totaux de l'etablissement.
     */
    #[DataProvider('routesDIndicateurs')]
    public function test_les_indicateurs_refusent_le_role_patient(string $uri): void
    {
        Sanctum::actingAs($this->utilisateur('patient'));

        $this->getJson($uri)->assertStatus(403);
    }

    #[DataProvider('routesDIndicateurs')]
    public function test_le_personnel_accede_aux_indicateurs(string $uri): void
    {
        foreach (['administrateur', 'medecin', 'secretaire', 'infirmier'] as $role) {
            Sanctum::actingAs($this->utilisateur($role));

            $this->getJson($uri)->assertStatus(200);
        }
    }

    /**
     * Lire les indicateurs ne doit pas entrainer l'acces aux donnees
     * financieres : les deux permissions restent distinctes.
     */
    public function test_lacces_aux_indicateurs_nouvre_pas_les_donnees_de_gestion(): void
    {
        Sanctum::actingAs($this->utilisateur('infirmier'));

        $this->getJson('/api/dashboard/patients-count')->assertStatus(200);
        $this->getJson('/api/dashboard/chiffre-affaires')->assertStatus(403);
    }

    // ------------------------------- cloisonnement de l agenda du jour

    /**
     * Prepare un rendez-vous du jour pour un medecin donne et renvoie le nom
     * complet du patient concerne.
     */
    private function creerRendezVousDuJour(int $idMedecin, string $prenom, string $nom): string
    {
        $idPatient = DB::table('patients')->insertGetId([
            'nom' => $nom,
            'prenom' => $prenom,
            'date_naissance' => '1990-01-01',
            'sexe' => 'F',
            'cin' => 'CIN'.random_int(100000, 999999),
            'telephone' => '0600000000',
            'email' => strtolower($prenom).random_int(1000, 9999).'@example.com',
            'groupe_sanguin' => 'O+',
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_patient');

        DB::table('rendez_vous')->insert([
            'id_patient' => $idPatient,
            'id_medecin' => $idMedecin,
            'date_rendez_vous' => now()->toDateString(),
            'heure_debut' => '09:00:00',
            'heure_fin' => '09:30:00',
            'motif' => 'Motif confidentiel de '.$prenom,
            'statut' => 'confirme',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $prenom.' '.$nom;
    }

    private function creerMedecin(string $nom): int
    {
        $idSpecialite = DB::table('specialites')->insertGetId([
            'nom_specialite' => 'Specialite '.$nom,
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

    /**
     * L'agenda du jour associe un nom de patient au MOTIF de sa venue, donc a
     * une information de sante. medecinDashboard() cloisonnait deja cette
     * donnee sur /api/dashboard, mais cette route-ci l'exposait en entier.
     */
    public function test_un_medecin_ne_voit_que_ses_propres_rendez_vous_du_jour(): void
    {
        $idMedecinA = $this->creerMedecin('Alami');
        $idMedecinB = $this->creerMedecin('Bennani');

        $patientDeA = $this->creerRendezVousDuJour($idMedecinA, 'Fatima', 'Alaoui');
        $patientDeB = $this->creerRendezVousDuJour($idMedecinB, 'Yasmine', 'Benali');

        Sanctum::actingAs($this->utilisateur('medecin', ['id_medecin' => $idMedecinA]));

        $reponse = $this->getJson('/api/dashboard/rendez-vous-du-jour');

        $reponse->assertStatus(200);
        $corps = $reponse->getContent();

        $this->assertStringContainsString($patientDeA, $corps);
        $this->assertStringNotContainsString(
            $patientDeB,
            $corps,
            'Un medecin ne doit pas voir les patients de ses confreres.'
        );
        $this->assertStringNotContainsString('Motif confidentiel de Yasmine', $corps);
    }

    /**
     * Un compte medecin sans profil associe ne se voit attribuer aucun
     * rendez-vous : on prefere une liste vide a l'agenda complet.
     */
    public function test_un_medecin_sans_profil_ne_voit_aucun_rendez_vous(): void
    {
        $idMedecin = $this->creerMedecin('Alami');
        $this->creerRendezVousDuJour($idMedecin, 'Fatima', 'Alaoui');

        Sanctum::actingAs($this->utilisateur('medecin', ['id_medecin' => null]));

        $this->getJson('/api/dashboard/rendez-vous-du-jour')
            ->assertStatus(200)
            ->assertJsonCount(0, 'rendez_vous');
    }

    /**
     * Le personnel d'accueil et de coordination garde l'agenda complet :
     * accueillir les patients de la journee suppose de le voir en entier.
     */
    public function test_le_personnel_daccueil_garde_lagenda_complet(): void
    {
        $idMedecinA = $this->creerMedecin('Alami');
        $idMedecinB = $this->creerMedecin('Bennani');

        $patientDeA = $this->creerRendezVousDuJour($idMedecinA, 'Fatima', 'Alaoui');
        $patientDeB = $this->creerRendezVousDuJour($idMedecinB, 'Yasmine', 'Benali');

        foreach (['administrateur', 'secretaire', 'infirmier'] as $role) {
            Sanctum::actingAs($this->utilisateur($role));

            $corps = $this->getJson('/api/dashboard/rendez-vous-du-jour')
                ->assertStatus(200)
                ->getContent();

            $this->assertStringContainsString($patientDeA, $corps, "Role {$role}");
            $this->assertStringContainsString($patientDeB, $corps, "Role {$role}");
        }
    }

    // ------------------------------------ modification des privileges

    public function test_un_administrateur_ne_peut_pas_modifier_son_propre_role(): void
    {
        $admin = $this->utilisateur('administrateur');

        Sanctum::actingAs($admin);

        $this->putJson("/api/users/{$admin->id}/role", ['role' => 'patient'])
            ->assertStatus(403)
            ->assertJson(['message' => 'Vous ne pouvez pas modifier votre propre role']);

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => 'administrateur',
        ]);
    }

    /**
     * Consequence de la regle precedente : il reste toujours au moins un
     * administrateur. Retrograder le dernier suppose de se retrograder
     * soi-meme, ce qui est refuse.
     */
    public function test_la_clinique_ne_peut_pas_se_retrouver_sans_administrateur(): void
    {
        $premier = $this->utilisateur('administrateur');
        $second = $this->utilisateur('administrateur');

        Sanctum::actingAs($premier);

        // Retrograder un confrere reste possible.
        $this->putJson("/api/users/{$second->id}/role", ['role' => 'secretaire'])
            ->assertStatus(200);

        // Mais le dernier administrateur ne peut pas se retirer lui-meme.
        $this->putJson("/api/users/{$premier->id}/role", ['role' => 'secretaire'])
            ->assertStatus(403);

        $this->assertSame(
            1,
            User::where('role', 'administrateur')->count(),
            'Il doit rester au moins un administrateur.'
        );
    }

    public function test_un_administrateur_peut_toujours_modifier_le_role_dun_autre_compte(): void
    {
        $admin = $this->utilisateur('administrateur');
        $cible = $this->utilisateur('patient');

        Sanctum::actingAs($admin);

        $this->putJson("/api/users/{$cible->id}/role", ['role' => 'infirmier'])
            ->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $cible->id,
            'role' => 'infirmier',
        ]);
    }

    /**
     * La protection ne remplace pas le controle de permission : un non
     * administrateur reste arrete avant, par 'roles.manage'.
     */
    public function test_un_non_administrateur_ne_peut_pas_modifier_de_role(): void
    {
        foreach (['medecin', 'secretaire', 'infirmier', 'patient'] as $role) {
            $auteur = $this->utilisateur($role);
            $cible = $this->utilisateur('patient');

            Sanctum::actingAs($auteur);

            // Ni celui d'un autre compte...
            $this->putJson("/api/users/{$cible->id}/role", ['role' => 'administrateur'])
                ->assertStatus(403);

            // ...ni le sien, pour se promouvoir.
            $this->putJson("/api/users/{$auteur->id}/role", ['role' => 'administrateur'])
                ->assertStatus(403);

            $this->assertDatabaseHas('users', ['id' => $auteur->id, 'role' => $role]);
        }
    }
}
