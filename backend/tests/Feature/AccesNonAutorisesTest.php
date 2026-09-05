<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * SCRUM-525 - Tests des acces non autorises.
 *
 * Verifie, sur les routes reellement exposees aujourd'hui (Patients et
 * Roles), que :
 * - un visiteur non authentifie recoit 401 ;
 * - un role sans la permission requise recoit 403 ;
 * - un role avec la permission requise n'est pas bloque.
 *
 * Ces tests portent sur le comportement HTTP (Gates via middleware can:),
 * pas sur la logique metier des controllers.
 */
class AccesNonAutorisesTest extends TestCase
{
    use RefreshDatabase;

    private function creerUtilisateur(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function creerPatient(): Patient
    {
        return Patient::create([
            'nom' => 'Alaoui',
            'prenom' => 'Fatima',
            'date_naissance' => '1990-01-01',
            'sexe' => 'F',
            'cin' => 'AA'.random_int(100000, 999999),
            'telephone' => '0600000000',
            'email' => 'patient'.random_int(1000, 9999).'@example.com',
            'groupe_sanguin' => 'O+',
        ]);
    }

    // --- Visiteur non authentifie : 401 partout ---

    public function test_visiteur_non_authentifie_recoit_401_sur_liste_patients(): void
    {
        $this->getJson('/api/patients')->assertStatus(401);
    }

    public function test_visiteur_non_authentifie_recoit_401_sur_liste_roles(): void
    {
        $this->getJson('/api/roles')->assertStatus(401);
    }

    public function test_visiteur_non_authentifie_recoit_401_sur_modification_role(): void
    {
        $cible = $this->creerUtilisateur('patient');

        $this->putJson("/api/users/{$cible->id}/role", ['role' => 'medecin'])
            ->assertStatus(401);
    }

    // --- Roles authentifies sans la permission requise : 403 ---

    #[DataProvider('rolesSansGestionDesRoles')]
    public function test_role_sans_permission_roles_manage_recoit_403_sur_liste_roles(string $role): void
    {
        Sanctum::actingAs($this->creerUtilisateur($role));

        $this->getJson('/api/roles')->assertStatus(403);
    }

    #[DataProvider('rolesSansGestionDesRoles')]
    public function test_role_sans_permission_roles_manage_recoit_403_sur_modification_role(string $role): void
    {
        $cible = $this->creerUtilisateur('patient');
        Sanctum::actingAs($this->creerUtilisateur($role));

        $this->putJson("/api/users/{$cible->id}/role", ['role' => 'medecin'])
            ->assertStatus(403);
    }

    public static function rolesSansGestionDesRoles(): array
    {
        return [
            'medecin' => ['medecin'],
            'secretaire' => ['secretaire'],
            'infirmier' => ['infirmier'],
            'patient' => ['patient'],
        ];
    }

    /**
     * Le patient n'a aucune permission sur le module Patients (reserve au
     * personnel) : il doit recevoir 403, pas seulement etre restreint aux
     * siennes.
     */
    public function test_role_patient_recoit_403_sur_liste_patients(): void
    {
        Sanctum::actingAs($this->creerUtilisateur('patient'));

        $this->getJson('/api/patients')->assertStatus(403);
    }

    #[DataProvider('rolesSansSuppressionPatient')]
    public function test_role_sans_permission_delete_recoit_403_sur_suppression_patient(string $role): void
    {
        $patient = $this->creerPatient();
        Sanctum::actingAs($this->creerUtilisateur($role));

        $this->deleteJson("/api/patients/{$patient->id_patient}")
            ->assertStatus(403);
    }

    public static function rolesSansSuppressionPatient(): array
    {
        return [
            'medecin' => ['medecin'],
            'secretaire' => ['secretaire'],
            'infirmier' => ['infirmier'],
            'patient' => ['patient'],
        ];
    }

    // --- Roles avec la permission requise : pas de 401/403 ---

    #[DataProvider('rolesAvecAccesLecturePatients')]
    public function test_role_avec_permission_patients_read_nest_pas_bloque(string $role): void
    {
        Sanctum::actingAs($this->creerUtilisateur($role));

        $reponse = $this->getJson('/api/patients');

        $reponse->assertStatus(200);
    }

    public static function rolesAvecAccesLecturePatients(): array
    {
        return [
            'administrateur' => ['administrateur'],
            'medecin' => ['medecin'],
            'secretaire' => ['secretaire'],
            'infirmier' => ['infirmier'],
        ];
    }

    public function test_administrateur_peut_supprimer_un_patient(): void
    {
        $patient = $this->creerPatient();
        Sanctum::actingAs($this->creerUtilisateur('administrateur'));

        $reponse = $this->deleteJson("/api/patients/{$patient->id_patient}");

        $reponse->assertStatus(200);
        $this->assertDatabaseMissing('patients', ['id_patient' => $patient->id_patient]);
    }

    public function test_administrateur_peut_consulter_la_liste_des_roles(): void
    {
        Sanctum::actingAs($this->creerUtilisateur('administrateur'));

        $this->getJson('/api/roles')
            ->assertStatus(200)
            ->assertJsonStructure(['roles']);
    }

    public function test_administrateur_peut_modifier_le_role_dun_utilisateur(): void
    {
        $cible = $this->creerUtilisateur('patient');
        Sanctum::actingAs($this->creerUtilisateur('administrateur'));

        $this->putJson("/api/users/{$cible->id}/role", ['role' => 'medecin'])
            ->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $cible->id,
            'role' => 'medecin',
        ]);
    }
}