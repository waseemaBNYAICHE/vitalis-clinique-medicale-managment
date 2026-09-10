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
        // SCRUM-526 : le role est desormais explicite. Sans lui, la factory
        // retombe sur la valeur par defaut de la colonne ('patient'),
        // un role qui n'a aucune permission sur le module Patients depuis
        // SCRUM-524 : le test recevait 403 au lieu de tester son sujet.
        // Role retenu ici : suppression d'un dossier medical : reservee a l'administrateur (SCRUM-518).
        $user = User::factory()->create(['role' => 'administrateur']);

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

        // SCRUM-526 : le point final manquait dans l'assertion. Le message est
        // recopie du controller (SCRUM-115) plutot que corrige dans celui-ci :
        // le frontend affiche cette chaine telle quelle. Cet ecart etait
        // masque par le 403 que le test recevait avant la correction du role.
        $response->assertJson([
            'message' => 'Patient supprimé avec succès.',
        ]);

        $this->assertDatabaseMissing('patients', [
            'id_patient' => $patient->id_patient,
        ]);
    }
}