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
        // SCRUM-526 : le role est desormais explicite. Sans lui, la factory
        // retombe sur la valeur par defaut de la colonne ('patient'),
        // un role qui n'a aucune permission sur le module Patients depuis
        // SCRUM-524 : le test recevait 403 au lieu de tester son sujet.
        // Role retenu ici : creation d'un dossier patient : operation du personnel d'accueil.
        $user = User::factory()->create(['role' => 'secretaire']);

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
