<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\ContactAdministrateurNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ContactAdministrateurController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fullName' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:254'],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $admins = User::where('role', 'administrateur')->get();

        if ($admins->isEmpty()) {
            return response()->json([
                'message' => 'Aucun administrateur disponible.',
            ], 503);
        }

        Notification::send(
            $admins,
            new ContactAdministrateurNotification(
                trim($validated['fullName']),
                $validated['email'],
                trim($validated['subject']),
                trim($validated['message'])
            )
        );

        return response()->json([
            'message' => 'Votre message a été envoyé à l’administrateur.',
        ], 201);
    }
}