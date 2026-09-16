<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Enums\StatutFacture;
use App\Models\Facture;
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

        // Vérifier que le paiement ne dépasse pas le reste à payer
        $facture = Facture::findOrFail($validated['id_facture']);

        $totalDejaPaye = (float) $facture->paiements()
         ->where('statut', 'valide')
         ->sum('montant_paye');

        $resteAPayer = (float) $facture->montant_net - $totalDejaPaye;

        if (
             $validated['statut'] === 'valide'
             && (float) $validated['montant_paye'] > $resteAPayer
            ) {
        throw ValidationException::withMessages([
            'montant_paye' => 'Le montant du paiement dépasse le reste à payer.'
        ]);
        }   

        $paiement = Paiement::create($validated);

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
        $ancienneFacture = Facture::findOrFail($paiement->id_facture);

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

        // Vérifier le reste à payer lors de la modification d'un paiement
     $idFacture = $validated['id_facture'] ?? $paiement->id_facture;
     $montantPaye = (float) ($validated['montant_paye'] ?? $paiement->montant_paye);
     $statut = $validated['statut'] ?? $paiement->statut;

     $facture = Facture::findOrFail($idFacture);

     $totalAutresPaiements = (float) $facture->paiements()
      ->where('statut', 'valide')
      ->where('id_paiement', '!=', $paiement->id_paiement)
      ->sum('montant_paye');

     $resteAPayer = (float) $facture->montant_net - $totalAutresPaiements;

      if (
         $statut === 'valide'
         && $montantPaye > $resteAPayer
         ) {
      throw ValidationException::withMessages([
        'montant_paye' => 'Le montant du paiement dépasse le reste à payer.'
     ]);
    }

        $paiement->update($validated);

        $this->synchroniserStatutFacture($facture);

        // Resynchroniser aussi l'ancienne facture si le paiement a changé de facture
        if ($ancienneFacture->id_facture !== $facture->id_facture) {
        $this->synchroniserStatutFacture($ancienneFacture);
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
    $facture = Facture::findOrFail($paiement->id_facture);

    $paiement->delete();

    // Mettre à jour le statut de la facture après la suppression
    $this->synchroniserStatutFacture($facture);

    return response()->json([
        'message' => 'Paiement supprimé avec succès'
    ], 200);
    }

    // Mettre à jour automatiquement le statut de la facture selon le total des paiements validés    
    private function synchroniserStatutFacture(Facture $facture): void
    {
    $totalPaye = (float) $facture->paiements()
        ->where('statut', 'valide')
        ->sum('montant_paye');

    $montantNet = (float) $facture->montant_net;

    if ($totalPaye <= 0) {
        $statut = StatutFacture::IMPAYEE->value;
    } elseif ($totalPaye < $montantNet) {
        $statut = StatutFacture::PARTIELLE->value;
    } else {
        $statut = StatutFacture::PAYEE->value;
    }

    $facture->update([
        'statut_paiement' => $statut
    ]);
    }
}