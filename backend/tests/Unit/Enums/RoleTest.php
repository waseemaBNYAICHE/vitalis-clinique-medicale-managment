<?php

namespace Tests\Unit\Enums;

use App\Enums\Permission;
use App\Enums\Role;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * SCRUM-516 / SCRUM-520 - Tests unitaires de l'enum Role.
 *
 * Ces tests figent le comportement attendu de Role::permissions() et
 * Role::accorde(), qui constituent l'unique source de verite pour savoir
 * "qui a le droit de faire quoi" dans l'application. Toute modification
 * volontaire de cette matrice doit se traduire par une modification
 * assumee de ces tests, pas par leur suppression.
 */
class RoleTest extends TestCase
{
    /**
     * Les 5 roles metier doivent exister, ni plus ni moins.
     */
    public function test_il_existe_cinq_roles(): void
    {
        $this->assertCount(5, Role::cases());
    }

    /**
     * Les valeurs string des roles doivent rester stables : elles sont
     * stockees telles quelles en base (colonne users.role).
     */
    public function test_les_valeurs_des_roles_sont_stables(): void
    {
        $valeurs = array_map(fn (Role $role) => $role->value, Role::cases());

        $this->assertEqualsCanonicalizing([
            'administrateur',
            'medecin',
            'secretaire',
            'infirmier',
            'patient',
        ], $valeurs);
    }

    /**
     * L'administrateur a acces a toutes les permissions, y compris la
     * gestion des roles et la suppression de dossiers patients.
     */
    public function test_administrateur_a_toutes_les_permissions(): void
    {
        $permissions = Role::ADMINISTRATEUR->permissions();

        $this->assertContains(Permission::PATIENTS_READ, $permissions);
        $this->assertContains(Permission::PATIENTS_CREATE, $permissions);
        $this->assertContains(Permission::PATIENTS_UPDATE, $permissions);
        $this->assertContains(Permission::PATIENTS_DELETE, $permissions);
        $this->assertContains(Permission::ROLES_MANAGE, $permissions);
    }

    /**
     * Medecin, secretaire et infirmier partagent le meme perimetre :
     * lecture/creation/mise a jour des patients, mais ni suppression
     * ni gestion des roles.
     */
    #[DataProvider('rolesDuPersonnelSoignantEtAdministratif')]
    public function test_personnel_medical_et_administratif_na_pas_les_droits_sensibles(Role $role): void
    {
        $permissions = $role->permissions();

        $this->assertContains(Permission::PATIENTS_READ, $permissions);
        $this->assertContains(Permission::PATIENTS_CREATE, $permissions);
        $this->assertContains(Permission::PATIENTS_UPDATE, $permissions);
        $this->assertNotContains(Permission::PATIENTS_DELETE, $permissions);
        $this->assertNotContains(Permission::ROLES_MANAGE, $permissions);
    }

    public static function rolesDuPersonnelSoignantEtAdministratif(): array
    {
        return [
            'medecin' => [Role::MEDECIN],
            'secretaire' => [Role::SECRETAIRE],
            'infirmier' => [Role::INFIRMIER],
        ];
    }

    /**
     * SCRUM-524 - Un patient n'a pas acces au module Patients (reserve au
     * personnel), mais a un acces en lecture seule a ses propres donnees
     * medicales (consultations, ordonnances, examens, hospitalisations).
     */
    public function test_patient_na_pas_acces_au_module_patients(): void
    {
        $permissions = Role::PATIENT->permissions();

        $this->assertNotContains(Permission::PATIENTS_READ, $permissions);
        $this->assertNotContains(Permission::PATIENTS_CREATE, $permissions);
        $this->assertNotContains(Permission::PATIENTS_UPDATE, $permissions);
        $this->assertNotContains(Permission::PATIENTS_DELETE, $permissions);
    }

