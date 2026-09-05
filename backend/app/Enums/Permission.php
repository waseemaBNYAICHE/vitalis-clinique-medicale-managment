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
 *
 * SCRUM-524 - Ajout des permissions sur les donnees medicales (consultations,
 * ordonnances, examens, hospitalisations). La suppression reste partout
 * reservee a l'administrateur : ce sont des dossiers medicaux, jamais
 * supprimes par le personnel soignant ou administratif.
 *
 * Ces permissions ne distinguent pas encore "toutes les donnees" de
 * "seulement les siennes" (voir maquette RBAC) : cette restriction par
 * propriete sera appliquee au niveau des Policies quand les Controllers
 * seront ecrits, pas dans cet enum.
 */
enum Permission: string
{
    case PATIENTS_READ = 'patients.read';
    case PATIENTS_CREATE = 'patients.create';
    case PATIENTS_UPDATE = 'patients.update';
    case PATIENTS_DELETE = 'patients.delete';
    case ROLES_MANAGE = 'roles.manage';

    case CONSULTATIONS_READ = 'consultations.read';
    case CONSULTATIONS_CREATE = 'consultations.create';
    case CONSULTATIONS_UPDATE = 'consultations.update';
    case CONSULTATIONS_DELETE = 'consultations.delete';

    case ORDONNANCES_READ = 'ordonnances.read';
    case ORDONNANCES_CREATE = 'ordonnances.create';
    case ORDONNANCES_UPDATE = 'ordonnances.update';
    case ORDONNANCES_DELETE = 'ordonnances.delete';

    case EXAMENS_READ = 'examens.read';
    case EXAMENS_CREATE = 'examens.create';
    case EXAMENS_UPDATE = 'examens.update';
    case EXAMENS_DELETE = 'examens.delete';

    case HOSPITALISATIONS_READ = 'hospitalisations.read';
    case HOSPITALISATIONS_CREATE = 'hospitalisations.create';
    case HOSPITALISATIONS_UPDATE = 'hospitalisations.update';
    case HOSPITALISATIONS_DELETE = 'hospitalisations.delete';
}