<?php

namespace App\Http\Controllers;

use App\Models\RendezVous;
use Illuminate\Http\Request;

class RendezVousController extends Controller
{
    public function index()
    {
        $rendezVous = RendezVous::with(['patient', 'medecin'])->paginate(10);

        return response()->json([
            'rendez_vous' => $rendezVous,
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_rendez_vous' => ['required', 'date', 'after_or_equal:today'],
            'heure_debut' => ['required', 'date_format:H:i'],
            'heure_fin' => ['required', 'date_format:H:i', 'after:heure_debut'],
            'motif' => ['required', 'string', 'max:255'],
            'statut' => ['required', 'string', 'max:50'],
            'id_patient' => ['required', 'integer', 'exists:patients,id_patient'],
            'id_medecin' => ['required', 'integer', 'exists:medecins,id_medecin'],
        ]);

        $rendezVous = RendezVous::create($validated);

        return response()->json([
            'message' => 'Rendez-vous créé avec succès',
            'rendez_vous' => $rendezVous,
        ], 201);
    }

    public function show($id)
    {
        $rendezVous = RendezVous::with(['patient', 'medecin', 'consultation'])
            ->findOrFail($id);

        return response()->json([
            'rendez_vous' => $rendezVous,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $rendezVous = RendezVous::findOrFail($id);

        $validated = $request->validate([
            'date_rendez_vous' => ['required', 'date'],
            'heure_debut' => ['required', 'date_format:H:i'],
            'heure_fin' => ['required', 'date_format:H:i', 'after:heure_debut'],
            'motif' => ['required', 'string', 'max:255'],
            'statut' => ['required', 'string', 'max:50'],
            'id_patient' => ['required', 'integer', 'exists:patients,id_patient'],
            'id_medecin' => ['required', 'integer', 'exists:medecins,id_medecin'],
        ]);

        $rendezVous->update($validated);

        return response()->json([
            'message' => 'Rendez-vous modifié avec succès',
            'rendez_vous' => $rendezVous,
        ], 200);
    }

    public function destroy($id)
    {
        $rendezVous = RendezVous::findOrFail($id);
        $rendezVous->delete();

        return response()->json([
            'message' => 'Rendez-vous supprimé avec succès',
        ], 200);
    }
}