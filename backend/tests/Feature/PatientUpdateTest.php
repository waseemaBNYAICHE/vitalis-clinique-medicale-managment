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
        // SCRUM-526 : le role est desormais explicite. Sans lui, la factory
        // retombe sur la valeur par defaut de la colonne ('patient'),
        // un role qui n'a aucune permission sur le module Patients depuis
        // SCRUM-524 : le test recevait 403 au lieu de tester son sujet.
        // Role retenu ici : mise a jour d'un dossier patient : operation du personnel d'accueil.
        $user = User::factory()->create(['role' => 'secretaire']);

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