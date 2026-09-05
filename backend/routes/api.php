<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Routes publiques d'authentification
// SCRUM-510 : ces routes sont accessibles sans jeton, elles sont donc limitees
// en debit pour empecher la force brute et les envois en masse. Les limiteurs
// 'login' et 'auth-public' sont definis dans AppServiceProvider.
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:login');

Route::middleware('throttle:auth-public')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});


// Routes accessibles uniquement aux utilisateurs authentifiés
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/patients-count', [DashboardController::class, 'nombrePatients']);
    Route::get('/dashboard/rendez-vous-count', [DashboardController::class, 'nombreRendezVous']);
    Route::get('/dashboard/consultations-count', [DashboardController::class, 'nombreConsultations']);
    Route::get('/dashboard/chiffre-affaires', [DashboardController::class, 'chiffreAffaires']);
   Route::get('/dashboard/rendez-vous-du-jour', [DashboardController::class, 'rendezVousDuJour']);
   Route::get('/dashboard/examens-en-attente', [DashboardController::class, 'examensEnAttente']);
   ///confli mergr
   Route::get('/dashboard/statistiques-mensuelles', [DashboardController::class, 'statistiquesMensuelles']);
   Route::get('/dashboard/export-statistiques', [DashboardController::class, 'exportStatistiques']);
  
  
   // Gestion des patients - reservee au personnel medical/administratif
   
   
   // SCRUM-518 : chaque route exige une PERMISSION plutot qu'une liste de
   // roles ecrite en dur. La correspondance role -> permissions est definie
   // une seule fois dans App\Enums\Role, et les Gates correspondantes dans
   // AppServiceProvider. Ajouter ou retirer un role a une operation ne
   // demande donc plus de modifier ce fichier.
   Route::get('/patients', [PatientController::class, 'index'])
       ->middleware('can:patients.read');
   Route::get('/patients/search', [PatientController::class, 'search'])
       ->middleware('can:patients.read');
   Route::get('/patients/{id}', [PatientController::class, 'show'])
       ->middleware('can:patients.read');

   Route::post('/patients', [PatientController::class, 'store'])
       ->middleware('can:patients.create');

   Route::put('/patients/{id}', [PatientController::class, 'update'])
       ->middleware('can:patients.update');

   // Suppression d'un dossier medical : reservee a l'administrateur.
   Route::delete('/patients/{id}', [PatientController::class, 'destroy'])
       ->middleware('can:patients.delete');

   
    // Gestion des roles : permission 'roles.manage', accordee au seul
    // administrateur (SCRUM-518).
    Route::middleware('can:roles.manage')->group(function () {

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