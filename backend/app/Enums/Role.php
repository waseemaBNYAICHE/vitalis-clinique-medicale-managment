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
     * SCRUM-524 - Meme logique etendue aux consultations, ordonnances,
     * examens et hospitalisations : la suppression reste reservee a
     * l'administrateur pour tous les dossiers medicaux. Le perimetre de
     * chaque role suit la maquette RBAC (Tous / Les siennes / Acces limite /
     * Lecture / Non) ; la distinction "toutes les donnees" vs "les siennes"
     * sera affinee au niveau des Policies quand les Controllers existeront.
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
                Permission::CONSULTATIONS_READ,
                Permission::CONSULTATIONS_CREATE,
                Permission::CONSULTATIONS_UPDATE,
                Permission::CONSULTATIONS_DELETE,
                Permission::ORDONNANCES_READ,
                Permission::ORDONNANCES_CREATE,
                Permission::ORDONNANCES_UPDATE,
                Permission::ORDONNANCES_DELETE,
                Permission::EXAMENS_READ,
                Permission::EXAMENS_CREATE,
                Permission::EXAMENS_UPDATE,
                Permission::EXAMENS_DELETE,
                Permission::HOSPITALISATIONS_READ,
                Permission::HOSPITALISATIONS_CREATE,
                Permission::HOSPITALISATIONS_UPDATE,
                Permission::HOSPITALISATIONS_DELETE,
            ],

            // Consultations/ordonnances/hospitalisations "les siennes" :
            // gere ses propres dossiers (lecture, creation, mise a jour),
            // jamais de suppression. Examens "les siens" : le medecin
            // consulte les resultats mais ne les saisit pas lui-meme.
            self::MEDECIN => [
                Permission::PATIENTS_READ,
                Permission::PATIENTS_CREATE,
                Permission::PATIENTS_UPDATE,
                Permission::CONSULTATIONS_READ,
                Permission::CONSULTATIONS_CREATE,
                Permission::CONSULTATIONS_UPDATE,
                Permission::ORDONNANCES_READ,
                Permission::ORDONNANCES_CREATE,
                Permission::ORDONNANCES_UPDATE,
                Permission::EXAMENS_READ,
                Permission::HOSPITALISATIONS_READ,
                Permission::HOSPITALISATIONS_CREATE,
                Permission::HOSPITALISATIONS_UPDATE,
            ],

            // Pas d'acces aux consultations ni ordonnances (donnees
            // cliniques). Acces limite en lecture aux examens et
            // hospitalisations pour la coordination administrative.
            self::SECRETAIRE => [
                Permission::PATIENTS_READ,
                Permission::PATIENTS_CREATE,
                Permission::PATIENTS_UPDATE,
                Permission::EXAMENS_READ,
                Permission::HOSPITALISATIONS_READ,
            ],

            // Acces limite en lecture aux consultations, lecture seule des
            // ordonnances. Gere pleinement les examens et hospitalisations
            // (saisie des resultats, suivi des sejours), sans suppression.
            self::INFIRMIER => [
                Permission::PATIENTS_READ,
                Permission::PATIENTS_CREATE,
                Permission::PATIENTS_UPDATE,
                Permission::CONSULTATIONS_READ,
                Permission::ORDONNANCES_READ,
                Permission::EXAMENS_READ,
                Permission::EXAMENS_CREATE,
                Permission::EXAMENS_UPDATE,
                Permission::HOSPITALISATIONS_READ,
                Permission::HOSPITALISATIONS_CREATE,
                Permission::HOSPITALISATIONS_UPDATE,
            ],

            // Un patient n'accede pas aux dossiers des autres patients.
            // Lecture seule de ses propres donnees medicales (le perimetre
            // "les siennes" est applique via Policy, pas ici).
            self::PATIENT => [
                Permission::CONSULTATIONS_READ,
                Permission::ORDONNANCES_READ,
                Permission::EXAMENS_READ,
                Permission::HOSPITALISATIONS_READ,
            ],
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