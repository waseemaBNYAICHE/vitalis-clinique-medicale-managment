<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * SCRUM-529 - Forme des reponses 401 et 403.
 *
 * Les tickets precedents ont etabli QUI a le droit de faire quoi. Celui-ci
 * porte sur la reponse renvoyee quand ce droit manque : un client d'API doit
 * pouvoir la traiter sans deviner, quel que soit son outillage.
 *
 * Le defaut corrige ici : une requete non authentifiee qui ne reclamait pas
 * explicitement du JSON recevait 500 "Route [login] not defined." au lieu de
 * 401. Laravel redirige par defaut les visiteurs vers route('login'), qui
 * n'existe pas dans un backend d'API, et cet appel a lieu en construisant
 * l'exception d'authentification. Le client Vue envoie toujours
 * Accept: application/json et recevait donc un 401 correct, ce qui masquait
 * completement le probleme depuis l'application.
 *
 * Le contrat verifie ici :
 *   401  {"message": "Non authentifié"}
 *   403  {"message": "Accès interdit"}   (ou un message metier explicite)
 * toujours en application/json, avec le seul champ 'message'.
 */
class Reponses401Et403Test extends TestCase
{
    use RefreshDatabase;

    private const MESSAGE_401 = 'Non authentifié';

    private const MESSAGE_403 = 'Accès interdit';

