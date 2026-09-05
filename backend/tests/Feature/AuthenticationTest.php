<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    // ---------- REGISTER ----------

    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'user', 'token', 'token_type']);

        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_register_fails_with_weak_password(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'newuser2@example.com',
            'password' => 'abc',
            'password_confirmation' => 'abc',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    public function test_register_fails_when_password_confirmation_does_not_match(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'newuser3@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Different123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    public function test_register_fails_with_missing_fields(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    // ---------- LOGIN ----------

    public function test_user_can_login_with_correct_credentials(): void
    {
        User::factory()->create([
            'email' => 'login@example.com',
            'password' => bcrypt('Password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login@example.com',
            'password' => 'Password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'user', 'token', 'token_type']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'login2@example.com',
            'password' => bcrypt('Password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login2@example.com',
            'password' => 'WrongPassword',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Identifiants invalides']);
    }

    public function test_login_fails_with_nonexistent_email(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'doesnotexist@example.com',
            'password' => 'Password123',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Identifiants invalides']);
    }

    public function test_login_fails_with_missing_fields(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422);
    }

    public function test_login_does_not_reveal_whether_account_exists(): void
    {
        User::factory()->create([
            'email' => 'exists@example.com',
            'password' => bcrypt('Password123'),
        ]);

        $wrongPassword = $this->postJson('/api/login', [
            'email' => 'exists@example.com',
            'password' => 'WrongOne',
        ]);

        $noAccount = $this->postJson('/api/login', [
            'email' => 'noaccount@example.com',
            'password' => 'WrongOne',
        ]);

        $this->assertEquals(
            $wrongPassword->json('message'),
            $noAccount->json('message')
        );
    }

    // ---------- LOGOUT ----------

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Déconnexion réussie']);
    }

    public function test_logout_requires_authentication(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    public function test_logout_revokes_only_current_token(): void
    {
        $user = User::factory()->create();
        $token1 = $user->createToken('device_1')->plainTextToken;
        $user->createToken('device_2');

        $this->withHeader('Authorization', "Bearer $token1")
            ->postJson('/api/logout')
            ->assertStatus(200);

        $this->assertEquals(1, $user->fresh()->tokens()->count());
    }

    // ---------- ME (route protegee) ----------

    public function test_me_requires_authentication(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }

    public function test_me_returns_authenticated_user(): void
    {
        $user = User::factory()->create(['email' => 'me@example.com']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJsonPath('user.email', 'me@example.com');
    }

    // ---------- FORGOT PASSWORD ----------

    public function test_forgot_password_with_valid_email_format(): void
    {
        User::factory()->create(['email' => 'forgot@example.com']);

        $response = $this->postJson('/api/forgot-password', [
            'email' => 'forgot@example.com',
        ]);

        $response->assertStatus(200);
    }

    public function test_forgot_password_fails_with_invalid_email_format(): void
    {
        $response = $this->postJson('/api/forgot-password', [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422);
    }

    // ---------- RESET PASSWORD ----------

    public function test_reset_password_fails_with_invalid_token(): void
    {
        User::factory()->create(['email' => 'reset@example.com']);

        $response = $this->postJson('/api/reset-password', [
            'token' => 'invalid-token',
            'email' => 'reset@example.com',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ]);

        $response->assertStatus(400);
    }

    public function test_reset_password_fails_with_weak_new_password(): void
    {
        $response = $this->postJson('/api/reset-password', [
            'token' => 'some-token',
            'email' => 'reset2@example.com',
            'password' => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }
}