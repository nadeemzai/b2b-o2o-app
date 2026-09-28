<?php

namespace Tests\Feature\Retailer;

use App\Models\Retailer;
use App\Models\TownshipStore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    /**
     * Create an authenticated retailer user with a profile and return both.
     *
     * @return array{user: User, retailer: Retailer, store: TownshipStore}
     */
    private function actAsRetailer(array $retailerOverrides = []): array
    {
        $user     = User::factory()->retailer()->create();
        $store    = TownshipStore::factory()->create();
        $retailer = Retailer::factory()->create(array_merge([
            'user_id'  => $user->id,
            'store_id' => $store->id,
        ], $retailerOverrides));

        Sanctum::actingAs($user, ['*']);

        return compact('user', 'retailer', 'store');
    }

    // ──────────────────────────────────────────────
    // GET /api/retailer/profile
    // ──────────────────────────────────────────────

    public function test_retailer_can_fetch_own_profile(): void
    {
        ['retailer' => $retailer] = $this->actAsRetailer();

        $this->getJson('/api/retailer/profile')
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'business_name', 'cnic', 'kyc_status', 'store'],
            ])
            ->assertJsonPath('data.id', $retailer->id);
    }

    public function test_profile_is_403_when_user_has_no_retailer_profile(): void
    {
        // User has retailer role but no Retailer record (edge case)
        $user = User::factory()->retailer()->create();
        Sanctum::actingAs($user, ['*']);

        $this->getJson('/api/retailer/profile')
            ->assertForbidden();
    }

    public function test_unauthenticated_request_is_401(): void
    {
        $this->getJson('/api/retailer/profile')
            ->assertUnauthorized();
    }

    public function test_non_retailer_role_is_403(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin, ['*']);

        $this->getJson('/api/retailer/profile')
            ->assertForbidden();
    }

    // ──────────────────────────────────────────────
    // PATCH /api/retailer/profile
    // ──────────────────────────────────────────────

    public function test_retailer_can_update_phone(): void
    {
        $this->actAsRetailer();

        $this->patchJson('/api/retailer/profile', ['phone' => '0333-9999999'])
            ->assertOk()
            ->assertJsonPath('data.phone', '0333-9999999');

        $this->assertDatabaseHas('retailers', ['phone' => '0333-9999999']);
    }

    public function test_retailer_can_update_address(): void
    {
        $this->actAsRetailer();

        $this->patchJson('/api/retailer/profile', ['address' => 'New Address, Lahore'])
            ->assertOk()
            ->assertJsonPath('data.address', 'New Address, Lahore');
    }

    public function test_retailer_can_update_business_name(): void
    {
        $this->actAsRetailer();

        $this->patchJson('/api/retailer/profile', ['business_name' => 'New Store Name'])
            ->assertOk()
            ->assertJsonPath('data.business_name', 'New Store Name');
    }

    public function test_update_with_no_body_returns_200_with_unchanged_data(): void
    {
        ['retailer' => $retailer] = $this->actAsRetailer();

        $this->patchJson('/api/retailer/profile', [])
            ->assertOk()
            ->assertJsonPath('data.id', $retailer->id);
    }

    public function test_sensitive_fields_cannot_be_updated(): void
    {
        ['retailer' => $retailer] = $this->actAsRetailer(['cnic' => '35202-1234567-1']);

        $this->patchJson('/api/retailer/profile', ['cnic' => '99999-9999999-9'])
            ->assertOk();

        // CNIC must remain unchanged
        $this->assertDatabaseHas('retailers', ['cnic' => '35202-1234567-1']);
    }

    public function test_profile_update_rejects_oversized_phone(): void
    {
        $this->actAsRetailer();

        $this->patchJson('/api/retailer/profile', ['phone' => str_repeat('0', 21)])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_unauthenticated_update_is_401(): void
    {
        $this->patchJson('/api/retailer/profile', ['phone' => '0333-9999999'])
            ->assertUnauthorized();
    }
}
