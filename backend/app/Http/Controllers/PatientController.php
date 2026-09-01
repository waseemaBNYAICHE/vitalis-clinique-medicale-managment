<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
     public function store(Request $request)
    {
        $validated = $request->validate([
        'nom' => ['required', 'string', 'max:255'],
        'prenom' => ['required', 'string', 'max:255'],
        'date_naissance' => ['required', 'date', 'before:today'],
        'sexe' => ['required', 'string', 'max:20'],
        'cin' => ['required', 'string', 'max:50', 'unique:patients,cin'],
        'telephone' => ['required', 'string', 'max:20'],
        'email' => ['required', 'email', 'max:255', 'unique:patients,email'],
        'groupe_sanguin' => ['nullable', 'string', 'max:10'],
        ]);

    $patient = Patient::create($validated);

    return response()->json([
        'message' => 'Patient ajouté avec succès',
        'patient' => $patient
    ], 201);
    }

    public function show($id)
    {
        $patient = Patient::findOrFail($id);

       return response()->json([
        'patient' => $patient
       ], 200);
    } 

    public function update(Request $request, $id)
    {
    $patient = Patient::findOrFail($id);

    $validated = $request->validate([
        'nom' => ['required', 'string', 'max:255'],
        'prenom' => ['required', 'string', 'max:255'],
        'date_naissance' => ['required', 'date', 'before:today'],
        'sexe' => ['required', 'string', 'max:20'],
        'cin' => ['required', 'string', 'max:50', 'unique:patients,cin,' . $id . ',id_patient'],
        'telephone' => ['required', 'string', 'max:20'],
        'email' => ['required', 'email', 'max:255', 'unique:patients,email,' . $id . ',id_patient'],
        'groupe_sanguin' => ['nullable', 'string', 'max:10'],
    ]);

    $patient->update($validated);

    return response()->json([
        'message' => 'Patient modifié avec succès',
        'patient' => $patient
    ], 200);
}

}