<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * SCRUM-512 - Securite des mots de passe.
 *
 * L'audit n'a pas trouve de defaut : le hachage passe par bcrypt via le cast
 * "hashed" du modele User, et le champ est masque dans les reponses JSON.
 * Ces tests figent ces proprietes pour qu'une modification future ne les casse
 * pas silencieusement.
 */
class PasswordSecurityTest extends TestCase
{
    use RefreshDatabase;

    private const MOT_DE_PASSE = 'MotDePasse123';

    protected function setUp(): void
    {
        parent::setUp();

        // Voir LoginSecurityTest : compteurs de limitation partages via Redis.
        $this->withServerVariables([
            'REMOTE_ADDR' => sprintf(
                '10.%d.%d.%d',
                random_int(0, 255),
                random_int(0, 255),
                random_int(1, 254)
            ),
        ]);
    }

    private function inscrire(string $email = 'sara@example.com')
    {
        return $this->postJson('/api/register', [
            'name' => 'Sara Bennani',
            'email' => $email,
            'password' => self::MOT_DE_PASSE,
            'password_confirmation' => self::MOT_DE_PASSE,
        ]);
    }

    // -------------------------------------------------------------- stockage

    public function test_the_stored_password_is_never_the_plain_text_one(): void
    {
        $this->inscrire()->assertStatus(201);

        $stocke = User::where('email', 'sara@example.com')->value('password');

        $this->assertNotSame(self::MOT_DE_PASSE, $stocke);
        $this->assertNotEmpty($stocke);
    }

    public function test_the_stored_password_is_a_bcrypt_hash_verifiable_by_hash_check(): void
    {
        $this->inscrire()->assertStatus(201);

        $stocke = User::where('email', 'sara@example.com')->value('password');

        $this->assertSame('bcrypt', Hash::info($stocke)['algoName']);
        $this->assertTrue(Hash::check(self::MOT_DE_PASSE, $stocke));
    }

    public function test_the_password_is_not_hashed_twice(): void
    {
        // Un double hachage passerait inapercu a l'inscription mais rendrait la
        // connexion impossible avec le mot de passe d'origine.
        $this->inscrire()->assertStatus(201);

        $this->postJson('/api/login', [
            'email' => 'sara@example.com',
            'password' => self::MOT_DE_PASSE,
        ])->assertStatus(200);
    }

    public function test_two_identical_passwords_produce_different_hashes(): void
    {
        // bcrypt integre un sel aleatoire : deux comptes avec le meme mot de
        // passe ne doivent pas partager le meme hash.
        $this->inscrire('premier@example.com')->assertStatus(201);
        $this->inscrire('second@example.com')->assertStatus(201);

        $this->assertNotSame(
            User::where('email', 'premier@example.com')->value('password'),
            User::where('email', 'second@example.com')->value('password')
        );
    }

    // ------------------------------------------------------------ verification

    public function test_a_wrong_password_is_rejected(): void
    {
        $this->inscrire()->assertStatus(201);

        $this->postJson('/api/login', [
            'email' => 'sara@example.com',
            'password' => 'MauvaisMotDePasse123',
        ])->assertStatus(401);
    }

    public function test_password_comparison_is_case_sensitive(): void
    {
        $this->inscrire()->assertStatus(201);

        $this->postJson('/api/login', [
            'email' => 'sara@example.com',
            'password' => strtolower(self::MOT_DE_PASSE),
        ])->assertStatus(401);
    }

    // ------------------------------------------------------- reponses de l API

    public function test_registration_response_never_contains_the_password(): void
    {
        $response = $this->inscrire();

        $response->assertStatus(201);
        $response->assertJsonMissingPath('user.password');
        $response->assertJsonMissingPath('user.remember_token');
        $this->assertStringNotContainsString(self::MOT_DE_PASSE, $response->getContent());
    }

    public function test_login_response_never_contains_the_password(): void
    {
        $this->inscrire()->assertStatus(201);

        $response = $this->postJson('/api/login', [
            'email' => 'sara@example.com',
            'password' => self::MOT_DE_PASSE,
        ]);

        $response->assertStatus(200);
        $response->assertJsonMissingPath('user.password');
        $response->assertJsonMissingPath('user.remember_token');
        $this->assertStringNotContainsString(self::MOT_DE_PASSE, $response->getContent());
    }

    public function test_profile_endpoint_never_contains_the_password(): void
    {
        $user = User::factory()->create(['remember_token' => 'jeton-de-memorisation']);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/me');

        $response->assertStatus(200);
        $response->assertJsonMissingPath('user.password');
        $response->assertJsonMissingPath('user.remember_token');
        $this->assertStringNotContainsString('jeton-de-memorisation', $response->getContent());
    }

    // --------------------------------------------------------------- journaux

    public function test_the_password_is_never_written_to_the_logs(): void
    {
        User::factory()->create(['email' => 'sara@example.com']);

        Log::spy();

        $this->postJson('/api/login', [
            'email' => 'sara@example.com',
            'password' => 'UnMotDePasseTresSecret123',
        ])->assertStatus(401);

        // L'echec est bien journalise (SCRUM-510), mais sans le mot de passe.
        Log::shouldHaveReceived('warning')->withArgs(
            fn ($message, $context = []) => ! str_contains(
                json_encode([$message, $context]),
                'UnMotDePasseTresSecret123'
            )
        );
    }

    // --------------------------------------------------------- reinitialisation

    public function test_resetting_the_password_changes_the_hash_and_revokes_tokens(): void
    {
        $user = User::factory()->create(['email' => 'sara@example.com']);
        $user->createToken('auth_token');

        $ancienHash = $user->fresh()->password;
        $this->assertSame(1, $user->tokens()->count());

        $this->postJson('/api/reset-password', [
            'token' => Password::createToken($user),
            'email' => 'sara@example.com',
            'password' => 'NouveauMotDePasse123',
            'password_confirmation' => 'NouveauMotDePasse123',
        ])->assertStatus(200);

        $recharge = $user->fresh();

        $this->assertNotSame($ancienHash, $recharge->password);
        $this->assertTrue(Hash::check('NouveauMotDePasse123', $recharge->password));
        // Les jetons emis avant le changement ne doivent plus donner acces.
        $this->assertSame(0, $recharge->tokens()->count());
    }
}
