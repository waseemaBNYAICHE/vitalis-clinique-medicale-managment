<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MedecinController;
use App\Http\Controllers\SpecialiteController;
use App\Http\Controllers\OrdonnanceController;
use App\Http\Controllers\LigneOrdonnanceController;

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

    // Tableau de bord principal.
    //
    // Seule route /dashboard/* sans permission, et c'est volontaire : le
    // controller choisit lui-meme le contenu selon le role de l'appelant, et
    // chaque role n'y recoit que son propre perimetre (le medecin ses
    // rendez-vous, le patient son dossier). Un compte valide suffit donc.
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // SCRUM-527 : les routes ci-dessous renvoient des donnees AGREGEES sur
    // toute la clinique, sans tenir compte du role de l'appelant. Elles
    // n'exigeaient jusqu'ici qu'un compte valide : n'importe quel utilisateur
    // authentifie - y compris un compte cree librement via /api/register, qui
    // recoit le role 'patient' - pouvait lire le chiffre d'affaires de la
    // clinique et la liste nominative des rendez-vous du jour.
    //
    // Indicateurs d'activite : matiere du tableau de bord du personnel.
    Route::middleware('can:indicateurs.read')->group(function () {
        Route::get('/dashboard/patients-count', [DashboardController::class, 'nombrePatients']);
        Route::get('/dashboard/rendez-vous-count', [DashboardController::class, 'nombreRendezVous']);
        Route::get('/dashboard/consultations-count', [DashboardController::class, 'nombreConsultations']);
        Route::get('/dashboard/examens-en-attente', [DashboardController::class, 'examensEnAttente']);

        // Agenda du jour : contient le nom des patients et le MOTIF de leur
        // venue. Le controller restreint en plus le medecin a ses propres
        // rendez-vous.
        Route::get('/dashboard/rendez-vous-du-jour', [DashboardController::class, 'rendezVousDuJour']);
    });

    // Donnees de gestion (chiffre d'affaires, tendances, export) : reservees
    // a l'administrateur. Un soignant n'a pas a connaitre le revenu global de
    // la clinique pour exercer.
    Route::middleware('can:statistiques.read')->group(function () {
        Route::get('/dashboard/chiffre-affaires', [DashboardController::class, 'chiffreAffaires']);
        Route::get('/dashboard/statistiques-mensuelles', [DashboardController::class, 'statistiquesMensuelles']);
        Route::get('/dashboard/export-statistiques', [DashboardController::class, 'exportStatistiques']);
    });

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

    // Gestion des medecins
    //
    // SCRUM-526 : ces routes etaient dans le groupe 'can:roles.manage'. Cette
    // permission decrit la gestion des comptes utilisateurs, pas le
    // referentiel medical : le controle etait donc correct par accident
    // (seul l'administrateur passait) mais impossible a faire evoluer sans
    // ouvrir la gestion des roles. Chaque route porte desormais la
    // permission de sa propre ressource.
    Route::get('/medecins', [MedecinController::class, 'index'])
        ->middleware('can:medecins.read');
    Route::get('/medecins/{id}', [MedecinController::class, 'show'])
        ->middleware('can:medecins.read');
    Route::post('/medecins', [MedecinController::class, 'store'])
        ->middleware('can:medecins.create');
    Route::put('/medecins/{id}', [MedecinController::class, 'update'])
        ->middleware('can:medecins.update');
    Route::delete('/medecins/{id}', [MedecinController::class, 'destroy'])
        ->middleware('can:medecins.delete');

    // Gestion des specialites
    Route::get('/specialites', [SpecialiteController::class, 'index'])
        ->middleware('can:specialites.read');
    Route::get('/specialites/{id}', [SpecialiteController::class, 'show'])
        ->middleware('can:specialites.read');
    Route::post('/specialites', [SpecialiteController::class, 'store'])
        ->middleware('can:specialites.create');
    Route::put('/specialites/{id}', [SpecialiteController::class, 'update'])
        ->middleware('can:specialites.update');
    Route::delete('/specialites/{id}', [SpecialiteController::class, 'destroy'])
        ->middleware('can:specialites.delete');

        // Gestion des ordonnances
    Route::get('/ordonnances', [OrdonnanceController::class, 'index']);
    Route::get('/ordonnances/{id}', [OrdonnanceController::class, 'show']);
    Route::post('/ordonnances', [OrdonnanceController::class, 'store']);
    Route::put('/ordonnances/{id}', [OrdonnanceController::class, 'update']);
    Route::delete('/ordonnances/{id}', [OrdonnanceController::class, 'destroy']);

    // Gestion du contenu des ordonnances
    Route::get('/ordonnances/{idOrdonnance}/lignes', [LigneOrdonnanceController::class, 'index']);
    Route::post('/ordonnances/{idOrdonnance}/lignes', [LigneOrdonnanceController::class, 'store']);
    Route::put('/lignes-ordonnance/{id}', [LigneOrdonnanceController::class, 'update']);
    Route::delete('/lignes-ordonnance/{id}', [LigneOrdonnanceController::class, 'destroy']);
});

// Health check
//
// SCRUM-526 : route volontairement publique (sondes Docker, workflow de
// deploiement, script de smoke test). Elle ne renvoie donc QUE 'ok' ou
// 'failed' par service. Auparavant elle recopiait le message de l'exception,
// qui contient l'hote, le port, le nom de la base et l'utilisateur : ces
// informations d'infrastructure etaient lisibles par n'importe quel visiteur
// non authentifie des que la base tombait. Le detail part maintenant dans les
// logs applicatifs, ou seule l'equipe y a acces.
Route::get('/health', function () {
    $checks = [];

    foreach ([
        'database' => fn () => DB::connection()->getPdo(),
        'redis' => fn () => Redis::connection()->ping(),
    ] as $service => $sonde) {
        try {
            $sonde();
            $checks[$service] = 'ok';
        } catch (\Throwable $e) {
            Log::error("Health check: service {$service} indisponible", [
                'service' => $service,
                'exception' => $e->getMessage(),
            ]);

            $checks[$service] = 'failed';
        }
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