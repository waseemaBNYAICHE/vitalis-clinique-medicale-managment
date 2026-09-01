<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
     public function store(Request $request)
    {
        $patient = Patient::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'date_naissance' => $request->date_naissance,
            'sexe' => $request->sexe,
            'cin' => $request->cin,
            'telephone' => $request->telephone,
            'email' => $request->email,
            'groupe_sanguin' => $request->groupe_sanguin,
        ]);

        return response()->json([
            'message' => 'Patient ajouté avec succès',
            'patient' => $patient
        ], 201);
    }
}