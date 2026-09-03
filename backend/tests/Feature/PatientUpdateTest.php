<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PatientUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_patient_peut_etre_modifie(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $patient = Patient::create([
            'nom' => 'Benali',
            'prenom' => 'Yasmine',
            'date_naissance' => '2000-03-20',
            'sexe' => 'Femme',
            'cin' => 'SCRUM113001',
            'telephone' => '0623456789',
            'email' => 'yasmine.scrum113@example.com',
            'groupe_sanguin' => 'O+',
        ]);

        $response = $this->putJson('/api/patients/' . $patient->id_patient, [
            'nom' => 'Benali',
            'prenom' => 'Yasmine',
            'date_naissance' => '2000-03-20',
            'sexe' => 'Femme',
            'cin' => 'SCRUM113001',
            'telephone' => '0699999999',
            'email' => 'yasmine.scrum113@example.com',
            'groupe_sanguin' => 'O+',
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Patient modifié avec succès',
        ]);

        $this->assertDatabaseHas('patients', [
            'id_patient' => $patient->id_patient,
            'telephone' => '0699999999',
        ]);
    }
}