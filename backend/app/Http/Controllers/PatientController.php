<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{   

    public function index()
   {
    $patients = Patient::paginate(10);

    return response()->json([
        'patients' => $patients
    ], 200);
   } 

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

   public function destroy($id)
   {
    $patient = Patient::findOrFail($id);

    $patient->delete();

    return response()->json([
        'message' => 'Patient supprimé avec succès'
    ], 200);
   }

   public function search(Request $request)
   {
    $query = Patient::query();

    if ($request->filled('cin')) {
        $query->where('cin', $request->cin);
    }

    if ($request->filled('date_naissance')) {
        $query->where('date_naissance', $request->date_naissance);
    }

    $patients = $query->get();

    return response()->json([
        'patients' => $patients
    ], 200);
   }

}