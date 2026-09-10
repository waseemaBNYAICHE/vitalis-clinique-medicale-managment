<?php

namespace App\Http\Controllers;

use App\Models\Ordonnance;
use Illuminate\Http\Request;

class OrdonnanceController extends Controller
{
    public function index()
    {
        $ordonnances = Ordonnance::all();

        return response()->json([
            'ordonnances' => $ordonnances
        ], 200);
    }

    public function show($id)
    {
        $ordonnance = Ordonnance::findOrFail($id);

        return response()->json([
            'ordonnance' => $ordonnance
        ], 200);
    }

    public function store(Request $request)
    {
        $ordonnance = Ordonnance::create(
            $request->only([
                'date_ordonnance',
                'instructions_generales',
                'duree_traitement',
                'type',
                'id_consultation',
            ])
        );

        return response()->json([
            'message' => 'Ordonnance ajoutée avec succès',
            'ordonnance' => $ordonnance
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $ordonnance = Ordonnance::findOrFail($id);

        $ordonnance->update(
            $request->only([
                'date_ordonnance',
                'instructions_generales',
                'duree_traitement',
                'type',
                'id_consultation',
            ])
        );

        return response()->json([
            'message' => 'Ordonnance modifiée avec succès',
            'ordonnance' => $ordonnance
        ], 200);
    }

    public function historiquePatient($idPatient)
    {
        $ordonnances = Ordonnance::whereHas(
            'consultation.rendezVous', 
            function ($query) use ($idPatient) {
                $query->where('id_patient', $idPatient);
            }
        )
        ->orderBy('date_ordonnance', 'desc')
        ->get();

        return response()->json([
            'historique' => $ordonnances
        ], 200);
    }

    public function destroy($id)
    {
        $ordonnance = Ordonnance::findOrFail($id);

        $ordonnance->delete();

        return response()->json([
            'message' => 'Ordonnance supprimée avec succès'
        ], 200);
    }
}