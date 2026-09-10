<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        /*
        |--------------------------------------------------------------------------
        | Spécialités
        |--------------------------------------------------------------------------
        */

        $specialites = [
            [1, 'Médecine générale', 'Consultations générales et suivi médical.'],
            [2, 'Cardiologie', 'Diagnostic et traitement des maladies cardiovasculaires.'],
            [3, 'Pédiatrie', 'Soins médicaux des nourrissons et des enfants.'],
            [4, 'Dermatologie', 'Diagnostic et traitement des maladies de la peau.'],
            [5, 'Gynécologie-obstétrique', 'Suivi gynécologique et suivi de grossesse.'],
            [6, 'Traumatologie-orthopédie', 'Traitement des traumatismes, os et articulations.'],
            [7, 'Neurologie', 'Traitement des maladies du système nerveux.'],
            [8, 'Gastro-entérologie', 'Traitement des maladies digestives.'],
            [9, 'Endocrinologie', 'Prise en charge du diabète et des troubles hormonaux.'],
            [10, 'Psychiatrie', 'Diagnostic et traitement des troubles psychiques.'],
            [11, 'Urologie', 'Traitement des maladies urinaires et urologiques.'],
        ];

        $specialitesData = [];

        foreach ($specialites as $specialite) {
            $specialitesData[] = [
                'id_specialite' => $specialite[0],
                'nom_specialite' => $specialite[1],
                'description' => $specialite[2],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('specialites')->insert($specialitesData);

        /*
        |--------------------------------------------------------------------------
        | Médecins
        |--------------------------------------------------------------------------
        */

        $medecins = [
            [1, 'MED-001', 'El Amrani', 'Youssef', '+212661234501', 'youssef.elamrani@vitalis.ma', '2019-03-11', 250, 1, 'M', 'Tanger'],
            [2, 'MED-002', 'Berrada', 'Salma', '+212661234502', 'salma.berrada@vitalis.ma', '2018-09-17', 400, 2, 'F', 'Rabat'],
            [3, 'MED-003', 'Alaoui', 'Mehdi', '+212661234503', 'mehdi.alaoui@vitalis.ma', '2020-01-06', 300, 3, 'M', 'Casablanca'],
            [4, 'MED-004', 'Benjelloun', 'Imane', '+212661234504', 'imane.benjelloun@vitalis.ma', '2021-05-24', 350, 4, 'F', 'Fès'],
            [5, 'MED-005', 'Tazi', 'Nadia', '+212661234505', 'nadia.tazi@vitalis.ma', '2017-11-13', 400, 5, 'F', 'Tanger'],
            [6, 'MED-006', 'Chraibi', 'Omar', '+212661234506', 'omar.chraibi@vitalis.ma', '2020-08-03', 350, 6, 'M', 'Marrakech'],
            [7, 'MED-007', 'Idrissi', 'Sara', '+212661234507', 'sara.idrissi@vitalis.ma', '2021-02-15', 450, 7, 'F', 'Tanger'],
            [8, 'MED-008', 'Lahlou', 'Hamza', '+212661234508', 'hamza.lahlou@vitalis.ma', '2020-10-05', 400, 8, 'M', 'Tétouan'],
            [9, 'MED-009', 'El Mansouri', 'Kawtar', '+212661234509', 'kawtar.elmansouri@vitalis.ma', '2022-01-10', 400, 9, 'F', 'Agadir'],
            [10, 'MED-010', 'Ait Lahcen', 'Adil', '+212661234510', 'adil.aitlahcen@vitalis.ma', '2019-06-17', 450, 10, 'M', 'Béni Mellal'],
            [11, 'MED-011', 'El Khatib', 'Othmane', '+212661234511', 'othmane.elkhatib@vitalis.ma', '2018-04-23', 400, 11, 'M', 'Meknès'],
        ];

        $medecinsData = [];

        foreach ($medecins as $medecin) {
            $medecinsData[] = [
                'id_medecin' => $medecin[0],
                'matricule' => $medecin[1],
                'nom' => $medecin[2],
                'prenom' => $medecin[3],
                'telephone' => $medecin[4],
                'email' => $medecin[5],
                'date_embauche' => $medecin[6],
                'tarif_consultation' => $medecin[7],
                'id_specialite' => $medecin[8],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('medecins')->insert($medecinsData);

        /*
        |--------------------------------------------------------------------------
        | 100 patients marocains
        |--------------------------------------------------------------------------
        */

        $prenomsHommes = [
            'Mohamed', 'Ahmed', 'Yassine', 'Amine', 'Oussama',
            'Reda', 'Karim', 'Ilyas', 'Soufiane', 'Adil',
            'Anas', 'Zakaria', 'Hamza', 'Mehdi', 'Ayoub',
        ];

        $prenomsFemmes = [
            'Fatima Zahra', 'Salma', 'Sara', 'Imane', 'Kawtar',
            'Nadia', 'Houda', 'Ikram', 'Meryem', 'Asmae',
            'Ghita', 'Chaimae', 'Oumaima', 'Hajar', 'Khadija',
        ];

        $nomsFamille = [
            'El Amrani', 'Alaoui', 'Berrada', 'Benjelloun',
            'Tazi', 'Idrissi', 'Lahlou', 'Chraibi',
            'Naciri', 'Mernissi', 'Ouazzani', 'Bennis',
            'El Fassi', 'Skalli', 'Belkadi', 'Lamrani',
            'El Mansouri', 'Ait Lahcen', 'El Khatib', 'Amrani',
        ];

        $groupesSanguins = [
            'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-',
        ];

        $prefixesCin = [
            'AB', 'AE', 'BE', 'BK', 'CD', 'EE',
            'GA', 'J', 'K', 'LA', 'TA',
        ];

        $villes = [
            'Tanger', 'Tétouan', 'Larache', 'Ksar El Kebir',
            'Rabat', 'Casablanca', 'Fès', 'Meknès',
            'Kénitra', 'Agadir', 'Marrakech',
        ];

        $patientsData = [];

        for ($id = 1; $id <= 100; $id++) {
            $sexe = $id % 2 === 0 ? 'M' : 'F';

            if ($sexe === 'M') {
                $prenom = $prenomsHommes[$id % count($prenomsHommes)];
            } else {
                $prenom = $prenomsFemmes[$id % count($prenomsFemmes)];
            }

            $nom = $nomsFamille[$id % count($nomsFamille)];
            $annee = 1955 + ($id % 50);
            $mois = 1 + ($id % 12);
            $jour = 1 + ($id % 28);

            $patientsData[] = [
                'id_patient' => $id,
                'nom' => $nom,
                'prenom' => $prenom,
                'date_naissance' => sprintf(
                    '%04d-%02d-%02d',
                    $annee,
                    $mois,
                    $jour
                ),
                'sexe' => $sexe,
                'cin' => $prefixesCin[
                    $id % count($prefixesCin)
                ] . sprintf('%06d', 500000 + $id),
                'telephone' => '+2126' . sprintf('%08d', 10000000 + $id),
                'email' => 'patient' . sprintf('%03d', $id) . '@example.ma',
                'groupe_sanguin' => $groupesSanguins[
                    $id % count($groupesSanguins)
                ],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('patients')->insert($patientsData);

        /*
        |--------------------------------------------------------------------------
        | Compte administrateur
        |--------------------------------------------------------------------------
        */

        DB::table('users')->insert([
            [
                'name' => 'Administrateur',
                'prenom' => 'Vitalis',
                'email' => 'admin@vitalis.ma',
                'password' => Hash::make('Vitalis@2026'),
                'role' => 'admin',
                'telephone' => '+212539000001',
                'adresse' => 'Avenue Mohammed VI, Tanger',
                'date_naissance' => '1990-01-01',
                'sexe' => 'F',
                'statut' => 'actif',
                'id_medecin' => null,
                'id_patient' => null,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Comptes des 11 médecins
        |--------------------------------------------------------------------------
        */

        $comptesMedecins = [];
        $motDePasseMedecin = Hash::make('Medecin@2026');

        foreach ($medecins as $medecin) {
            $comptesMedecins[] = [
                'name' => $medecin[2],
                'prenom' => $medecin[3],
                'email' => $medecin[5],
                'password' => $motDePasseMedecin,
                'role' => 'medecin',
                'telephone' => $medecin[4],
                'adresse' => $medecin[10],
                'date_naissance' => sprintf(
                    '%04d-%02d-%02d',
                    1975 + ($medecin[0] % 15),
                    1 + ($medecin[0] % 12),
                    1 + ($medecin[0] % 28)
                ),
                'sexe' => $medecin[9],
                'statut' => 'actif',
                'id_medecin' => $medecin[0],
                'id_patient' => null,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('users')->insert($comptesMedecins);

        /*
        |--------------------------------------------------------------------------
        | Comptes des 100 patients
        |--------------------------------------------------------------------------
        */

        $comptesPatients = [];
        $motDePassePatient = Hash::make('Patient@2026');

        foreach ($patientsData as $patient) {
            $comptesPatients[] = [
                'name' => $patient['nom'],
                'prenom' => $patient['prenom'],
                'email' => $patient['email'],
                'password' => $motDePassePatient,
                'role' => 'patient',
                'telephone' => $patient['telephone'],
                'adresse' => $villes[
                    $patient['id_patient'] % count($villes)
                ],
                'date_naissance' => $patient['date_naissance'],
                'sexe' => $patient['sexe'],
                'statut' => 'actif',
                'id_medecin' => null,
                'id_patient' => $patient['id_patient'],
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('users')->insert($comptesPatients);

        /*
        |--------------------------------------------------------------------------
        | Rendez-vous
        |--------------------------------------------------------------------------
        */

        DB::table('rendez_vous')->insert([
            [
                'id_rendez_vous' => 1,
                'date_rendez_vous' => '2026-09-11',
                'heure_debut' => '09:00',
                'heure_fin' => '09:30',
                'motif' => 'Migraine récurrente',
                'statut' => 'confirme',
                'id_patient' => 1,
                'id_medecin' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_rendez_vous' => 2,
                'date_rendez_vous' => '2026-09-11',
                'heure_debut' => '10:00',
                'heure_fin' => '10:30',
                'motif' => 'Douleurs thoraciques',
                'statut' => 'termine',
                'id_patient' => 2,
                'id_medecin' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_rendez_vous' => 3,
                'date_rendez_vous' => '2026-09-12',
                'heure_debut' => '11:00',
                'heure_fin' => '11:30',
                'motif' => 'Fièvre et toux',
                'statut' => 'confirme',
                'id_patient' => 3,
                'id_medecin' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_rendez_vous' => 4,
                'date_rendez_vous' => '2026-09-12',
                'heure_debut' => '14:00',
                'heure_fin' => '14:30',
                'motif' => 'Éruption cutanée',
                'statut' => 'en_attente',
                'id_patient' => 4,
                'id_medecin' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_rendez_vous' => 5,
                'date_rendez_vous' => '2026-09-13',
                'heure_debut' => '09:30',
                'heure_fin' => '10:00',
                'motif' => 'Contrôle de grossesse',
                'statut' => 'confirme',
                'id_patient' => 5,
                'id_medecin' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_rendez_vous' => 6,
                'date_rendez_vous' => '2026-09-14',
                'heure_debut' => '09:00',
                'heure_fin' => '09:30',
                'motif' => 'Maux de tête et vertiges',
                'statut' => 'confirme',
                'id_patient' => 6,
                'id_medecin' => 7,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_rendez_vous' => 7,
                'date_rendez_vous' => '2026-09-14',
                'heure_debut' => '10:00',
                'heure_fin' => '10:30',
                'motif' => 'Douleurs abdominales',
                'statut' => 'confirme',
                'id_patient' => 7,
                'id_medecin' => 8,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_rendez_vous' => 8,
                'date_rendez_vous' => '2026-09-15',
                'heure_debut' => '11:00',
                'heure_fin' => '11:30',
                'motif' => 'Fatigue et contrôle hormonal',
                'statut' => 'en_attente',
                'id_patient' => 8,
                'id_medecin' => 9,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Consultations
        |--------------------------------------------------------------------------
        */

        DB::table('consultations')->insert([
            [
                'id_consultation' => 1,
                'motif' => 'Douleurs thoraciques',
                'diagnostic' => 'Hypertension artérielle légère',
                'observations' => 'Réduire le sel et contrôler la tension.',
                'poids' => 82.5,
                'taille' => 178,
                'tension_arterielle' => '145/90',
                'temperature' => 36.8,
                'id_rendez_vous' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_consultation' => 2,
                'motif' => 'Éruption cutanée',
                'diagnostic' => 'Réaction allergique légère',
                'observations' => 'Éviter les produits allergènes.',
                'poids' => 65.2,
                'taille' => 165,
                'tension_arterielle' => '120/80',
                'temperature' => 36.7,
                'id_rendez_vous' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Médicaments
        |--------------------------------------------------------------------------
        */

        DB::table('medicaments')->insert([
            [
                'id_medicament' => 1,
                'nom_medicament' => 'Paracétamol',
                'forme' => 'Comprimé',
                'dosage' => '1 g',
                'fabricant' => 'Sothema Maroc',
                'description' => 'Antalgique et antipyrétique.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_medicament' => 2,
                'nom_medicament' => 'Amlodipine',
                'forme' => 'Comprimé',
                'dosage' => '5 mg',
                'fabricant' => 'Cooper Pharma',
                'description' => 'Traitement de l’hypertension artérielle.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_medicament' => 3,
                'nom_medicament' => 'Ibuprofène',
                'forme' => 'Comprimé',
                'dosage' => '400 mg',
                'fabricant' => 'Laprophan',
                'description' => 'Médicament anti-inflammatoire.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Ordonnances
        |--------------------------------------------------------------------------
        */

        DB::table('ordonnances')->insert([
            [
                'id_ordonnance' => 1,
                'date_ordonnance' => '2026-09-11',
                'instructions_generales' => 'Contrôler régulièrement la tension.',
                'duree_traitement' => '30 jours',
                'type' => 'Traitement chronique',
                'id_consultation' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_ordonnance' => 2,
                'date_ordonnance' => '2026-09-12',
                'instructions_generales' => 'Prendre le traitement après les repas.',
                'duree_traitement' => '7 jours',
                'type' => 'Traitement ponctuel',
                'id_consultation' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Lignes d’ordonnances
        |--------------------------------------------------------------------------
        */

        DB::table('ligne_ordonnances')->insert([
            [
                'dosologie' => '5 mg',
                'frequence' => 'Une fois par jour',
                'duree' => '30 jours',
                'quantite' => 30,
                'instructions' => 'Le matin à heure fixe.',
                'id_ordonnance' => 1,
                'id_medicament' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'dosologie' => '400 mg',
                'frequence' => 'Deux fois par jour',
                'duree' => '5 jours',
                'quantite' => 10,
                'instructions' => 'Après les repas.',
                'id_ordonnance' => 2,
                'id_medicament' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Demandes d’examen
        |--------------------------------------------------------------------------
        */

        DB::table('demandes_examen')->insert([
            [
                'id_demande_examen' => 1,
                'date_demande' => '2026-09-11',
                'niveau_urgence' => 'normal',
                'indications_cliniques' => 'Bilan lipidique et fonction rénale.',
                'statut' => 'realise',
                'date_prevue' => '2026-09-12',
                'date_realisation' => '2026-09-12',
                'observation' => 'Patient à jeun.',
                'id_consultation' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_demande_examen' => 2,
                'date_demande' => '2026-09-12',
                'niveau_urgence' => 'normal',
                'indications_cliniques' => 'Analyse allergologique.',
                'statut' => 'realise',
                'date_prevue' => '2026-09-13',
                'date_realisation' => '2026-09-13',
                'observation' => 'Recherche d’allergènes.',
                'id_consultation' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Résultats
        |--------------------------------------------------------------------------
        */

        DB::table('resultats')->insert([
            [
                'date_resultat' => '2026-09-12',
                'resultat_detaille' => 'Cholestérol légèrement élevé.',
                'conclusion' => 'Dyslipidémie modérée.',
                'valeurs_mesurees' => 'Cholestérol : 2.25 g/L',
                'fichier_resultat' => 'bilan_001.pdf',
                'image_resultat' => 'bilan_001.png',
                'observations' => 'Contrôle conseillé dans trois mois.',
                'id_demande_examen' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'date_resultat' => '2026-09-13',
                'resultat_detaille' => 'Réaction allergique modérée.',
                'conclusion' => 'Allergie saisonnière probable.',
                'valeurs_mesurees' => 'IgE légèrement élevées.',
                'fichier_resultat' => 'allergie_002.pdf',
                'image_resultat' => 'allergie_002.png',
                'observations' => 'Suivi dermatologique conseillé.',
                'id_demande_examen' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Chambres
        |--------------------------------------------------------------------------
        */

        DB::table('chambres')->insert([
            [
                'id_chambre' => 1,
                'numero_chambre' => '101',
                'type_chambre' => 'Individuelle',
                'etage' => '1er étage',
                'capacite' => 1,
                'tarif_journalier' => 650,
                'statut' => 'disponible',
                'description' => 'Chambre individuelle climatisée.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_chambre' => 2,
                'numero_chambre' => '102',
                'type_chambre' => 'Double',
                'etage' => '1er étage',
                'capacite' => 2,
                'tarif_journalier' => 450,
                'statut' => 'occupee',
                'description' => 'Chambre double avec salle de bain.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_chambre' => 3,
                'numero_chambre' => '201',
                'type_chambre' => 'Soins intensifs',
                'etage' => '2e étage',
                'capacite' => 1,
                'tarif_journalier' => 1800,
                'statut' => 'occupee',
                'description' => 'Chambre de surveillance intensive.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Hospitalisations
        |--------------------------------------------------------------------------
        */

        DB::table('hospitalisations')->insert([
            [
                'id_hospitalisation' => 1,
                'date_entree' => '2026-09-06',
                'heure_entree' => '18:30',
                'date_sortie' => null,
                'motif_hospitalisation' => 'Surveillance cardiaque',
                'diagnostic_entree' => 'Crise hypertensive',
                'statut' => 'en_cours',
                'observations' => 'Surveillance toutes les quatre heures.',
                'id_patient' => 2,
                'id_chambre' => 3,
                'id_medecin' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_hospitalisation' => 2,
                'date_entree' => '2026-09-03',
                'heure_entree' => '11:15',
                'date_sortie' => '2026-09-05',
                'motif_hospitalisation' => 'Observation après intervention',
                'diagnostic_entree' => 'Fracture traitée',
                'statut' => 'terminee',
                'observations' => 'Évolution favorable.',
                'id_patient' => 6,
                'id_chambre' => 2,
                'id_medecin' => 6,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Factures
        |--------------------------------------------------------------------------
        */

        DB::table('factures')->insert([
            [
                'id_facture' => 1,
                'numero_facture' => 'FAC-2026-0001',
                'date_facture' => '2026-09-11',
                'montant_total' => 650,
                'remise' => 50,
                'montant_net' => 600,
                'statut_paiement' => 'payee',
                'observations' => 'Consultation et bilan biologique.',
                'id_consultation' => 1,
                'id_hospitalisation' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_facture' => 2,
                'numero_facture' => 'FAC-2026-0002',
                'date_facture' => '2026-09-12',
                'montant_total' => 600,
                'remise' => 0,
                'montant_net' => 600,
                'statut_paiement' => 'partielle',
                'observations' => 'Consultation et analyses.',
                'id_consultation' => 2,
                'id_hospitalisation' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_facture' => 3,
                'numero_facture' => 'FAC-2026-0003',
                'date_facture' => '2026-09-05',
                'montant_total' => 1700,
                'remise' => 100,
                'montant_net' => 1600,
                'statut_paiement' => 'payee',
                'observations' => 'Hospitalisation en chambre double.',
                'id_consultation' => null,
                'id_hospitalisation' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Paiements
        |--------------------------------------------------------------------------
        */

        DB::table('paiements')->insert([
            [
                'date_paiement' => '2026-09-11',
                'heure_paiement' => '11:20',
                'montant_paye' => 600,
                'mode_paiement' => 'carte_bancaire',
                'statut' => 'valide',
                'observations' => 'Paiement par carte bancaire.',
                'id_facture' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'date_paiement' => '2026-09-12',
                'heure_paiement' => '16:10',
                'montant_paye' => 300,
                'mode_paiement' => 'especes',
                'statut' => 'valide',
                'observations' => 'Acompte reçu à la caisse.',
                'id_facture' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'date_paiement' => '2026-09-05',
                'heure_paiement' => '14:45',
                'montant_paye' => 1600,
                'mode_paiement' => 'virement',
                'statut' => 'valide',
                'observations' => 'Virement bancaire confirmé.',
                'id_facture' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Synchronisation des séquences PostgreSQL
        |--------------------------------------------------------------------------
        */

        $sequences = [
            ['specialites', 'id_specialite'],
            ['medecins', 'id_medecin'],
            ['patients', 'id_patient'],
            ['rendez_vous', 'id_rendez_vous'],
            ['consultations', 'id_consultation'],
            ['medicaments', 'id_medicament'],
            ['ordonnances', 'id_ordonnance'],
            ['demandes_examen', 'id_demande_examen'],
            ['chambres', 'id_chambre'],
            ['hospitalisations', 'id_hospitalisation'],
            ['factures', 'id_facture'],
        ];

        foreach ($sequences as [$table, $primaryKey]) {
            DB::statement(
                "SELECT setval(
                    pg_get_serial_sequence('$table', '$primaryKey'),
                    (SELECT MAX($primaryKey) FROM $table)
                )"
            );
        }
    }
}