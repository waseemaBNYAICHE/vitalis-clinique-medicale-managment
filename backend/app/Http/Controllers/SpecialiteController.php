<?php

namespace App\Http\Controllers;

use App\Models\Specialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpecialiteController extends Controller
{
    public function index()
    {
        $specialites = Specialite::all();

        return response()->json([
            'specialites' => $specialites
        ], 200);
    }

    public function show($id)
    {
    $specialite = Specialite::findOrFail($id);

    return response()->json([
        'specialite' => $specialite
    ], 200);
    }

    public function store(Request $request)
    {
    // SCRUM-567 : bornes alignees sur le schema (VARCHAR(255),
    // DECIMAL(10,2)). Sans elles, une valeur surdimensionnee passait la
    // validation et n'echouait qu'en base, en 500.
    $validated = $request->validate([
        'nom_specialite' => 'required|string|max:255',
        'description' => 'nullable|string|max:2000',
    ]);

    $specialite = Specialite::create($validated);

    return response()->json([
        'message' => 'Spécialité ajoutée avec succès',
        'specialite' => $specialite
    ], 201);
    }

    public function update(Request $request, $id)
   {
    $specialite = Specialite::findOrFail($id);

    $validated = $request->validate([
        'nom_specialite' => 'required|string|max:255',
        'description' => 'nullable|string|max:2000',
    ]);

    $specialite->update($validated);

    return response()->json([
        'message' => 'Spécialité modifiée avec succès',
        'specialite' => $specialite
    ], 200);
    }

public function destroy($id)
   {
    $specialite = Specialite::findOrFail($id);

    // SCRUM-563 : meme raison que pour les medecins - une specialite encore
    // rattachee a un medecin faisait remonter la contrainte de cle etrangere
    // en 500 au lieu d'un conflit metier.
    if (DB::table('medecins')->where('id_specialite', $specialite->id_specialite)->exists()) {
        return response()->json([
            'message' => 'Impossible de supprimer cette specialite car des medecins y sont rattaches.'
        ], 409);
    }

    $specialite->delete();

    return response()->json([
        'message' => 'Spécialité supprimée avec succès'
    ], 200);
    }
}
