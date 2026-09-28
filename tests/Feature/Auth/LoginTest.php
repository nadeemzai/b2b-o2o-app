<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    private function makeRetailer(array $overrides = []): User
    {
        return User::factory()->retailer()->create(array_merge([
            'email'    => 'retailer@test.com',
            'password' => bcrypt('Password1'),
        ], $overrides));
    }

    // ──────────────────────────────────────────────
    // Success cases
    // ──────────────────────────────────────────────

    public function test_retailer_can_login_with_valid_credentials(): void
    {
        $this->makeRetailer();

        $this->postJson('/api/auth/login', [
            'email'    => 'retailer@test.com',
            'password' => 'Password1',
        ])
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['token', 'user'],
            ]);
    }

    public function test_login_response_includes_user_fields(): void
    {
        $this->makeRetailer(['name' => 'Test Retailer']);

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'retailer@test.com',
            'password' => 'Password1',
        ])->assertOk();

        $this->assertSame('Test Retailer', $response->json('data.user.name'));
        $this->assertSame('retailer@test.com', $response->json('data.user.email'));
    }

    public function test_returned_token_can_authenticate_subsequent_requests(): void
    {
        $this->makeRetailer();

        $token = $this->postJson('/api/auth/login', [
            'email'    => 'retailer@test.com',
            'password' => 'Password1',
        ])->json('data.token');

        $this->getJson('/api/auth/me', ['Authorization' => "Bearer {$token}"])
            ->assertOk()
            ->assertJsonPath('data.email', 'retailer@test.com');
    }

    public function test_login_revokes_previous_token_for_same_device(): void
    {
        $user = $this->makeRetailer();
        // Create an existing token that should be revoked
        $user->createToken('api');
        $this->assertCount(1, $user->tokens);

        $this->postJson('/api/auth/login', [
            'email'       => 'retailer@test.com',
            'password'    => 'Password1',
            'device_name' => 'api',
        ])->assertOk();

        // Still only one token — the old one was replaced
        $this->assertCount(1, $user->fresh()->tokens);
    }

    // ──────────────────────────────────────────────
    // Failure cases
    // ──────────────────────────────────────────────

    public function test_login_fails_with_wrong_password(): void
    {
        $this->makeRetailer();

        $this->postJson('/api/auth/login', [
            'email'    => 'retailer@test.com',
            'password' => 'WrongPass1',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_for_unknown_email(): void
    {
        $this->postJson('/api/auth/login', [
            'email'    => 'ghost@nowhere.com',
            'password' => 'Password1',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_for_inactive_account(): void
    {
        $this->makeRetailer(['is_active' => false]);

        $this->postJson('/api/auth/login', [
            'email'    => 'retailer@test.com',
            'password' => 'Password1',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_requires_email(): void
    {
        $this->postJson('/api/auth/login', ['password' => 'Password1'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_requires_password(): void
    {
        $this->postJson('/api/auth/login', ['email' => 'retailer@test.com'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_login_rejects_malformed_email(): void
    {
        $this->postJson('/api/auth/login', [
            'email'    => 'not-an-email',
            'password' => 'Password1',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}
