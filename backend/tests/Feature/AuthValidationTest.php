<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * SCRUM-511 - Validation des donnees d'authentification.
 *
 * La validation cote serveur est la seule qui fasse foi : le formulaire Vue
 * peut etre contourne (appel direct a l'API). Ces tests verifient donc que
 * l'API refuse elle-meme les donnees invalides, avec un code 422.
 */
class AuthValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Voir LoginSecurityTest : les compteurs de limitation de debit sont
        // partages via le cache Redis du conteneur. Une IP distincte par test
        // garantit que c'est bien la validation qui est mesuree ici, et pas
        // une limite de debit atteinte par un test precedent.
        $this->withServerVariables([
            'REMOTE_ADDR' => sprintf(
                '10.%d.%d.%d',
                random_int(0, 255),
                random_int(0, 255),
                random_int(1, 254)
            ),
        ]);
    }

    // ------------------------------------------------------------------ login

    public function test_login_requires_an_email(): void
    {
        $response = $this->postJson('/api/login', ['password' => 'MotDePasse123']);

        $response->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_login_rejects_a_malformed_email(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'pas-une-adresse',
            'password' => 'MotDePasse123',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_login_rejects_an_oversized_email(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => str_repeat('a', 250).'@example.com',
            'password' => 'MotDePasse123',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_login_requires_a_password(): void
    {
        $response = $this->postJson('/api/login', ['email' => 'utilisateur@example.com']);

        $response->assertStatus(422)->assertJsonValidationErrors('password');
    }

    public function test_login_accepts_well_formed_credentials(): void
    {
        User::factory()->create(['email' => 'utilisateur@example.com']);

        $response = $this->postJson('/api/login', [
            'email' => 'utilisateur@example.com',
            'password' => 'password',
        ]);

        // La validation laisse passer : le mot de passe existant est court et
        // sans chiffre, les regles de robustesse ne s'appliquent volontairement
        // pas a la connexion.
        $response->assertStatus(200);
    }

    // --------------------------------------------------------------- register

    public function test_registration_rejects_a_short_password(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Sara',
            'email' => 'sara@example.com',
            'password' => 'Abc123',
            'password_confirmation' => 'Abc123',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'sara@example.com']);
    }

    public function test_registration_rejects_a_password_without_digits(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Sara',
            'email' => 'sara@example.com',
            'password' => 'motdepasse',
            'password_confirmation' => 'motdepasse',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('password');
    }

    public function test_registration_rejects_a_password_without_letters(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Sara',
            'email' => 'sara@example.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('password');
    }

    public function test_registration_requires_a_matching_confirmation(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Sara',
            'email' => 'sara@example.com',
            'password' => 'MotDePasse123',
            'password_confirmation' => 'AutreMotDePasse123',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('password');
    }

    public function test_registration_rejects_an_already_used_email(): void
    {
        User::factory()->create(['email' => 'deja@example.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'Sara',
            'email' => 'deja@example.com',
            'password' => 'MotDePasse123',
            'password_confirmation' => 'MotDePasse123',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_registration_accepts_a_valid_payload(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Sara Bennani',
            'email' => 'sara@example.com',
            'password' => 'MotDePasse123',
            'password_confirmation' => 'MotDePasse123',
        ]);

        $response->assertStatus(201)->assertJsonStructure(['token', 'user' => ['id', 'email']]);
        $this->assertDatabaseHas('users', ['email' => 'sara@example.com']);
    }

    // ------------------------------------------------- mot de passe oublie

    public function test_forgot_password_rejects_a_malformed_email(): void
    {
        $response = $this->postJson('/api/forgot-password', ['email' => 'pas-une-adresse']);

        $response->assertStatus(422)->assertJsonValidationErrors('email');
    }

    // -------------------------------------------------------- reinitialisation

    public function test_reset_password_requires_a_token(): void
    {
        $response = $this->postJson('/api/reset-password', [
            'email' => 'utilisateur@example.com',
            'password' => 'MotDePasse123',
            'password_confirmation' => 'MotDePasse123',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('token');
    }

    public function test_reset_password_applies_the_same_password_rules(): void
    {
        $response = $this->postJson('/api/reset-password', [
            'token' => 'un-jeton-quelconque',
            'email' => 'utilisateur@example.com',
            'password' => 'motdepasse',
            'password_confirmation' => 'motdepasse',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('password');
    }

    public function test_validation_errors_are_returned_in_a_consistent_shape(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['email', 'password']]);
    }
}
