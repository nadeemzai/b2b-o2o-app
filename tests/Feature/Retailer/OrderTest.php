<?php

namespace Tests\Feature\Retailer;

use App\Models\Order;
use App\Models\ProductStorePrice;
use App\Models\Retailer;
use App\Models\StockLevel;
use App\Models\TownshipStore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    /**
     * Create an approved retailer, their store, one product, stock + price for it,
     * authenticate via Sanctum, and return everything needed for order tests.
     *
     * @return array{user: User, retailer: Retailer, store: TownshipStore, product_id: int, price: float}
     */
    private function approvedRetailerWithStock(int $onHand = 50, int $reserved = 0): array
    {
        $store    = TownshipStore::factory()->create();
        $user     = User::factory()->retailer()->create();
        $retailer = Retailer::factory()->approved()->create([
            'user_id'  => $user->id,
            'store_id' => $store->id,
        ]);

        // Create a product with a price and stock at this store
        $stockLevel = StockLevel::factory()
            ->for($store, 'store')
            ->withStock($onHand, $reserved)
            ->create();

        $price = ProductStorePrice::factory()->create([
            'product_id' => $stockLevel->product_id,
            'store_id'   => $store->id,
            'price_pkr'  => 250.00,
            'is_active'  => true,
        ]);

        Sanctum::actingAs($user, ['*']);

        return [
            'user'       => $user,
            'retailer'   => $retailer,
            'store'      => $store,
            'product_id' => $stockLevel->product_id,
            'price'      => 250.00,
        ];
    }

    private function placeOrderPayload(int $productId, int $qty = 2): array
    {
        return [
            'items' => [
                ['product_id' => $productId, 'qty' => $qty],
            ],
        ];
    }

    // ──────────────────────────────────────────────
    // POST /api/retailer/orders — success
    // ──────────────────────────────────────────────

    public function test_approved_retailer_can_place_an_order(): void
    {
        ['product_id' => $productId] = $this->approvedRetailerWithStock();

        $this->postJson('/api/retailer/orders', $this->placeOrderPayload($productId, 2))
            ->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'status', 'total_pkr', 'items'],
            ]);
    }

    public function test_order_is_persisted_with_correct_total(): void
    {
        ['product_id' => $productId, 'price' => $price] = $this->approvedRetailerWithStock();
        $qty = 3;

        $this->postJson('/api/retailer/orders', $this->placeOrderPayload($productId, $qty))
            ->assertCreated();

        $this->assertDatabaseHas('orders', [
            'total_pkr' => $price * $qty,
            'status'    => Order::STATUS_PENDING,
        ]);
    }

    public function test_placing_an_order_increments_qty_reserved_on_stock(): void
    {
        ['product_id' => $productId, 'store' => $store] = $this->approvedRetailerWithStock(50, 0);

        $this->postJson('/api/retailer/orders', $this->placeOrderPayload($productId, 5))
            ->assertCreated();

        $this->assertDatabaseHas('stock_levels', [
            'product_id'  => $productId,
            'store_id'    => $store->id,
            'qty_reserved' => 5,
        ]);
    }

    public function test_order_creates_an_initial_status_history_row(): void
    {
        ['product_id' => $productId] = $this->approvedRetailerWithStock();

        $response = $this->postJson('/api/retailer/orders', $this->placeOrderPayload($productId, 1))
            ->assertCreated();

        $orderId = $response->json('data.id');
        $this->assertDatabaseHas('order_status_history', [
            'order_id'    => $orderId,
            'from_status' => null,
            'to_status'   => Order::STATUS_PENDING,
        ]);
    }

    public function test_duplicate_product_ids_in_items_are_merged(): void
    {
        ['product_id' => $productId] = $this->approvedRetailerWithStock(100, 0);

        // Send the same product twice — quantities should be summed (total 6)
        $this->postJson('/api/retailer/orders', [
            'items' => [
                ['product_id' => $productId, 'qty' => 4],
                ['product_id' => $productId, 'qty' => 2],
            ],
        ])->assertCreated();

        $this->assertDatabaseHas('stock_levels', ['qty_reserved' => 6]);
    }

    // ──────────────────────────────────────────────
    // POST /api/retailer/orders — stock / availability failures
    // ──────────────────────────────────────────────

    public function test_order_fails_when_stock_is_insufficient(): void
    {
        // Only 3 available (5 on hand, 2 reserved → 3 available)
        ['product_id' => $productId] = $this->approvedRetailerWithStock(5, 2);

        $this->postJson('/api/retailer/orders', $this->placeOrderPayload($productId, 10))
            ->assertUnprocessable();
    }

    public function test_order_fails_when_product_has_no_price_for_the_store(): void
    {
        // Set up retailer + stock but NO ProductStorePrice
        $store    = TownshipStore::factory()->create();
        $user     = User::factory()->retailer()->create();
        Retailer::factory()->approved()->create(['user_id' => $user->id, 'store_id' => $store->id]);

        $stockLevel = StockLevel::factory()->for($store, 'store')->withStock(50)->create();
        // Intentionally no ProductStorePrice created

        Sanctum::actingAs($user, ['*']);

        $this->postJson('/api/retailer/orders', $this->placeOrderPayload($stockLevel->product_id, 1))
            ->assertUnprocessable();
    }

    // ──────────────────────────────────────────────
    // POST /api/retailer/orders — authorization
    // ──────────────────────────────────────────────

    public function test_pending_kyc_retailer_cannot_place_order(): void
    {
        // Retailer with kyc_status = 'pending' — policy denies placeOrder
        $store    = TownshipStore::factory()->create();
        $user     = User::factory()->retailer()->create();
        Retailer::factory()->create([   // default is pending
            'user_id'  => $user->id,
            'store_id' => $store->id,
        ]);

        $stockLevel = StockLevel::factory()->for($store, 'store')->withStock(50)->create();
        ProductStorePrice::factory()->create([
            'product_id' => $stockLevel->product_id,
            'store_id'   => $store->id,
        ]);

        Sanctum::actingAs($user, ['*']);

        $this->postJson('/api/retailer/orders', $this->placeOrderPayload($stockLevel->product_id, 1))
            ->assertForbidden();
    }

    public function test_admin_user_cannot_place_a_retailer_order(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin, ['*']);

        $this->postJson('/api/retailer/orders', ['items' => [['product_id' => 1, 'qty' => 1]]])
            ->assertForbidden();
    }

    public function test_unauthenticated_order_request_is_401(): void
    {
        $this->postJson('/api/retailer/orders', ['items' => [['product_id' => 1, 'qty' => 1]]])
            ->assertUnauthorized();
    }

    // ──────────────────────────────────────────────
    // POST /api/retailer/orders — validation
    // ──────────────────────────────────────────────

    public function test_order_requires_items_array(): void
    {
        $this->approvedRetailerWithStock();

        $this->postJson('/api/retailer/orders', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items']);
    }

    public function test_order_rejects_empty_items_array(): void
    {
        $this->approvedRetailerWithStock();

        $this->postJson('/api/retailer/orders', ['items' => []])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items']);
    }

    public function test_order_rejects_qty_of_zero(): void
    {
        ['product_id' => $productId] = $this->approvedRetailerWithStock();

        $this->postJson('/api/retailer/orders', $this->placeOrderPayload($productId, 0))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items.0.qty']);
    }

    // ──────────────────────────────────────────────
    // GET /api/retailer/orders — order history
    // ──────────────────────────────────────────────

    public function test_retailer_can_fetch_own_order_history(): void
    {
        ['retailer' => $retailer, 'store' => $store] = $this->approvedRetailerWithStock();

        // Seed a couple of orders for this retailer
        Order::factory()->count(2)->create([
            'retailer_id' => $retailer->id,
            'store_id'    => $store->id,
        ]);

        $this->getJson('/api/retailer/orders')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'status', 'total_pkr']],
                'meta',
            ]);
    }

    public function test_order_history_is_scoped_to_authenticated_retailer(): void
    {
        // Two retailers — each places one order
        ['retailer' => $r1, 'store' => $s1] = $this->approvedRetailerWithStock();
        $order1 = Order::factory()->create(['retailer_id' => $r1->id, 'store_id' => $s1->id]);

        // Second retailer and store
        $store2 = TownshipStore::factory()->create();
        $user2  = User::factory()->retailer()->create();
        $r2     = Retailer::factory()->approved()->create(['user_id' => $user2->id, 'store_id' => $store2->id]);
        Order::factory()->create(['retailer_id' => $r2->id, 'store_id' => $store2->id]);

        // Authenticated as retailer 1 — should only see own order
        $response = $this->getJson('/api/retailer/orders')->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($order1->id, $ids);
        $this->assertCount(1, $ids);
    }

    public function test_order_history_is_paginated(): void
    {
        ['retailer' => $retailer, 'store' => $store] = $this->approvedRetailerWithStock();

        Order::factory()->count(20)->create([
            'retailer_id' => $retailer->id,
            'store_id'    => $store->id,
        ]);

        $this->getJson('/api/retailer/orders')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'last_page', 'total']]);
    }

    public function test_unauthenticated_order_history_request_is_401(): void
    {
        $this->getJson('/api/retailer/orders')
            ->assertUnauthorized();
    }
}
