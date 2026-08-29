<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Routes publiques d'authentification
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);



// Routes accessibles uniquement aux utilisateurs authentifies
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    /*
    |--------------------------------------------------------------------------
    | Gestion des patients
    |--------------------------------------------------------------------------
    */

    // Consulter la liste des patients
    Route::get('/patients', [PatientController::class, 'index']);
    // Ajouter un nouveau patient
    Route::post('/patients', [PatientController::class, 'store']);
     // consulter un patient
    Route::get('/patients/{id}', [PatientController::class, 'show']);
    // Modifier un patient
    Route::put('/patients/{id}', [PatientController::class, 'update']);
    // Supprimer un patient
    Route::delete('/patients/{id}', [PatientController::class, 'destroy']);
    /*
    |--------------------------------------------------------------------------
    | Routes reservees a l'administrateur
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:administrateur')->group(function () {

        // Consulter les roles disponibles
        Route::get('/roles', [RoleController::class, 'index']);

        // Attribuer ou modifier le role d'un utilisateur
        Route::put('/users/{user}/role', [RoleController::class, 'update']);
    });
});

// Health check
Route::get('/health', function () {
    $checks = [];

    try {
        DB::connection()->getPdo();
        $checks['database'] = 'ok';
    } catch (\Throwable $e) {
        $checks['database'] = 'failed: '.$e->getMessage();
    }

    try {
        Redis::connection()->ping();
        $checks['redis'] = 'ok';
    } catch (\Throwable $e) {
        $checks['redis'] = 'failed: '.$e->getMessage();
    }

    $healthy = ! in_array(false, array_map(
        fn (string $status) => $status === 'ok',
        $checks,
    ), true);

    return response()->json([
        'status' => $healthy ? 'ok' : 'degraded',
        'services' => $checks,
        'timestamp' => now()->toIso8601String(),
    ], $healthy ? 200 : 503);
});