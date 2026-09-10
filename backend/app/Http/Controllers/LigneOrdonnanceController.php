<?php

namespace App\Http\Controllers;

use App\Models\LigneOrdonnance;
use Illuminate\Http\Request;

class LigneOrdonnanceController extends Controller
{
    public function index($idOrdonnance)
    {
        $lignes = LigneOrdonnance::where(
            'id_ordonnance',
            $idOrdonnance
        )->get();

        return response()->json([
            'lignes' => $lignes
        ], 200);
    }

    public function store(Request $request, $idOrdonnance)
    {
        $ligne = LigneOrdonnance::create([
            'dosologie' => $request->dosologie,
            'frequence' => $request->frequence,
            'duree' => $request->duree,
            'quantite' => $request->quantite,
            'instructions' => $request->instructions,
            'id_ordonnance' => $idOrdonnance,
            'id_medicament' => $request->id_medicament,
        ]);

        return response()->json([
            'message' => 'Médicament ajouté à l\'ordonnance avec succès',
            'ligne' => $ligne
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $ligne = LigneOrdonnance::findOrFail($id);

        $ligne->update(
            $request->only([
                'dosologie',
                'frequence',
                'duree',
                'quantite',
                'instructions',
                'id_medicament',
            ])
        );

        return response()->json([
            'message' => 'Contenu de l\'ordonnance modifié avec succès',
            'ligne' => $ligne
        ], 200);
    }

    public function destroy($id)
    {
        $ligne = LigneOrdonnance::findOrFail($id);

        $ligne->delete();

        return response()->json([
            'message' => 'Ligne supprimée de l\'ordonnance avec succès'
        ], 200);
    }
}
