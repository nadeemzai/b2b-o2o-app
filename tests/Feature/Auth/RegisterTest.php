<?php

namespace Tests\Feature\Auth;

use App\Models\Retailer;
use App\Models\TownshipStore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    /** Build a valid registration payload, creating a fresh active store. */
    private function payload(array $overrides = []): array
    {
        $store = TownshipStore::factory()->create();

        return array_merge([
            'name'          => 'Ahmed Khan',
            'email'         => 'ahmed@example.com',
            'password'      => 'Password1',
            'store_id'      => $store->id,
            'business_name' => 'Ahmed General Store',
            'cnic'          => '35202-1234567-1',
            'phone'         => '0321-4567890',
            'address'       => 'Shop 5, Model Town, Lahore',
        ], $overrides);
    }

    // ──────────────────────────────────────────────
    // Success cases
    // ──────────────────────────────────────────────

    public function test_retailer_can_register_successfully(): void
    {
        $this->postJson('/api/auth/register', $this->payload())
            ->assertCreated()
            ->assertJsonStructure([
                'data'    => ['token', 'user', 'retailer'],
                'message',
            ]);
    }

    public function test_registration_persists_user_and_retailer_to_database(): void
    {
        $this->postJson('/api/auth/register', $this->payload());

        $this->assertDatabaseHas('users', [
            'email' => 'ahmed@example.com',
            'role'  => 'retailer',
        ]);
        $this->assertDatabaseHas('retailers', [
            'cnic'       => '35202-1234567-1',
            'kyc_status' => 'pending',
        ]);
    }

    public function test_registration_returns_usable_token(): void
    {
        $token = $this->postJson('/api/auth/register', $this->payload())
            ->json('data.token');

        $this->assertNotEmpty($token);

        $this->getJson('/api/auth/me', ['Authorization' => "Bearer {$token}"])
            ->assertOk();
    }

    public function test_optional_ntn_strn_can_be_submitted(): void
    {
        $this->postJson('/api/auth/register', $this->payload([
            'ntn'  => '1234567-8',
            'strn' => 'SRN-999',
        ]))->assertCreated();

        $this->assertDatabaseHas('retailers', [
            'ntn'  => '1234567-8',
            'strn' => 'SRN-999',
        ]);
    }

    // ──────────────────────────────────────────────
    // Validation / failure cases
    // ──────────────────────────────────────────────

    public function test_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'ahmed@example.com']);

        $this->postJson('/api/auth/register', $this->payload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_fails_with_duplicate_cnic(): void
    {
        $existingStore = TownshipStore::factory()->create();
        $existingUser  = User::factory()->retailer()->create();
        Retailer::factory()->create([
            'user_id'  => $existingUser->id,
            'store_id' => $existingStore->id,
            'cnic'     => '35202-1234567-1',
        ]);

        $this->postJson('/api/auth/register', $this->payload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['cnic']);
    }

    public function test_registration_fails_with_inactive_store(): void
    {
        $inactiveStore = TownshipStore::factory()->inactive()->create();

        $this->postJson('/api/auth/register', $this->payload(['store_id' => $inactiveStore->id]))
            ->assertNotFound();
    }

    public function test_registration_fails_with_nonexistent_store(): void
    {
        $this->postJson('/api/auth/register', $this->payload(['store_id' => 99999]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['store_id']);
    }

    public function test_registration_fails_with_invalid_cnic_format(): void
    {
        $this->postJson('/api/auth/register', $this->payload(['cnic' => '12345-ABCDEFG-1']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['cnic']);

        // Short cnic
        $this->postJson('/api/auth/register', $this->payload(['cnic' => '1234-123456-1']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['cnic']);
    }

    public function test_registration_requires_all_mandatory_fields(): void
    {
        $this->postJson('/api/auth/register', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name', 'email', 'password', 'store_id',
                'business_name', 'cnic', 'phone', 'address',
            ]);
    }

    public function test_registration_rejects_weak_password(): void
    {
        // No uppercase + no number
        $this->postJson('/api/auth/register', $this->payload(['password' => 'simplepassword']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }
}
