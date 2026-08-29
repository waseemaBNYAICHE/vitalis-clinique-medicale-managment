<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
           return response()->json(Patient::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    $validated = $request->validate([
        'nom' => 'required|string|max:255',
        'prenom' => 'required|string|max:255',
        'date_naissance' => 'required|date|before_or_equal:today',
        'sexe' => 'required|in:Homme,Femme',
        'cin' => 'required|string|max:50|unique:patients,cin',
        'telephone' => 'required|string|max:30',
        'email' => 'required|email|unique:patients,email',
        'groupe_sanguin' => 'nullable|string|max:10',
    ]);

    $patient = Patient::create($validated);

    return response()->json([
        'message' => 'Patient ajoute avec succes',
        'patient' => $patient,
    ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
            $patient = Patient::findOrFail($id);

            return response()->json($patient);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
            $patient = Patient::findOrFail($id);

        $validated = $request->validate([
        'nom' => 'required|string|max:255',
        'prenom' => 'required|string|max:255',
        'date_naissance' => 'required|date|before_or_equal:today',
        'sexe' => 'required|in:Homme,Femme',
        'cin' => 'required|string|max:50|unique:patients,cin,' . $patient->id_patient . ',id_patient',
        'telephone' => 'required|string|max:30',
        'email' => 'required|email|unique:patients,email,' . $patient->id_patient . ',id_patient',
        'groupe_sanguin' => 'nullable|string|max:10',
        ]);

    $patient->update($validated);

    return response()->json([
        'message' => 'Patient modifie avec succes',
        'patient' => $patient,
    ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
            $patient = Patient::findOrFail($id);

            $patient->delete();

            return response()->json([
            'message' => 'Patient supprime avec succes'
            ]);
    }
    }
