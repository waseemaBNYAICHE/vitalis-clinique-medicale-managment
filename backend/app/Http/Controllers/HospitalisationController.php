<?php

namespace App\Http\Controllers;

use App\Models\Hospitalisation;
use Illuminate\Http\Request;
use App\Models\Chambre;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HospitalisationController extends Controller
{
    // Lister toutes les hospitalisations
    public function index()
    {
        $hospitalisations = Hospitalisation::with([
            'patient',
            'chambre',
            'medecin'
        ])->get();

        return response()->json([
            'hospitalisations' => $hospitalisations
        ], 200);
    }

    // Afficher une hospitalisation
    public function show($id)
    {
        $hospitalisation = Hospitalisation::with([
            'patient',
            'chambre',
            'medecin'
        ])->findOrFail($id);

        return response()->json([
            'hospitalisation' => $hospitalisation
        ], 200);
    }

    // Ajouter une hospitalisation
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_entree' => ['required', 'date'],
            'heure_entree' => ['required', 'date_format:H:i'],
            'date_sortie' => ['nullable', 'date'],
            'motif_hospitalisation' => ['required', 'string'],
            'diagnostic_entree' => ['required', 'string'],
            'statut' => ['required', 'string'],
            'observations' => ['nullable', 'string'],

            'id_patient' => [
                'required',
                'integer',
                'exists:patients,id_patient'
            ],

            'id_chambre' => [
                'required',
                'integer',
                'exists:chambres,id_chambre'
            ],

            'id_medecin' => [
                'required',
                'integer',
                'exists:medecins,id_medecin'
            ],
        ]);

        $hospitalisation = Hospitalisation::create($validated);

        return response()->json([
            'message' => 'Hospitalisation ajoutée avec succès',
            'hospitalisation' => $hospitalisation
        ], 201);
    }

    // Modifier une hospitalisation
    public function update(Request $request, $id)
   {
            $hospitalisation = Hospitalisation::findOrFail($id);

             $validated = $request->validate([
            'date_entree' => ['sometimes', 'required', 'date'],
            'heure_entree' => ['sometimes', 'required', 'date_format:H:i'],
            'date_sortie' => ['sometimes', 'nullable', 'date'],
            'motif_hospitalisation' => ['sometimes', 'required', 'string'],
            'diagnostic_entree' => ['sometimes', 'required', 'string'],
            'statut' => ['sometimes', 'required', 'string'],
            'observations' => ['sometimes', 'nullable', 'string'],

            'id_patient' => [
                'sometimes',
                'required',
                'integer',
                'exists:patients,id_patient'
            ],

            'id_chambre' => [
                'sometimes',
                'required',
                'integer',
                'exists:chambres,id_chambre'
            ],

            'id_medecin' => [
                'sometimes',
                'required',
                'integer',
                'exists:medecins,id_medecin'
            ],
        ]);

        $hospitalisation->update($validated);

        return response()->json([
            'message' => 'Hospitalisation modifiée avec succès',
            'hospitalisation' => $hospitalisation
        ], 200);
    }


        // Admettre un patient
        public function admettre(Request $request)
    {
         $validated = $request->validate([
        'date_entree' => ['required', 'date'],
        'heure_entree' => ['required', 'date_format:H:i'],
        'motif_hospitalisation' => ['required', 'string'],
        'diagnostic_entree' => ['required', 'string'],
        'observations' => ['nullable', 'string'],

        'id_patient' => [
            'required',
            'integer',
            'exists:patients,id_patient'
        ],

        'id_chambre' => [
            'required',
            'integer',
            'exists:chambres,id_chambre'
        ],

        'id_medecin' => [
            'required',
            'integer',
            'exists:medecins,id_medecin'
        ],
    ]);

    $hospitalisation = DB::transaction(function () use ($validated) {

        $chambre = Chambre::findOrFail($validated['id_chambre']);

        if ($chambre->statut !== 'disponible') {
            throw ValidationException::withMessages([
                'id_chambre' => 'Cette chambre n\'est pas disponible.'
            ]);
    }

        $hospitalisation = Hospitalisation::create([
            ...$validated,
            'date_sortie' => null,
            'statut' => 'en_cours',
        ]);

        $chambre->update([
            'statut' => 'occupee'
        ]);

        return $hospitalisation;
    });

    return response()->json([
        'message' => 'Patient admis avec succès',
        'hospitalisation' => $hospitalisation
    ], 201);
    }

    // Supprimer une hospitalisation
    public function destroy($id)
    {
        $hospitalisation = Hospitalisation::findOrFail($id);

        $hospitalisation->delete();

        return response()->json([
            'message' => 'Hospitalisation supprimée avec succès'
        ], 200);
    }
}