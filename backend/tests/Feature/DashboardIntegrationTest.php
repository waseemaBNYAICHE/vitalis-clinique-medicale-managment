<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_login_and_dashboard_flow_for_medecin(): void
    {
        // 1. Créer un médecin avec un utilisateur associé
        $idSpecialite = DB::table('specialites')->insertGetId([
            'nom_specialite' => 'Cardiologie',
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_specialite');

        $idMedecin = DB::table('medecins')->insertGetId([
            'matricule' => 'MED-TEST-01',
            'nom' => 'Benali',
            'prenom' => 'Ahmed',
            'telephone' => '0600000000',
            'email' => 'medecin.integration@test.com',
            'date_embauche' => now(),
            'tarif_consultation' => 200,
            'id_specialite' => $idSpecialite,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_medecin');

        $user = User::factory()->create([
            'role' => 'medecin',
            'id_medecin' => $idMedecin,
            'email' => 'user.medecin@test.com',
        ]);

        // 2. Login
        $loginResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $loginResponse->assertStatus(200)
            ->assertJsonStructure(['token', 'user' => ['role']]);

        $token = $loginResponse->json('token');

        // 3. Créer un patient et un rendez-vous du jour
        $idPatient = DB::table('patients')->insertGetId([
            'nom' => 'Zahra', 'prenom' => 'Fatima',
            'date_naissance' => '1990-01-01', 'sexe' => 'F',
            'cin' => 'INT001', 'telephone' => '0611111111',
            'email' => 'patient.integration@test.com',
            'created_at' => now(), 'updated_at' => now(),
        ], 'id_patient');

        DB::table('rendez_vous')->insert([
            'date_rendez_vous' => now()->toDateString(),
            'heure_debut' => '09:00:00', 'heure_fin' => '09:30:00',
            'motif' => 'Consultation générale', 'statut' => 'Confirmé',
            'id_patient' => $idPatient, 'id_medecin' => $idMedecin,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // 4. Appeler le dashboard avec le token
        $dashboardResponse = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/dashboard');

        $dashboardResponse->assertStatus(200)
            ->assertJson([
                'role' => 'medecin',
                'data' => [
                    'stats' => ['patients' => 1, 'rendezVous' => 1],
                ],
            ]);
    }

    public function test_all_roles_can_access_dashboard_without_error(): void
    {
        $roles = ['administrateur', 'medecin', 'secretaire', 'infirmier', 'patient'];

        foreach ($roles as $role) {
            $user = User::factory()->create(['role' => $role]);

            $response = $this->actingAs($user, 'sanctum')
                ->getJson('/api/dashboard');

            $response->assertStatus(200);
        }
    }

    public function test_dashboard_all_endpoints_are_accessible_together(): void
    {
        $user = User::factory()->create(['role' => 'medecin']);

        $endpoints = [
            '/api/dashboard',
            '/api/dashboard/patients-count',
            '/api/dashboard/rendez-vous-count',
            '/api/dashboard/consultations-count',
            '/api/dashboard/chiffre-affaires',
            '/api/dashboard/examens-en-attente',
            '/api/dashboard/statistiques-mensuelles',
        ];

        foreach ($endpoints as $endpoint) {
            $this->actingAs($user, 'sanctum')
                ->getJson($endpoint)
                ->assertStatus(200);
        }
    }
}