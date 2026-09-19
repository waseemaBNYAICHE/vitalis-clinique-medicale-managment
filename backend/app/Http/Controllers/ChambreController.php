<?php

namespace App\Http\Controllers;

use App\Models\Chambre;
use Illuminate\Http\Request;

class ChambreController extends Controller
{
    /**
     * Lister toutes les chambres.
     */
    public function index()
    {
        $chambres = Chambre::all();

        return response()->json($chambres, 200);
    }

    /**
     * Ajouter une nouvelle chambre.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero_chambre' => ['required', 'string', 'max:255'],
            'type_chambre' => ['required', 'string', 'max:255'],
            'etage' => ['required', 'string', 'max:255'],
            'capacite' => ['required', 'integer', 'min:1'],
            'tarif_journalier' => ['required', 'numeric', 'min:0'],
            'statut' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        $chambre = Chambre::create($validated);

        return response()->json([
            'message' => 'Chambre ajoutée avec succès',
            'chambre' => $chambre,
        ], 201);
    }

    /**
     * Afficher une chambre.
     */
    public function show($id)
    {
        $chambre = Chambre::findOrFail($id);

        return response()->json($chambre, 200);
    }

    /**
     * Modifier une chambre.
     */
    public function update(Request $request, $id)
    {
        $chambre = Chambre::findOrFail($id);

        $validated = $request->validate([
            'numero_chambre' => ['sometimes', 'required', 'string', 'max:255'],
            'type_chambre' => ['sometimes', 'required', 'string', 'max:255'],
            'etage' => ['sometimes', 'required', 'string', 'max:255'],
            'capacite' => ['sometimes', 'required', 'integer', 'min:1'],
            'tarif_journalier' => ['sometimes', 'required', 'numeric', 'min:0'],
            'statut' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
        ]);

        $chambre->update($validated);

        return response()->json([
            'message' => 'Chambre modifiée avec succès',
            'chambre' => $chambre,
        ], 200);
    }

    /**
     * Supprimer une chambre.
     */
    public function destroy($id)
    {
        $chambre = Chambre::findOrFail($id);

        $chambre->delete();

        return response()->json([
            'message' => 'Chambre supprimée avec succès',
        ], 200);
    }
}
