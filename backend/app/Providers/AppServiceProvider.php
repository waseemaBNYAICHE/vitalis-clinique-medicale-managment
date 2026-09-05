<?php

namespace App\Providers;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response as ReponseAutorisation;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
        $this->declarerPermissions();

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

    /**
     * SCRUM-518 - Declare une Gate par permission.
     *
     * Chaque permission devient une Gate portant son propre nom, ce qui permet
     * d'utiliser le middleware natif `can:` sur les routes. Il n'y a donc pas
     * de middleware supplementaire a ecrire ni de table a creer : la reponse
     * vient de la correspondance role -> permissions definie dans l'enum Role.
     */
    private function declarerPermissions(): void
    {
        foreach (Permission::cases() as $permission) {
            Gate::define($permission->value, function (User $user) use ($permission) {
                $role = Role::tryFrom((string) $user->role);

                // Le message est conserve a l'identique pour que les clients
                // existants voient la meme reponse 403 qu'avec le middleware
                // 'role' utilise jusqu'ici.
                return $role !== null && $role->accorde($permission)
                    ? ReponseAutorisation::allow()
                    : ReponseAutorisation::deny('Accès interdit');
            });
        }
    }
}