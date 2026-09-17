<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

class AiController extends Controller
{
    /**
     * Envoie les symptômes au service IA FastAPI
     * et retourne la prédiction au frontend.
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

            if ($response->successful()) {
                return response()->json($response->json());
            }

            // Conserver les erreurs de validation retournées par FastAPI.
            if ($response->status() === 400) {
                return response()->json(
                    $response->json(),
                    400
                );
            }

            return response()->json([
                'message' => 'AI service returned an error.',
            ], 502);

        } catch (ConnectionException $e) {
            return response()->json([
                'message' => 'AI service is currently unavailable.',
            ], 503);
        }
    }
}