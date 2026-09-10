<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * SCRUM-540 - Configuration sensible resolue au bon endroit.
 *
 * FRONTEND_URL n'etait definie que dans docker-compose.yml (developpement),
 * jamais dans docker-compose.prod.yml. En production le backend retombait
 * donc sur son defaut, http://localhost:5173, avec deux consequences :
 *
 *  - CORS n'autorisait que localhost, pas l'adresse reelle du site ;
 *  - le lien de reinitialisation de mot de passe envoye par email pointait
 *    vers localhost, jeton compris. Le parcours etait inutilisable en
 *    production.
 *
 * Ces tests figent la RESOLUTION de la valeur, pas seulement sa presence :
 * une valeur codee en dur les ferait echouer.
 */
class ConfigurationProductionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Reevalue config/app.php avec un environnement controle.
     *
     * Le fichier est relu pour que ses appels a env() soient reexecutes : la
     * configuration chargee au demarrage refleterait sinon l'environnement de
     * test et ne prouverait rien.
     */
    private function adresseResolue(array $variables): string
    {
        $sauvegarde = [];

        foreach (['FRONTEND_URL', 'APP_URL'] as $cle) {
            $sauvegarde[$cle] = $_ENV[$cle] ?? null;
            unset($_ENV[$cle], $_SERVER[$cle]);
            putenv($cle);
        }

        foreach ($variables as $cle => $valeur) {
            $_ENV[$cle] = $valeur;
            $_SERVER[$cle] = $valeur;
            putenv("{$cle}={$valeur}");
        }

        $configuration = require config_path('app.php');

        foreach ($sauvegarde as $cle => $valeur) {
            if ($valeur === null) {
                unset($_ENV[$cle], $_SERVER[$cle]);
                putenv($cle);
            } else {
                $_ENV[$cle] = $valeur;
                $_SERVER[$cle] = $valeur;
                putenv("{$cle}={$valeur}");
            }
        }

        return $configuration['frontend_url'];
    }

    public function test_ladresse_du_frontend_prime_sur_celle_de_lapi(): void
    {
        $this->assertSame(
            'https://front.example',
            $this->adresseResolue([
                'FRONTEND_URL' => 'https://front.example',
                'APP_URL' => 'https://api.example',
            ])
        );
    }

    /**
     * Le cas qui rendait la production silencieusement fausse : la variable
     * existe mais est vide - ce que produit un `${FRONTEND_URL:-}` de Docker
     * Compose. env('FRONTEND_URL', $defaut) renvoie alors '' et non $defaut.
     */
    public function test_une_adresse_vide_est_traitee_comme_une_absence(): void
    {
        $this->assertSame(
            'https://api.example',
            $this->adresseResolue([
                'FRONTEND_URL' => '',
                'APP_URL' => 'https://api.example',
            ])
        );
    }

    public function test_sans_adresse_de_frontend_on_retombe_sur_celle_de_lapi(): void
    {
        // Mieux vaut l'adresse du serveur que celle du poste du developpeur :
        // c'est exactement l'ecart qui cassait la production.
        $this->assertSame(
            'https://api.example',
            $this->adresseResolue(['APP_URL' => 'https://api.example'])
        );
    }

    public function test_le_defaut_de_developpement_ne_sert_qu_en_dernier_recours(): void
    {
        $this->assertSame('http://localhost:5173', $this->adresseResolue([]));
    }

    // ------------------------------------------------------------- CORS

    public function test_les_origines_cors_reprennent_ladresse_du_frontend(): void
    {
        // Une seule source pour les deux usages : ils ne peuvent pas diverger.
        $this->assertContains(
            config('app.frontend_url'),
            config('cors.allowed_origins')
        );
    }

    public function test_aucune_origine_cors_nest_vide_ni_joker(): void
    {
        $this->assertNotEmpty(config('cors.allowed_origins'));

        foreach (config('cors.allowed_origins') as $origine) {
            $this->assertNotSame('', $origine, 'Une origine vide est refusee par les navigateurs.');
            $this->assertNotSame(
                '*',
                $origine,
                'Le joker combine a supports_credentials exposerait l API a tout site.'
            );
        }
    }

    // ---------------------------------------- lien de reinitialisation

    /**
     * Ce lien transporte un jeton de reinitialisation : il doit mener au vrai
     * site, pas au poste de l'utilisateur.
     */
    public function test_le_lien_de_reinitialisation_suit_la_configuration(): void
    {
        config(['app.frontend_url' => 'https://vitalis.example']);

        $utilisateur = User::factory()->create(['email' => 'agent@example.com']);
        $lien = (new ResetPassword('jeton-de-test'))->toMail($utilisateur)->actionUrl;

        $this->assertStringStartsWith('https://vitalis.example/reset-password', $lien);
        $this->assertStringNotContainsString('localhost', $lien);
        $this->assertStringContainsString('jeton-de-test', $lien);
    }
}
