<?php

namespace App\Auth;

use App\Enums\Role;
use App\Models\Consultation;
use App\Models\DemandeExamen;
use App\Models\Hospitalisation;
use App\Models\Ordonnance;
use App\Models\Patient;
use App\Models\RendezVous;
use App\Models\Resultat;
use App\Models\User;

/**
 * SCRUM-528 - A qui appartient un dossier, et cet utilisateur y a-t-il acces ?
 *
 * Les Gates de SCRUM-518 repondent a "cet utilisateur a-t-il le droit de lire
 * une consultation ?". Elles ne repondaient pas a "celle-ci ?". Tant qu'aucune
 * route ne passait de dossier precis, la question ne se posait pas ; elle se
 * posera des le premier controller de donnees medicales, et la reponse par
 * defaut aurait ete "oui" pour n'importe quel dossier.
 *
 * Cette classe fournit la piece manquante. Elle est volontairement reduite a
 * deux methodes statiques : ce n'est pas un service a injecter, juste la
 * traduction d'une regle metier ("un medecin suit ses patients") en code
 * verifiable.
 *
 * Elle resout l'appartenance par les CLES ETRANGERES et jamais par les
 * relations Eloquent, pour une raison precise : selon le modele, patient()
 * renvoie soit un ?Patient (Consultation, Ordonnance, DemandeExamen) soit un
 * BelongsTo (Hospitalisation, RendezVous). Un objet BelongsTo est toujours
 * "truthy" : un controle d'acces ecrit dessus semblerait fonctionner tout en
 * laissant passer les mauvais dossiers. Les colonnes id_patient / id_medecin,
 * elles, ont le meme sens partout.
 */
final class PerimetreDossier
{
    /**
     * Cet utilisateur peut-il acceder a ce dossier precis ?
     *
     * Repond false par defaut : un modele dont on ne sait pas rattacher le
     * proprietaire n'est pas accessible a un role limite a ses propres
     * dossiers. Se tromper dans ce sens bloque un acces legitime ; se tromper
     * dans l'autre expose un dossier medical.
     */
    public static function accessible(User $user, object $dossier): bool
    {
        $role = Role::tryFrom((string) $user->role);

        if ($role === null) {
            return false;
        }

        if ($role->accedeATousLesDossiers()) {
            return true;
        }

        $proprietaires = self::proprietaires($dossier);

        if ($proprietaires === null) {
            return false;
        }

        return match ($role) {
            Role::MEDECIN => $user->id_medecin !== null
                && $proprietaires['id_medecin'] !== null
                && (int) $proprietaires['id_medecin'] === (int) $user->id_medecin,

            Role::PATIENT => $user->id_patient !== null
                && $proprietaires['id_patient'] !== null
                && (int) $proprietaires['id_patient'] === (int) $user->id_patient,

            default => false,
        };
    }

    /**
     * Remonte la chaine des cles etrangeres jusqu'au patient et au medecin.
     *
     * La hierarchie du MLD est : rendez-vous -> consultation -> ordonnance /
     * demande d'examen -> resultat. Seuls le rendez-vous et l'hospitalisation
     * portent directement id_patient et id_medecin ; tout le reste s'y
     * rattache de proche en proche.
     *
     * Renvoie null si le modele n'est pas un dossier rattachable.
     *
     * @return array{id_patient: int|null, id_medecin: int|null}|null
     */
    private static function proprietaires(object $dossier): ?array
    {
        return match (true) {
            $dossier instanceof RendezVous => [
                'id_patient' => $dossier->id_patient,
                'id_medecin' => $dossier->id_medecin,
            ],

            $dossier instanceof Hospitalisation => [
                'id_patient' => $dossier->id_patient,
                'id_medecin' => $dossier->id_medecin,
            ],

            // Un patient est son propre dossier ; aucun medecin ne lui est
            // rattache a ce niveau.
            $dossier instanceof Patient => [
                'id_patient' => $dossier->id_patient,
                'id_medecin' => null,
            ],

            $dossier instanceof Consultation => self::viaRendezVous($dossier->id_rendez_vous),

            $dossier instanceof Ordonnance,
            $dossier instanceof DemandeExamen => self::viaConsultation($dossier->id_consultation),

            $dossier instanceof Resultat => self::viaDemandeExamen($dossier->id_demande_examen),

            default => null,
        };
    }

    /**
     * @return array{id_patient: int|null, id_medecin: int|null}|null
     */
    private static function viaRendezVous(?int $idRendezVous): ?array
    {
        if ($idRendezVous === null) {
            return null;
        }

        $rendezVous = RendezVous::find($idRendezVous);

        return $rendezVous === null ? null : [
            'id_patient' => $rendezVous->id_patient,
            'id_medecin' => $rendezVous->id_medecin,
        ];
    }

    /**
     * @return array{id_patient: int|null, id_medecin: int|null}|null
     */
    private static function viaConsultation(?int $idConsultation): ?array
    {
        if ($idConsultation === null) {
            return null;
        }

        $consultation = Consultation::find($idConsultation);

        return $consultation === null
            ? null
            : self::viaRendezVous($consultation->id_rendez_vous);
    }

    /**
     * @return array{id_patient: int|null, id_medecin: int|null}|null
     */
    private static function viaDemandeExamen(?int $idDemandeExamen): ?array
    {
        if ($idDemandeExamen === null) {
            return null;
        }

        $demande = DemandeExamen::find($idDemandeExamen);

        return $demande === null
            ? null
            : self::viaConsultation($demande->id_consultation);
    }
}
