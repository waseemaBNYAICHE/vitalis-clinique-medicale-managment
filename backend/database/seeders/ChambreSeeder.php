<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChambreSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $chambres = [
            [1, '101', 'Individuelle', '1er étage', 1, 650, 'disponible', 'Chambre individuelle climatisée avec salle de bain.'],
            [2, '102', 'Double', '1er étage', 2, 450, 'occupee', 'Chambre double avec salle de bain.'],
            [3, '201', 'Soins intensifs', '2e étage', 1, 1800, 'occupee', 'Chambre équipée pour la surveillance intensive.'],

            [4, '103', 'Individuelle', '1er étage', 1, 650, 'disponible', 'Chambre individuelle standard avec salle de bain.'],
            [5, '104', 'Double', '1er étage', 2, 450, 'disponible', 'Chambre double climatisée pour deux patients.'],
            [6, '105', 'Individuelle', '1er étage', 1, 650, 'maintenance', 'Chambre individuelle temporairement en maintenance.'],
            [7, '106', 'Double', '1er étage', 2, 450, 'disponible', 'Chambre double avec sanitaires privés.'],
            [8, '107', 'Individuelle', '1er étage', 1, 700, 'disponible', 'Chambre individuelle avec espace accompagnant.'],

            [9, '202', 'Soins intensifs', '2e étage', 1, 1800, 'disponible', 'Chambre de soins intensifs avec monitoring.'],
            [10, '203', 'Soins intensifs', '2e étage', 1, 1800, 'disponible', 'Chambre équipée pour surveillance médicale continue.'],
            [11, '204', 'Individuelle', '2e étage', 1, 750, 'occupee', 'Chambre individuelle équipée pour hospitalisation.'],
            [12, '205', 'Double', '2e étage', 2, 500, 'disponible', 'Chambre double avec équipement médical.'],
            [13, '206', 'Isolement', '2e étage', 1, 1100, 'disponible', 'Chambre individuelle destinée à l’isolement médical.'],
            [14, '207', 'Isolement', '2e étage', 1, 1100, 'maintenance', 'Chambre d’isolement en maintenance technique.'],

            [15, '301', 'Suite', '3e étage', 1, 1400, 'disponible', 'Suite privée avec espace pour accompagnant.'],
            [16, '302', 'Suite', '3e étage', 1, 1400, 'occupee', 'Suite privée climatisée avec sanitaires privés.'],
            [17, '303', 'Maternité', '3e étage', 1, 900, 'disponible', 'Chambre dédiée au séjour post-accouchement.'],
            [18, '304', 'Maternité', '3e étage', 1, 900, 'disponible', 'Chambre de maternité avec espace nouveau-né.'],
            [19, '305', 'Pédiatrie', '3e étage', 1, 800, 'disponible', 'Chambre adaptée à l’hospitalisation pédiatrique.'],
            [20, '306', 'Pédiatrie', '3e étage', 1, 800, 'disponible', 'Chambre pédiatrique avec espace accompagnant.'],
        ];

        $data = [];

        foreach ($chambres as $chambre) {
            $data[] = [
                'id_chambre' => $chambre[0],
                'numero_chambre' => $chambre[1],
                'type_chambre' => $chambre[2],
                'etage' => $chambre[3],
                'capacite' => $chambre[4],
                'tarif_journalier' => $chambre[5],
                'statut' => $chambre[6],
                'description' => $chambre[7],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('chambres')->upsert(
            $data,
            ['id_chambre'],
            [
                'numero_chambre',
                'type_chambre',
                'etage',
                'capacite',
                'tarif_journalier',
                'statut',
                'description',
                'updated_at',
            ]
        );
    }
}