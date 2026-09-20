<?php

namespace App\Http\Controllers;

use App\Enums\StatutFacture;
use App\Models\Facture;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaiementController extends Controller
{
    // Lister tous les paiements
    public function index()
    {
        $paiements = Paiement::all();

        return response()->json([
            'paiements' => $paiements
        ], 200);
    }

    // Afficher un paiement
    public function show($id)
    {
        $paiement = Paiement::findOrFail($id);

        return response()->json([
            'paiement' => $paiement
        ], 200);
    }

    // Ajouter un paiement
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_paiement' => [
                'required',
                'date'
            ],

            'heure_paiement' => [
                'required',
                'date_format:H:i'
            ],

            'montant_paye' => [
                'required',
                'numeric',
                'min:0.01'
            ],

            'mode_paiement' => [
                'required',
                Rule::in([
                    'especes',
                    'carte_bancaire',
                    'virement'
                ])
            ],

            'statut' => [
                'required',
                Rule::in([
                    'valide',
                    'annule'
                ])
            ],

            'observations' => [
                'nullable',
                'string'
            ],

            'id_facture' => [
                'required',
                'integer',
                'exists:factures,id_facture'
            ],
        ]);

        // Récupérer la facture concernée
        $facture = Facture::findOrFail($validated['id_facture']);

        // Interdire un paiement sur une facture annulée
        if ($facture->statut_paiement === StatutFacture::ANNULEE->value) {
            throw ValidationException::withMessages([
                'id_facture' => 'Impossible d\'ajouter un paiement à une facture annulée.'
            ]);
        }

        // Calculer le total déjà payé
        $totalDejaPaye = (float) $facture->paiements()
            ->where('statut', 'valide')
            ->sum('montant_paye');

        // Calculer le reste à payer
        $resteAPayer =
            (float) $facture->montant_net - $totalDejaPaye;

        // Empêcher un paiement supérieur au reste à payer
        if (
            $validated['statut'] === 'valide'
            && (float) $validated['montant_paye'] > $resteAPayer
        ) {
            throw ValidationException::withMessages([
                'montant_paye' => 'Le montant du paiement dépasse le reste à payer.'
            ]);
        }

        // Enregistrer le paiement
        $paiement = Paiement::create($validated);

        // Mettre à jour automatiquement le statut de la facture
        $this->synchroniserStatutFacture($facture);

        return response()->json([
            'message' => 'Paiement ajouté avec succès',
            'paiement' => $paiement
        ], 201);
    }

    // Modifier un paiement
    public function update(Request $request, $id)
    {
        $paiement = Paiement::findOrFail($id);

        // Conserver l'ancienne facture avant la modification
        $ancienneFacture = Facture::findOrFail(
            $paiement->id_facture
        );

        $validated = $request->validate([
            'date_paiement' => [
                'sometimes',
                'required',
                'date'
            ],

            'heure_paiement' => [
                'sometimes',
                'required',
                'date_format:H:i'
            ],

            'montant_paye' => [
                'sometimes',
                'required',
                'numeric',
                'min:0.01'
            ],

            'mode_paiement' => [
                'sometimes',
                'required',
                Rule::in([
                    'especes',
                    'carte_bancaire',
                    'virement'
                ])
            ],

            'statut' => [
                'sometimes',
                'required',
                Rule::in([
                    'valide',
                    'annule'
                ])
            ],

            'observations' => [
                'sometimes',
                'nullable',
                'string'
            ],

            'id_facture' => [
                'sometimes',
                'required',
                'integer',
                'exists:factures,id_facture'
            ],
        ]);

        // Récupérer les nouvelles valeurs
        // ou conserver les valeurs actuelles
        $idFacture = $validated['id_facture']
            ?? $paiement->id_facture;

        $montantPaye = (float) (
            $validated['montant_paye']
            ?? $paiement->montant_paye
        );

        $statut = $validated['statut']
            ?? $paiement->statut;

        // Récupérer la facture concernée
        $facture = Facture::findOrFail($idFacture);

        // Interdire la modification d'un paiement
        // associé à une facture annulée
        if ($facture->statut_paiement === StatutFacture::ANNULEE->value) {
            throw ValidationException::withMessages([
                'id_facture' => 'Impossible de modifier un paiement associé à une facture annulée.'
            ]);
        }

        // Calculer le total des autres paiements validés
        // sans compter le paiement actuellement modifié
        $totalAutresPaiements = (float) $facture->paiements()
            ->where('statut', 'valide')
            ->where(
                'id_paiement',
                '!=',
                $paiement->id_paiement
            )
            ->sum('montant_paye');

        // Calculer le reste à payer
        $resteAPayer =
            (float) $facture->montant_net
            - $totalAutresPaiements;

        // Empêcher le paiement de dépasser le reste à payer
        if (
            $statut === 'valide'
            && $montantPaye > $resteAPayer
        ) {
            throw ValidationException::withMessages([
                'montant_paye' => 'Le montant du paiement dépasse le reste à payer.'
            ]);
        }

        // Modifier le paiement
        $paiement->update($validated);

        // Mettre à jour la facture concernée
        $this->synchroniserStatutFacture($facture);

        // Si le paiement a changé de facture,
        // mettre également à jour l'ancienne facture
        if (
            $ancienneFacture->id_facture
            !== $facture->id_facture
        ) {
            $this->synchroniserStatutFacture(
                $ancienneFacture
            );
        }

        return response()->json([
            'message' => 'Paiement modifié avec succès',
            'paiement' => $paiement
        ], 200);
    }

    // Supprimer un paiement
    public function destroy($id)
    {
        $paiement = Paiement::findOrFail($id);

        // Récupérer la facture avant de supprimer le paiement
        $facture = Facture::findOrFail(
            $paiement->id_facture
        );

        $paiement->delete();

        // Mettre à jour le statut de la facture
        // après la suppression
        $this->synchroniserStatutFacture($facture);

        return response()->json([
            'message' => 'Paiement supprimé avec succès'
        ], 200);
    }

    // Mettre à jour automatiquement le statut de la facture
    // selon le total des paiements validés
    private function synchroniserStatutFacture(
        Facture $facture
    ): void {
        // Une facture annulée doit conserver son statut
        if (
            $facture->statut_paiement
            === StatutFacture::ANNULEE->value
        ) {
            return;
        }

        // Calculer le total des paiements validés
        $totalPaye = (float) $facture->paiements()
            ->where('statut', 'valide')
            ->sum('montant_paye');

        $montantNet = (float) $facture->montant_net;

        // Déterminer automatiquement le statut
        if ($totalPaye <= 0) {
            $statut = StatutFacture::IMPAYEE->value;
        } elseif ($totalPaye < $montantNet) {
            $statut = StatutFacture::PARTIELLE->value;
        } else {
            $statut = StatutFacture::PAYEE->value;
        }

        // Mettre à jour le statut de la facture
        $facture->update([
            'statut_paiement' => $statut
        ]);
    }
}