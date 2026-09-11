<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\RendezVous;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * SCRUM-605 / SCRUM-607 - Rendez-vous.
 *
 * Le CRUD ajoute par SCRUM-49 portait bien ses permissions `rendez-vous.*`,
 * mais aucun cloisonnement : les cinq roles detiennent RENDEZ_VOUS_READ, la
 * permission ne distinguait donc personne. Concretement, un patient obtenait
 * l'agenda complet de la clinique - motif de consultation d'autrui compris -
 * et un medecin pouvait modifier, voire reattribuer, le rendez-vous d'un
 * confrere.
 *
 * Trois protections sont ajoutees, toutes en reutilisant l'existant :
 *
 * 1. Le cloisonnement, via les Gates a ressource de SCRUM-528. RendezVous
 *    porte id_patient et id_medecin en direct, PerimetreDossier le traite
 *    donc deja sans rien y ajouter.
 *
 * 2. La reattribution. Modifier un rendez-vous permet d'en changer le patient
 *    ou le medecin : on verifie le perimetre AVANT et APRES, sans quoi il
 *    suffirait de s'attribuer le rendez-vous d'autrui pour y acceder.
 *
 * 3. La minimisation des donnees. index() et show() chargeaient les modeles
 *    Patient et Medecin entiers : CIN, groupe sanguin, email et telephone du
 *    patient, matricule, date d'embauche et tarif du medecin. Un agenda n'a
 *    besoin que de l'identite.
 */
class RendezVousController extends Controller
{
    /**
     * Colonnes strictement necessaires a l'affichage d'un agenda.
     *
     * La cle primaire est incluse : sans elle, Eloquent ne sait pas rattacher
     * la relation chargee a son rendez-vous.
     */
    private const COLONNES_PATIENT = ['id_patient', 'nom', 'prenom'];

    private const COLONNES_MEDECIN = ['id_medecin', 'nom', 'prenom', 'id_specialite'];

    /**
     * Regles de validation d'un rendez-vous.
     *
     * Deja posees par SCRUM-49 et conservees telles quelles : dates bornees,
     * heure de fin posterieure au debut, cles etrangeres verifiees.
     *
     * @return array<string, array<int, string>>
     */
    private function regles(bool $creation): array
    {
        return [
            'date_rendez_vous' => $creation
                ? ['required', 'date', 'after_or_equal:today']
                : ['required', 'date'],
            'heure_debut' => ['required', 'date_format:H:i'],
            'heure_fin' => ['required', 'date_format:H:i', 'after:heure_debut'],
            'motif' => ['required', 'string', 'max:255'],
            'statut' => ['required', 'string', 'max:50'],
            'id_patient' => ['required', 'integer', 'exists:patients,id_patient'],
            'id_medecin' => ['required', 'integer', 'exists:medecins,id_medecin'],
        ];
    }

    /**
     * Restreint une requete au perimetre du role qui la formule.
     *
     * PerimetreDossier repond dossier par dossier ; pour une liste il faut
     * filtrer en base. La regle appliquee est la meme : un medecin son
     * agenda, un patient ses rendez-vous, le reste du personnel toute la
     * clinique.
     */
    private function limiterAuPerimetre(Builder $requete, Request $request): Builder
    {
        $utilisateur = $request->user();
        $role = Role::tryFrom((string) $utilisateur->role);

        if ($role === null) {
            // Role inattendu en base : on n'expose rien plutot que tout.
            return $requete->whereRaw('1 = 0');
        }

        if ($role->accedeATousLesDossiers()) {
            return $requete;
        }

        return $role === Role::MEDECIN
            ? $requete->where('id_medecin', $utilisateur->id_medecin)
            : $requete->where('id_patient', $utilisateur->id_patient);
    }

    public function index(Request $request)
    {
        $rendezVous = $this->limiterAuPerimetre(RendezVous::query(), $request)
            ->with([
                'patient:'.implode(',', self::COLONNES_PATIENT),
                'medecin:'.implode(',', self::COLONNES_MEDECIN),
            ])
            ->paginate(10);

        return response()->json([
            'rendez_vous' => $rendezVous,
        ], 200);
    }

    public function store(Request $request)
    {
        $valide = $request->validate($this->regles(creation: true));

        // Le rendez-vous n'est pas encore enregistre : la Gate lit ses cles
        // etrangeres pour verifier qu'un patient ne prend rendez-vous que
        // pour lui-meme, et un medecin que pour son propre agenda.
        $rendezVous = new RendezVous($valide);
        Gate::authorize('rendez-vous.create', $rendezVous);

        $rendezVous->save();

        return response()->json([
            'message' => 'Rendez-vous créé avec succès',
            'rendez_vous' => $rendezVous,
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $rendezVous = $this->trouverOuRefuser(RendezVous::find($id), $request);

        Gate::authorize('rendez-vous.read', $rendezVous);

        // La consultation rattachee contient le diagnostic : elle n'est
        // chargee qu'une fois l'acces au rendez-vous etabli.
        $rendezVous->load([
            'patient:'.implode(',', self::COLONNES_PATIENT),
            'medecin:'.implode(',', self::COLONNES_MEDECIN),
            'consultation',
        ]);

        return response()->json([
            'rendez_vous' => $rendezVous,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $rendezVous = $this->trouverOuRefuser(RendezVous::find($id), $request);

        // Avant : a-t-il le droit de toucher a CE rendez-vous ?
        Gate::authorize('rendez-vous.update', $rendezVous);

        $valide = $request->validate($this->regles(creation: false));

        // Apres : la modification ne doit pas servir a s'attribuer le
        // rendez-vous de quelqu'un d'autre, ni a le faire sortir de son
        // perimetre. On evalue la Gate sur les valeurs demandees avant de
        // les enregistrer.
        $apresModification = (clone $rendezVous)->fill($valide);
        Gate::authorize('rendez-vous.update', $apresModification);

        $rendezVous->update($valide);

        return response()->json([
            'message' => 'Rendez-vous modifié avec succès',
            'rendez_vous' => $rendezVous,
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $rendezVous = $this->trouverOuRefuser(RendezVous::find($id), $request);

        Gate::authorize('rendez-vous.delete', $rendezVous);

        // SCRUM-607 : une consultation rattachee rend la suppression
        // impossible en base. Sans ce controle, la contrainte de cle
        // etrangere remonterait en 500 au lieu d'un conflit metier - meme
        // traitement que PatientController et OrdonnanceController.
        if ($rendezVous->consultation()->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer ce rendez-vous car une consultation y est rattachée.',
            ], 409);
        }

        $rendezVous->delete();

        return response()->json([
            'message' => 'Rendez-vous supprimé avec succès',
        ], 200);
    }
}
