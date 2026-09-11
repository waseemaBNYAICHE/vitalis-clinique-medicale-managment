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
        $request->validate([
        'dosologie' => 'required|string|max:255',
        'frequence' => 'required|string|max:255',
        'duree' => 'required|string|max:255',
        'quantite' => 'required|integer|min:1',
        'instructions' => 'required|string',
        'id_medicament' => 'required|integer|exists:medicaments,id_medicament',
         ]);

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
        $request->validate([
          'dosologie' => 'sometimes|required|string|max:255',
          'frequence' => 'sometimes|required|string|max:255',
          'duree' => 'sometimes|required|string|max:255',
          'quantite' => 'sometimes|required|integer|min:1',
          'instructions' => 'sometimes|required|string',
          'id_medicament' => 'sometimes|required|integer|exists:medicaments,id_medicament',
        ]);

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
