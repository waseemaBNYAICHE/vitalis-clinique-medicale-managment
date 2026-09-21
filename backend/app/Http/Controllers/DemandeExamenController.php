<?php

namespace App\Http\Controllers;

use App\Models\DemandeExamen;
use Illuminate\Http\Request;

class DemandeExamenController extends Controller
{
    // Lister toutes les demandes d'examen
    public function index()
    {
        $demandes = DemandeExamen::with([
            'consultation.rendezVous.patient',
            'consultation.rendezVous.medecin',
            'resultat'
        ])->get();

        return response()->json([
            'demandes_examens' => $demandes
        ], 200);
    }

    // Afficher une demande d'examen
    public function show($id)
    {
        $demande = DemandeExamen::with([
            'consultation.rendezVous.patient',
            'consultation.rendezVous.medecin',
            'resultat'
        ])->findOrFail($id);

        return response()->json([
            'demande_examen' => $demande
        ], 200);
    }

    // Ajouter une demande d'examen
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_demande' => ['required', 'date'],
            'type_examen' => ['required', 'string', 'max:255'],
            'niveau_urgence' => ['required', 'string'],
            'indications_cliniques' => ['required', 'string'],
            'statut' => ['required', 'string'],
            'date_prevue' => ['required', 'date'],
            'date_realisation' => ['nullable', 'date'],
            'observation' => ['nullable', 'string'],

            'id_consultation' => [
                'required',
                'integer',
                'exists:consultations,id_consultation'
            ],
        ]);

        $demande = DemandeExamen::create($validated);

        return response()->json([
            'message' => 'Demande d\'examen ajoutée avec succès',
            'demande_examen' => $demande
        ], 201);
    }

    // Modifier une demande d'examen
    public function update(Request $request, $id)
    {
        $demande = DemandeExamen::findOrFail($id);

        $validated = $request->validate([
            'date_demande' => ['sometimes', 'required', 'date'],
            'type_examen' => ['sometimes', 'required', 'string', 'max:255'],
            'niveau_urgence' => ['sometimes', 'required', 'string'],
            'indications_cliniques' => ['sometimes', 'required', 'string'],
            'statut' => ['sometimes', 'required', 'string'],
            'date_prevue' => ['sometimes', 'required', 'date'],
            'date_realisation' => ['sometimes', 'nullable', 'date'],
            'observation' => ['sometimes', 'nullable', 'string'],

            'id_consultation' => [
                'sometimes',
                'required',
                'integer',
                'exists:consultations,id_consultation'
            ],
        ]);

        $demande->update($validated);

        return response()->json([
            'message' => 'Demande d\'examen modifiée avec succès',
            'demande_examen' => $demande
        ], 200);
    }

    // Supprimer une demande d'examen
    public function destroy($id)
    {
        $demande = DemandeExamen::findOrFail($id);

        $demande->delete();

        return response()->json([
            'message' => 'Demande d\'examen supprimée avec succès'
        ], 200);
    }
}