<?php

namespace App\Http\Controllers;

use App\Models\Resultat;
use Illuminate\Http\Request;

class ResultatController extends Controller
{
    // Lister tous les résultats
    public function index()
    {
        $resultats = Resultat::with([
            'demandeExamen.consultation.rendezVous.patient',
            'demandeExamen.consultation.rendezVous.medecin'
        ])->get();

        return response()->json([
            'resultats' => $resultats
        ], 200);
    }

    // Afficher un résultat
    public function show($id)
    {
        $resultat = Resultat::with([
            'demandeExamen.consultation.rendezVous.patient',
            'demandeExamen.consultation.rendezVous.medecin'
        ])->findOrFail($id);

        return response()->json([
            'resultat' => $resultat
        ], 200);
    }

    // Ajouter un résultat
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_resultat' => ['required', 'date'],
            'resultat_detaille' => ['required', 'string'],
            'conclusion' => ['nullable', 'string'],
            'valeurs_mesurees' => ['nullable', 'string'],
            'fichier_resultat' => ['nullable', 'string'],
            'image_resultat' => ['nullable', 'string'],
            'observations' => ['nullable', 'string'],
            'id_demande_examen' => [
                'required',
                'integer',
                'exists:demandes_examen,id_demande_examen'
            ],
        ]);

        $resultat = Resultat::create($validated);

        return response()->json([
            'message' => 'Résultat ajouté avec succès',
            'resultat' => $resultat
        ], 201);
    }

    // Modifier un résultat
    public function update(Request $request, $id)
    {
        $resultat = Resultat::findOrFail($id);

        $validated = $request->validate([
            'date_resultat' => ['sometimes', 'required', 'date'],
            'resultat_detaille' => ['sometimes', 'required', 'string'],
            'conclusion' => ['sometimes', 'nullable', 'string'],
            'valeurs_mesurees' => ['sometimes', 'nullable', 'string'],
            'fichier_resultat' => ['sometimes', 'nullable', 'string'],
            'image_resultat' => ['sometimes', 'nullable', 'string'],
            'observations' => ['sometimes', 'nullable', 'string'],
            'id_demande_examen' => [
                'sometimes',
                'required',
                'integer',
                'exists:demandes_examen,id_demande_examen'
            ],
        ]);

        $resultat->update($validated);

        return response()->json([
            'message' => 'Résultat modifié avec succès',
            'resultat' => $resultat
        ], 200);
    }

    // Supprimer un résultat
    public function destroy($id)
    {
        $resultat = Resultat::findOrFail($id);

        $resultat->delete();

        return response()->json([
            'message' => 'Résultat supprimé avec succès'
        ], 200);
    }
}