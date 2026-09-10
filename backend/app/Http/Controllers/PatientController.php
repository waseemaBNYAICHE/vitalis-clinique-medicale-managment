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

   public function destroy($id) { 
    $patient = Patient::findOrFail($id);
$hasRendezVous = \DB::table('rendez_vous')
    ->where('id_patient', $patient->id_patient)
    ->exists();

if ($hasRendezVous) {
    return response()->json([
        'message' => 'Impossible de supprimer ce patient car il possède des rendez-vous associés.'
    ], 409);
}

$patient->delete();

return response()->json([
    'message' => 'Patient supprimé avec succès.'
], 200);
}

   

   /**
    * Recherche d'un dossier patient.
    *
    * SCRUM-528 - Les deux filtres sont facultatifs. Appelee sans aucun
    * parametre, cette route executait Patient::query()->get() et renvoyait
    * donc l'INTEGRALITE du fichier patients, sans pagination et avec toutes
    * les donnees personnelles (CIN, telephone, email, date de naissance,
    * groupe sanguin).
    *
    * C'etait un contournement complet de la pagination de index(), qui se
    * limite volontairement a 10 dossiers par page : un seul GET suffisait a
    * exporter tout le fichier. La permission 'patients.read' autorise a
    * CONSULTER un dossier, pas a exporter le registre entier.
    *
    * Trois corrections : au moins un critere est exige, les criteres sont
    * valides, et le resultat est pagine comme celui de index().
    */
   public function search(Request $request)
   {
    $validated = $request->validate([
        'cin' => ['nullable', 'string', 'max:50'],
        'date_naissance' => ['nullable', 'date'],
    ]);

    $criteres = array_filter(
        $validated,
        fn ($valeur) => $valeur !== null && $valeur !== ''
    );

    if ($criteres === []) {
        return response()->json([
            'message' => 'Erreur de validation',
            'errors' => [
                'cin' => ['Indiquez au moins un critere de recherche (cin ou date_naissance).'],
            ],
        ], 422);
    }

    $query = Patient::query();

    if (isset($criteres['cin'])) {
        $query->where('cin', $criteres['cin']);
    }

    if (isset($criteres['date_naissance'])) {
        $query->where('date_naissance', $criteres['date_naissance']);
    }

    return response()->json([
        'patients' => $query->paginate(10)
    ], 200);
   }

}