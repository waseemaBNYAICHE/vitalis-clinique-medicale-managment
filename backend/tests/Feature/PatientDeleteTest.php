<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PatientDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_patient_peut_etre_supprime(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $patient = Patient::create([
            'nom' => 'Benali',
            'prenom' => 'Yasmine',
            'date_naissance' => '2000-03-20',
            'sexe' => 'Femme',
            'cin' => 'SCRUM118001',
            'telephone' => '0623456789',
            'email' => 'yasmine.scrum118@example.com',
            'groupe_sanguin' => 'O+',
        ]);

        $response = $this->deleteJson(
            '/api/patients/' . $patient->id_patient
        );

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Patient supprimé avec succès',
        ]);

        $this->assertDatabaseMissing('patients', [
            'id_patient' => $patient->id_patient,
        ]);
    }
}