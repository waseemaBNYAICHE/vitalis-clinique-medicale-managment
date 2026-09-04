<?php

namespace App\Enums;

enum Role: string
{
    case ADMINISTRATEUR = 'administrateur';
    case MEDECIN = 'medecin';
    case SECRETAIRE = 'secretaire';
    case INFIRMIER = 'infirmier';
    case PATIENT = 'patient';

    /**
     * SCRUM-518 - Permissions accordees a ce role.
     *
     * C'est l'unique endroit ou se decide "qui a le droit de faire quoi".
     *
     * La suppression d'un patient est reservee a l'administrateur : c'est une
     * action irreversible sur un dossier medical. La lecture, la creation et la
     * mise a jour restent ouvertes a l'ensemble du personnel, comme avant.
     *
     * @return array<int, Permission>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::ADMINISTRATEUR => [
                Permission::PATIENTS_READ,
                Permission::PATIENTS_CREATE,
                Permission::PATIENTS_UPDATE,
                Permission::PATIENTS_DELETE,
                Permission::ROLES_MANAGE,
            ],

            self::MEDECIN, self::SECRETAIRE, self::INFIRMIER => [
                Permission::PATIENTS_READ,
                Permission::PATIENTS_CREATE,
                Permission::PATIENTS_UPDATE,
            ],

            // Un patient n'accede pas aux dossiers des autres patients.
            self::PATIENT => [],
        };
    }

    /**
     * SCRUM-518 - Ce role accorde-t-il la permission demandee ?
     */
    public function accorde(Permission $permission): bool
    {
        return in_array($permission, $this->permissions(), true);
    }
}
