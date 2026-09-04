<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurerLimitesAuthentification();

        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');

            return $frontendUrl.'/reset-password?token='.$token.'&email='.urlencode($notifiable->getEmailForPasswordReset());
        });
    }

    /**
     * SCRUM-510 - Limites de debit sur les routes d'authentification publiques.
     *
     * Sans ces limites, /api/login accepte un nombre illimite de tentatives :
     * un mot de passe peut donc etre devine par force brute. Une fois la limite
     * atteinte, Laravel repond 429 avec un en-tete Retry-After.
     */
    private function configurerLimitesAuthentification(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $email = Str::lower((string) $request->input('email'));

            return [
                // Cible un compte precis : ralentit la recherche du mot de passe.
                Limit::perMinute(5)->by($email.'|'.$request->ip()),
                // Cible l'adresse IP : ralentit le balayage de plusieurs comptes.
                Limit::perMinute(20)->by($request->ip()),
            ];
        });

        RateLimiter::for('auth-public', function (Request $request) {
            // Inscription, demande et confirmation de reinitialisation : limite
            // la creation de comptes en masse, l'envoi massif d'emails et les
            // essais de jetons de reinitialisation.
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}