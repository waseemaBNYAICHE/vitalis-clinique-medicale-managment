<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(401);
    }

    public function test_medecin_can_access_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'medecin']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure(['role', 'data']);
    }

    public function test_administrateur_dashboard_returns_expected_keys(): void
    {
        $user = User::factory()->create(['role' => 'administrateur']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'role',
                'data' => [
                    'total_patients',
                    'total_medecins',
                    'rendez_vous_aujourdhui',
                    'hospitalisations_en_cours',
                    'factures_impayees',
                    'revenu_total',
                ],
            ]);
    }

    public function test_patients_count_endpoint(): void
    {
        $user = User::factory()->create(['role' => 'medecin']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/dashboard/patients-count');

        $response->assertStatus(200)
            ->assertJsonStructure(['total_patients']);
    }

    public function test_rendez_vous_count_endpoint(): void
    {
        $user = User::factory()->create(['role' => 'medecin']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/dashboard/rendez-vous-count');

        $response->assertStatus(200)
            ->assertJsonStructure(['total_rendez_vous']);
    }

    public function test_consultations_count_endpoint(): void
    {
        $user = User::factory()->create(['role' => 'medecin']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/dashboard/consultations-count');

        $response->assertStatus(200)
            ->assertJsonStructure(['total_consultations']);
    }

    public function test_chiffre_affaires_endpoint(): void
    {
        $user = User::factory()->create(['role' => 'medecin']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/dashboard/chiffre-affaires');

        $response->assertStatus(200)
            ->assertJsonStructure(['chiffre_affaires']);
    }

    public function test_examens_en_attente_endpoint(): void
    {
        $user = User::factory()->create(['role' => 'medecin']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/dashboard/examens-en-attente');

        $response->assertStatus(200)
            ->assertJsonStructure(['examens_en_attente']);
    }

    public function test_statistiques_mensuelles_endpoint(): void
    {
        $user = User::factory()->create(['role' => 'medecin']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/dashboard/statistiques-mensuelles');

        $response->assertStatus(200)
            ->assertJsonStructure(['statistiques']);
    }

    public function test_export_statistiques_returns_csv(): void
    {
        $user = User::factory()->create(['role' => 'medecin']);

        $response = $this->actingAs($user, 'sanctum')
            ->get('/api/dashboard/export-statistiques');

        $response->assertStatus(200)
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_patient_without_profile_gets_clear_message(): void
    {
        $user = User::factory()->create(['role' => 'patient', 'id_patient' => null]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'role' => 'patient',
                'data' => ['message' => 'Aucun profil patient associé à cet utilisateur'],
            ]);
    }
}