<?php

namespace App\Enums;

/**
 * SCRUM-518 - Permissions de l'application.
 *
 * Un ROLE dit qui est l'utilisateur (medecin, secretaire...).
 * Une PERMISSION dit ce qu'il a le droit de faire (lire un patient, en
 * supprimer un...). Les routes s'appuient sur les permissions : le jour ou un
 * role change de perimetre, seule la table de correspondance de l'enum Role
 * est a modifier, pas les routes.
 *
 * Volontairement sans table en base ni package externe : la liste est courte
 * et connue a l'avance, un enum suffit et reste lisible.
 */
enum Permission: string
{
    case PATIENTS_READ = 'patients.read';
    case PATIENTS_CREATE = 'patients.create';
    case PATIENTS_UPDATE = 'patients.update';
    case PATIENTS_DELETE = 'patients.delete';
    case ROLES_MANAGE = 'roles.manage';
}
