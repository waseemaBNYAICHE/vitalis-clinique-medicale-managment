<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

class AiController extends Controller
{
    /**
     * Envoie les symptomes au service IA FastAPI,
     * puis recommande une specialite et les medecins correspondants.
     */
    public function predict(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'symptoms' => ['required', 'array', 'min:1'],
            'symptoms.*' => ['required', 'string'],
        ]);

        $aiServiceUrl = rtrim(
            env('AI_SERVICE_URL', 'http://ai:8000'),
            '/'
        );

        try {
            $response = Http::timeout(10)
                ->post($aiServiceUrl . '/predict', [
                    'symptoms' => $validated['symptoms'],
                ]);

            if ($response->status() === 400) {
                return response()->json(
                    $response->json(),
                    400
                );
            }

            if (!$response->successful()) {
                return response()->json([
                    'message' => 'AI service returned an error.',
                ], 502);
            }

            $prediction = $response->json();

            $disease = $prediction['disease'] ?? null;
            $confidence = $prediction['confidence'] ?? null;

            $specialityName = $this->getRecommendedSpeciality($disease);

            $speciality = DB::table('specialites')
                ->where('nom_specialite', $specialityName)
                ->first();

            $doctors = collect();

            if ($speciality) {
                $doctors = DB::table('medecins')
                    ->where('id_specialite', $speciality->id_specialite)
                    ->select(
                        'id_medecin',
                        'nom',
                        'prenom',
                        'telephone',
                        'email',
                        'tarif_consultation'
                    )
                    ->get();
            }

            return response()->json([
                'disease' => $disease,
                'confidence' => $confidence,
                'recommendation' => [
                    'speciality' => $specialityName,
                    'doctors' => $doctors,
                ],
            ]);

        } catch (ConnectionException $e) {
            return response()->json([
                'message' => 'AI service is currently unavailable.',
            ], 503);
        }
    }

    /**
     * Associe la prediction du modele a une specialite
     * disponible dans Vitalis.
     */
    private function getRecommendedSpeciality(?string $disease): string
    {
        return match ($disease) {
            'Migraine' => 'Neurologie',

            'Influenza',
            'Common_Cold',
            'Asthma',
            'Allergy' => 'Médecine générale',

            default => 'Médecine générale',
        };
    }
}