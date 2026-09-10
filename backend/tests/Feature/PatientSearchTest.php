<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PatientSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_recherche_patient_par_cin(): void
    {
        // SCRUM-526 : le role est desormais explicite. Sans lui, la factory
        // retombe sur la valeur par defaut de la colonne ('patient'),
        // un role qui n'a aucune permission sur le module Patients depuis
        // SCRUM-524 : le test recevait 403 au lieu de tester son sujet.
        // Role retenu ici : consultation des dossiers : operation du personnel d'accueil.
        $user = User::factory()->create(['role' => 'secretaire']);

        Sanctum::actingAs($user);

        Patient::create([
            'nom' => 'Benali',
            'prenom' => 'Yasmine',
            'date_naissance' => '2000-03-20',
            'sexe' => 'Femme',
            'cin' => 'SCRUM122001',
            'telephone' => '0623456789',
            'email' => 'yasmine.scrum122@example.com',
            'groupe_sanguin' => 'O+',
        ]);

        $response = $this->getJson(
            '/api/patients/search?cin=SCRUM122001'
        );

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'cin' => 'SCRUM122001',
            'nom' => 'Benali',
            'prenom' => 'Yasmine',
        ]);
    }

    public function test_recherche_patient_par_date_de_naissance(): void
    {
        // SCRUM-526 : le role est desormais explicite. Sans lui, la factory
        // retombe sur la valeur par defaut de la colonne ('patient'),
        // un role qui n'a aucune permission sur le module Patients depuis
        // SCRUM-524 : le test recevait 403 au lieu de tester son sujet.
        // Role retenu ici : consultation des dossiers : operation du personnel d'accueil.
        $user = User::factory()->create(['role' => 'secretaire']);

        Sanctum::actingAs($user);

        Patient::create([
            'nom' => 'Alaoui',
            'prenom' => 'Sara',
            'date_naissance' => '1999-04-10',
            'sexe' => 'Femme',
            'cin' => 'SCRUM122002',
            'telephone' => '0612345678',
            'email' => 'sara.scrum122@example.com',
            'groupe_sanguin' => 'B+',
        ]);

        $response = $this->getJson(
            '/api/patients/search?date_naissance=1999-04-10'
        );

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'date_naissance' => '1999-04-10',
            'nom' => 'Alaoui',
            'prenom' => 'Sara',
        ]);
    }
}