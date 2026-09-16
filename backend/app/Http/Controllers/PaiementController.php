<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

        $paiement = Paiement::create($validated);

        return response()->json([
            'message' => 'Paiement ajouté avec succès',
            'paiement' => $paiement
        ], 201);
    }

    // Modifier un paiement
    public function update(Request $request, $id)
    {
        $paiement = Paiement::findOrFail($id);

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

        $paiement->update($validated);

        return response()->json([
            'message' => 'Paiement modifié avec succès',
            'paiement' => $paiement
        ], 200);
    }

    // Supprimer un paiement
    public function destroy($id)
    {
        $paiement = Paiement::findOrFail($id);

        $paiement->delete();

        return response()->json([
            'message' => 'Paiement supprimé avec succès'
        ], 200);
    }
}