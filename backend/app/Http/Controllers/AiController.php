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
 * AI SMART - Assistant conversationnel pour les patients.
 */
public function chat(Request $request): JsonResponse
{
    $validated = $request->validate([
        'message' => ['required', 'string', 'max:1000'],
    ]);

    $apiKey = env('GEMINI_API_KEY');

    if (!$apiKey) {
        return response()->json([
            'message' => 'AI SMART is not configured.',
        ], 503);
    }

    try {
        $prompt = <<<PROMPT
Tu es AI SMART, l'assistant virtuel de la clinique Vitalis.

Ton rôle :
- répondre simplement et poliment aux questions des patients ;
- donner uniquement des informations générales sur la santé ;
- aider à comprendre les fonctionnalités de Vitalis ;
- répondre en français ou en arabe selon la langue du patient ;
- ne jamais présenter une réponse comme un diagnostic médical certain ;
- ne pas prescrire de médicaments ;
- conseiller de consulter un professionnel de santé lorsque nécessaire ;
- en cas de symptômes potentiellement urgents, recommander de contacter
  rapidement les services médicaux appropriés.

Question du patient :
{$validated['message']}
PROMPT;
    $response = Http::timeout(20)
    ->withHeaders([
        'x-goog-api-key' => $apiKey,
    ])
    ->post(
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent',
        [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                ]
            );

        if (!$response->successful()) {
            return response()->json([
                'message' => 'AI SMART service returned an error.',
            ], 502);
        }

        $parts = data_get(
    $response->json(),
    'candidates.0.content.parts',
    []
);

$reply = collect($parts)
    ->pluck('text')
    ->filter()
    ->implode('');

        if (!$reply) {
            return response()->json([
                'message' => 'AI SMART could not generate a response.',
            ], 502);
        }

        return response()->json([
            'reply' => $reply,
        ]);

    } catch (ConnectionException $e) {
        return response()->json([
            'message' => 'AI SMART is currently unavailable.',
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