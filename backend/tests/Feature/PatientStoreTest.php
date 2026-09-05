<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PatientStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_patient_peut_etre_enregistre(): void
    {
   $user = User::factory()->create(['role' => 'medecin']);
   
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/patients', [
            'nom' => 'Test',
            'prenom' => 'Patient',
            'date_naissance' => '2000-01-15',
            'sexe' => 'Homme',
            'cin' => 'TESTSCRUM110',
            'telephone' => '0612345678',
            'email' => 'patient.scrum110@example.com',
            'groupe_sanguin' => 'A+',
        ]);

        $response->assertStatus(201);

        $response->assertJson([
            'message' => 'Patient ajouté avec succès',
        ]);

        $this->assertDatabaseHas('patients', [
            'cin' => 'TESTSCRUM110',
            'email' => 'patient.scrum110@example.com',
        ]);
    }
}