    private function utilisateur(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    /**
     * En-tetes Accept representatifs des clients possibles.
     *
     * @return array<string, array{array<string, string>}>
     */
    public static function enTetesAccept(): array
    {
        return [
            'client JSON (Vue)' => [['Accept' => 'application/json']],
            'navigateur' => [['Accept' => 'text/html,application/xhtml+xml']],
            'curl par defaut' => [['Accept' => '*/*']],
            'aucun en-tete Accept' => [[]],
        ];
    }

    // ------------------------------------------------------------- 401

    /**
     * Le test de non-regression du ticket : sans jeton, la reponse doit etre
     * 401 en JSON pour TOUS ces clients. Les trois derniers recevaient 500
     * avant SCRUM-529.
     */
    #[DataProvider('enTetesAccept')]
    public function test_une_requete_sans_jeton_repond_401_en_json(array $enTetes): void
    {
        $reponse = $this->get('/api/patients', $enTetes);

        $reponse->assertStatus(401);
        $this->assertStringStartsWith('application/json', $reponse->headers->get('content-type'));
        $reponse->assertExactJson(['message' => self::MESSAGE_401]);
    }

    /**
     * La redirection vers une page de connexion n'a pas de sens pour une API,
     * et c'est elle qui provoquait le 500. Aucune trace ne doit en subsister.
     */
    #[DataProvider('enTetesAccept')]
    public function test_la_reponse_401_ne_tente_aucune_redirection(array $enTetes): void
    {
        $reponse = $this->get('/api/patients', $enTetes);

        $this->assertNotSame(302, $reponse->status());
        $this->assertNull($reponse->headers->get('Location'));

        $corps = $reponse->getContent();
        $this->assertStringNotContainsString('login', $corps);
        $this->assertStringNotContainsString('Route [', $corps);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function verbesEtRoutesProtegees(): array
    {
        return [
            'GET' => ['get', '/api/patients'],
            'POST' => ['post', '/api/patients'],
            'PUT' => ['put', '/api/patients/1'],
            'DELETE' => ['delete', '/api/patients/1'],
            'GET profil' => ['get', '/api/me'],
            'POST deconnexion' => ['post', '/api/logout'],
            'GET tableau de bord' => ['get', '/api/dashboard'],
        ];
    }

    #[DataProvider('verbesEtRoutesProtegees')]
    public function test_tous_les_verbes_repondent_401_de_la_meme_facon(string $methode, string $uri): void
    {
        $reponse = $this->json(strtoupper($methode), $uri);

        $reponse->assertStatus(401)
            ->assertExactJson(['message' => self::MESSAGE_401]);
    }

    /**
     * Jeton absent, invalide ou expire : la reponse est rigoureusement la
     * meme. Distinguer ces cas n'aiderait que celui qui essaie des jetons.
     */
    public function test_la_cause_du_401_nest_pas_divulguee(): void
    {
        $sansJeton = $this->getJson('/api/me');

        $this->app['auth']->forgetGuards();
        $invalide = $this->withHeader('Authorization', 'Bearer jeton-invente')
            ->getJson('/api/me');

        $utilisateur = $this->utilisateur('secretaire');
        $jeton = $utilisateur->createToken('test')->plainTextToken;
        $utilisateur->tokens()->update([
            'created_at' => now()->subMinutes((int) config('sanctum.expiration') + 1),
        ]);
        $this->app['auth']->forgetGuards();
        $expire = $this->withHeader('Authorization', "Bearer {$jeton}")
            ->getJson('/api/me');

        foreach (['sans jeton' => $sansJeton, 'invalide' => $invalide, 'expire' => $expire] as $cas => $reponse) {
            $this->assertSame(401, $reponse->status(), "Cas : {$cas}");
            $reponse->assertExactJson(['message' => self::MESSAGE_401]);
        }
    }

    // ------------------------------------------------------------- 403

    /**
     * Un compte authentifie mais sans la permission recoit 403, pas 401 :
     * se reconnecter n'y changerait rien, le client ne doit pas boucler sur
     * une page de connexion.
     */
    #[DataProvider('enTetesAccept')]
    public function test_un_refus_de_permission_repond_403_en_json(array $enTetes): void
    {
        Sanctum::actingAs($this->utilisateur('patient'));

        $reponse = $this->get('/api/patients', $enTetes);

        $reponse->assertStatus(403);
        $this->assertStringStartsWith('application/json', $reponse->headers->get('content-type'));
        $reponse->assertJson(['message' => self::MESSAGE_403]);
    }

    public function test_le_message_403_est_le_meme_pour_toutes_les_ressources(): void
    {
        $refus = [
            ['patient', 'get', '/api/patients'],
            ['patient', 'get', '/api/medecins'],
            ['patient', 'get', '/api/dashboard/patients-count'],
            ['medecin', 'get', '/api/roles'],
            ['medecin', 'get', '/api/dashboard/chiffre-affaires'],
            ['secretaire', 'delete', '/api/patients/1'],
        ];

        foreach ($refus as [$role, $methode, $uri]) {
            $this->app['auth']->forgetGuards();
            Sanctum::actingAs($this->utilisateur($role));

            $this->json(strtoupper($methode), $uri)
                ->assertStatus(403)
                ->assertJson(['message' => self::MESSAGE_403]);
        }
    }

    /**
     * Un refus metier (SCRUM-527) porte un message explicite parce qu'il est
     * actionnable, mais garde la meme structure : un seul champ 'message'.
     */
    public function test_un_refus_metier_garde_la_meme_structure(): void
    {
        $admin = $this->utilisateur('administrateur');
        Sanctum::actingAs($admin);

        $reponse = $this->putJson("/api/users/{$admin->id}/role", ['role' => 'patient']);

        $reponse->assertStatus(403)
            ->assertExactJson(['message' => 'Vous ne pouvez pas modifier votre propre role']);
    }

    /**
     * L'authentification passe avant l'autorisation : sans jeton, la reponse
     * est 401, jamais 403, meme sur une route dont le role n'aurait de toute
     * facon pas la permission.
     */
    public function test_lauthentification_est_verifiee_avant_lautorisation(): void
    {
        $this->getJson('/api/roles')->assertStatus(401);
        $this->deleteJson('/api/patients/1')->assertStatus(401);
        $this->getJson('/api/dashboard/chiffre-affaires')->assertStatus(401);
    }

    // ----------------------------- absence de details d implementation

    /**
     * @return array<string, array{int, string}>
     */
    public static function codesDErreur(): array
    {
        return [
            '401' => [401, 'anonyme'],
            '403' => [403, 'patient'],
        ];
    }

    /**
     * En production APP_DEBUG vaut false (docker-compose.prod.yml). La
     * reponse ne doit alors contenir ni classe d'exception, ni chemin de
     * fichier, ni pile d'appels.
     */
    #[DataProvider('codesDErreur')]
    public function test_la_reponse_ne_divulgue_aucun_detail_dimplementation(int $codeAttendu, string $cas): void
    {
        config(['app.debug' => false]);

        if ($cas === 'patient') {
            Sanctum::actingAs($this->utilisateur('patient'));
        }

        $reponse = $this->getJson('/api/patients');
        $reponse->assertStatus($codeAttendu);

        $corps = $reponse->json();

        $this->assertSame(['message'], array_keys($corps), 'La reponse ne doit contenir que "message".');

        foreach (['exception', 'file', 'line', 'trace'] as $champ) {
            $this->assertArrayNotHasKey($champ, $corps);
        }

        $brut = $reponse->getContent();
        $this->assertStringNotContainsString('Illuminate\\', $brut);
        $this->assertStringNotContainsString('Symfony\\', $brut);
        $this->assertStringNotContainsString('vendor', $brut);
        $this->assertStringNotContainsString('App\\', $brut);
    }

    // --------------------------------------- coherence de la structure

    /**
     * Les deux codes partagent la meme forme : un objet JSON avec le seul
     * champ 'message', renseigne. Un client peut donc ecrire un unique
     * gestionnaire d'erreur.
     */
    public function test_401_et_403_partagent_la_meme_structure(): void
    {
        $nonAuthentifie = $this->getJson('/api/patients');

        Sanctum::actingAs($this->utilisateur('patient'));
        $nonAutorise = $this->getJson('/api/patients');

        $this->assertSame(401, $nonAuthentifie->status());
        $this->assertSame(403, $nonAutorise->status());

        foreach ([$nonAuthentifie, $nonAutorise] as $reponse) {
            $corps = $reponse->json();

            $this->assertIsArray($corps);
            $this->assertArrayHasKey('message', $corps);
            $this->assertIsString($corps['message']);
            $this->assertNotSame('', trim($corps['message']));
            $this->assertStringStartsWith(
                'application/json',
                $reponse->headers->get('content-type')
            );
        }
    }

    /**
     * Les messages sont en francais, comme le reste de l'API. Le 401 venait
     * de Laravel et repondait "Unauthenticated." pendant que le 403 repondait
     * "Accès interdit".
     */
    public function test_les_messages_sont_en_francais(): void
    {
        $this->getJson('/api/patients')
            ->assertStatus(401)
            ->assertJson(['message' => self::MESSAGE_401]);

        Sanctum::actingAs($this->utilisateur('patient'));

        $this->getJson('/api/patients')
            ->assertStatus(403)
            ->assertJson(['message' => self::MESSAGE_403]);
    }
}
