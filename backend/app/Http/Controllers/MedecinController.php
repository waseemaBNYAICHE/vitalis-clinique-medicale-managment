<?php

namespace App\Http\Controllers;

use App\Models\Medecin;
use Illuminate\Http\Request;

class MedecinController extends Controller
{
    public function index()
    {
        $medecins = Medecin::all();

        return response()->json([
            'medecins' => $medecins
        ], 200);
    }

    public function show($id)
    {
    $medecin = Medecin::findOrFail($id);

    return response()->json([
        'medecin' => $medecin
    ], 200);
    }

    public function store(Request $request)
    {
    $validated = $request->validate([
        'matricule' => 'required|string|unique:medecins,matricule',
        'nom' => 'required|string',
        'prenom' => 'required|string',
        'telephone' => 'required|string',
        'email' => 'required|email|unique:medecins,email',
        'date_embauche' => 'required|date',
        'tarif_consultation' => 'required|numeric',
        'id_specialite' => 'required|exists:specialites,id_specialite',
    ]);

    $medecin = Medecin::create($validated);

    return response()->json([
        'message' => 'Médecin ajouté avec succès',
        'medecin' => $medecin
    ], 201);
   }

   public function update(Request $request, $id)
   {
    $medecin = Medecin::findOrFail($id);

    $validated = $request->validate([
        'matricule' => 'required|string|unique:medecins,matricule,' . $id . ',id_medecin',
        'nom' => 'required|string',
        'prenom' => 'required|string',
        'telephone' => 'required|string',
        'email' => 'required|email|unique:medecins,email,' . $id . ',id_medecin',
        'date_embauche' => 'required|date',
        'tarif_consultation' => 'required|numeric',
        'id_specialite' => 'required|exists:specialites,id_specialite',
    ]);

    $medecin->update($validated);

    return response()->json([
        'message' => 'Médecin modifié avec succès',
        'medecin' => $medecin
    ], 200);
    }

public function destroy($id)
   {
    $medecin = Medecin::findOrFail($id);

    $medecin->delete();

    return response()->json([
        'message' => 'Médecin supprimé avec succès'
    ], 200);
   }

}