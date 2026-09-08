<?php

namespace App\Http\Controllers;

use App\Models\Specialite;
use Illuminate\Http\Request;

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
    $validated = $request->validate([
        'nom_specialite' => 'required|string',
        'description' => 'nullable|string',
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
        'nom_specialite' => 'required|string',
        'description' => 'nullable|string',
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

    $specialite->delete();

    return response()->json([
        'message' => 'Spécialité supprimée avec succès'
    ], 200);
    }
}
