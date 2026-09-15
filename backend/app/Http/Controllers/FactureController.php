<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FactureController extends Controller
{
    // Lister toutes les factures
    public function index()
    {
        $factures = Facture::all();

        return response()->json([
            'factures' => $factures
        ], 200);
    }

    // Afficher une facture
    public function show($id)
    {
        $facture = Facture::findOrFail($id);

        return response()->json([
            'facture' => $facture
        ], 200);
    }

    // Ajouter une facture
    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero_facture' => [
                'required',
                'string',
                'max:255',
                'unique:factures,numero_facture'
            ],

            'date_facture' => [
                'required',
                'date'
            ],

            'montant_total' => [
                'required',
                'numeric',
                'min:0'
            ],

            'remise' => [
                'required',
                'numeric',
                'min:0'
            ],

            'montant_net' => [
                'required',
                'numeric',
                'min:0'
            ],

            'statut_paiement' => [
                'required',
                'string',
                'max:255'
            ],

            'observations' => [
                'nullable',
                'string'
            ],

            'id_consultation' => [
                'nullable',
                'integer',
                'exists:consultations,id_consultation'
            ],

            'id_hospitalisation' => [
                'nullable',
                'integer',
                'exists:hospitalisations,id_hospitalisation'
            ],
        ]);

        $facture = Facture::create($validated);

        return response()->json([
            'message' => 'Facture ajoutée avec succès',
            'facture' => $facture
        ], 201);
    }

    // Modifier une facture
    public function update(Request $request, $id)
    {
        $facture = Facture::findOrFail($id);

        $validated = $request->validate([
            'numero_facture' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('factures', 'numero_facture')
                    ->ignore($facture->id_facture, 'id_facture')
            ],

            'date_facture' => [
                'sometimes',
                'required',
                'date'
            ],

            'montant_total' => [
                'sometimes',
                'required',
                'numeric',
                'min:0'
            ],

            'remise' => [
                'sometimes',
                'required',
                'numeric',
                'min:0'
            ],

            'montant_net' => [
                'sometimes',
                'required',
                'numeric',
                'min:0'
            ],

            'statut_paiement' => [
                'sometimes',
                'required',
                'string',
                'max:255'
            ],

            'observations' => [
                'sometimes',
                'nullable',
                'string'
            ],

            'id_consultation' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:consultations,id_consultation'
            ],

            'id_hospitalisation' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:hospitalisations,id_hospitalisation'
            ],
        ]);

        $facture->update($validated);

        return response()->json([
            'message' => 'Facture modifiée avec succès',
            'facture' => $facture
        ], 200);
    }

    // Supprimer une facture
    public function destroy($id)
    {
        $facture = Facture::findOrFail($id);

        $facture->delete();

        return response()->json([
            'message' => 'Facture supprimée avec succès'
        ], 200);
    }
}