    public function test_patient_a_un_acces_lecture_seule_a_ses_donnees_medicales(): void
    {
        $permissions = Role::PATIENT->permissions();

        $this->assertContains(Permission::CONSULTATIONS_READ, $permissions);
        $this->assertContains(Permission::ORDONNANCES_READ, $permissions);
        $this->assertContains(Permission::EXAMENS_READ, $permissions);
        $this->assertContains(Permission::HOSPITALISATIONS_READ, $permissions);

        // Aucun droit d'ecriture ni de suppression sur ses propres donnees.
        $this->assertNotContains(Permission::CONSULTATIONS_CREATE, $permissions);
        $this->assertNotContains(Permission::CONSULTATIONS_DELETE, $permissions);
    }

    /**
     * SCRUM-524 - Suppression des dossiers medicaux reservee a l'administrateur.
     */
    public function test_seul_administrateur_peut_supprimer_les_donnees_medicales(): void
    {
        $this->assertTrue(Role::ADMINISTRATEUR->accorde(Permission::CONSULTATIONS_DELETE));
        $this->assertTrue(Role::ADMINISTRATEUR->accorde(Permission::ORDONNANCES_DELETE));
        $this->assertTrue(Role::ADMINISTRATEUR->accorde(Permission::EXAMENS_DELETE));
        $this->assertTrue(Role::ADMINISTRATEUR->accorde(Permission::HOSPITALISATIONS_DELETE));

        $this->assertFalse(Role::MEDECIN->accorde(Permission::CONSULTATIONS_DELETE));
        $this->assertFalse(Role::INFIRMIER->accorde(Permission::EXAMENS_DELETE));
        $this->assertFalse(Role::SECRETAIRE->accorde(Permission::HOSPITALISATIONS_DELETE));
    }

    /**
     * SCRUM-524 - Infirmier gere pleinement les examens et hospitalisations
     * (hors suppression), mais n'a qu'un acces limite aux consultations et
     * ordonnances.
     */
    public function test_infirmier_gere_examens_et_hospitalisations_sans_suppression(): void
    {
        $permissions = Role::INFIRMIER->permissions();

        $this->assertContains(Permission::EXAMENS_CREATE, $permissions);
        $this->assertContains(Permission::EXAMENS_UPDATE, $permissions);
        $this->assertNotContains(Permission::EXAMENS_DELETE, $permissions);

        $this->assertContains(Permission::CONSULTATIONS_READ, $permissions);
        $this->assertNotContains(Permission::CONSULTATIONS_CREATE, $permissions);
    }

    /**
     * SCRUM-524 - Secretaire n'a aucun acces aux consultations ni
     * ordonnances (donnees cliniques), seulement une lecture limitee des
     * examens et hospitalisations.
     */
    public function test_secretaire_na_pas_acces_aux_donnees_cliniques(): void
    {
        $permissions = Role::SECRETAIRE->permissions();

        $this->assertNotContains(Permission::CONSULTATIONS_READ, $permissions);
        $this->assertNotContains(Permission::ORDONNANCES_READ, $permissions);
        $this->assertContains(Permission::EXAMENS_READ, $permissions);
        $this->assertContains(Permission::HOSPITALISATIONS_READ, $permissions);
    }

    /**
     * accorde() doit refleter fidelement le contenu de permissions().
     */
    public function test_accorde_retourne_vrai_si_la_permission_est_dans_la_liste(): void
    {
        $this->assertTrue(Role::ADMINISTRATEUR->accorde(Permission::ROLES_MANAGE));
        $this->assertTrue(Role::MEDECIN->accorde(Permission::PATIENTS_READ));
    }

    public function test_accorde_retourne_faux_si_la_permission_nest_pas_dans_la_liste(): void
    {
        $this->assertFalse(Role::MEDECIN->accorde(Permission::ROLES_MANAGE));
        $this->assertFalse(Role::SECRETAIRE->accorde(Permission::PATIENTS_DELETE));
        $this->assertFalse(Role::PATIENT->accorde(Permission::PATIENTS_READ));
    }
}