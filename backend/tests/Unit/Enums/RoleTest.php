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
     * Un patient n'a acces a aucune permission : il consulte ses propres
     * donnees via des routes dediees, pas via le systeme de permissions
     * generique reserve au personnel.
     */
    public function test_patient_na_aucune_permission(): void
    {
        $this->assertSame([], Role::PATIENT->permissions());
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