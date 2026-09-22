<?php

namespace Tests\Feature\Retailer;

use App\Models\Retailer;
use App\Models\TownshipStore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class KycUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    private function actAsRetailer(array $retailerOverrides = []): array
    {
        $user     = User::factory()->retailer()->create();
        $store    = TownshipStore::factory()->create();
        $retailer = Retailer::factory()->create(array_merge([
            'user_id'      => $user->id,
            'store_id'     => $store->id,
            'kyc_documents' => null,
        ], $retailerOverrides));

        Sanctum::actingAs($user, ['*']);

        return compact('user', 'retailer', 'store');
    }

    private function fakePdf(string $name = 'document.pdf'): UploadedFile
    {
        return UploadedFile::fake()->create($name, 100, 'application/pdf');
    }

    private function fakeImage(string $name = 'photo.jpg'): UploadedFile
    {
        return UploadedFile::fake()->image($name);
    }

    // ──────────────────────────────────────────────
    // First upload (no existing docs — all required)
    // ──────────────────────────────────────────────

    public function test_retailer_can_upload_kyc_documents_for_the_first_time(): void
    {
        ['retailer' => $retailer] = $this->actAsRetailer();

        $this->postJson('/api/retailer/kyc/upload', [
            'cnic_front' => $this->fakeImage('front.jpg'),
            'cnic_back'  => $this->fakeImage('back.jpg'),
        ])
            ->assertOk()
            ->assertJsonPath('message', fn ($msg) => str_contains($msg, 'uploaded'));

        $retailer->refresh();
        $this->assertNotNull($retailer->kyc_documents['cnic_front'] ?? null);
        $this->assertNotNull($retailer->kyc_documents['cnic_back'] ?? null);
    }

    public function test_first_upload_stores_files_on_private_disk(): void
    {
        ['retailer' => $retailer] = $this->actAsRetailer();

        $this->postJson('/api/retailer/kyc/upload', [
            'cnic_front' => $this->fakeImage('front.jpg'),
            'cnic_back'  => $this->fakeImage('back.jpg'),
        ])->assertOk();

        $retailer->refresh();
        Storage::disk('private')->assertExists($retailer->kyc_documents['cnic_front']);
        Storage::disk('private')->assertExists($retailer->kyc_documents['cnic_back']);
    }

    public function test_first_upload_sets_kyc_status_to_pending(): void
    {
        ['retailer' => $retailer] = $this->actAsRetailer([
            'kyc_status' => 'rejected',
        ]);

        $this->postJson('/api/retailer/kyc/upload', [
            'cnic_front' => $this->fakeImage('front.jpg'),
            'cnic_back'  => $this->fakeImage('back.jpg'),
        ])->assertOk();

        $this->assertDatabaseHas('retailers', [
            'id'         => $retailer->id,
            'kyc_status' => 'pending',
        ]);
    }

    public function test_upload_clears_rejection_reason(): void
    {
        ['retailer' => $retailer] = $this->actAsRetailer([
            'kyc_status'           => 'rejected',
            'kyc_rejection_reason' => 'Documents were blurry.',
        ]);

        $this->postJson('/api/retailer/kyc/upload', [
            'cnic_front' => $this->fakeImage('front.jpg'),
            'cnic_back'  => $this->fakeImage('back.jpg'),
        ])->assertOk();

        $this->assertDatabaseHas('retailers', [
            'id'                   => $retailer->id,
            'kyc_rejection_reason' => null,
        ]);
    }

    public function test_optional_business_doc_can_be_included(): void
    {
        ['retailer' => $retailer] = $this->actAsRetailer();

        $this->postJson('/api/retailer/kyc/upload', [
            'cnic_front'   => $this->fakeImage('front.jpg'),
            'cnic_back'    => $this->fakeImage('back.jpg'),
            'business_doc' => $this->fakePdf('business.pdf'),
        ])->assertOk();

        $retailer->refresh();
        $this->assertNotNull($retailer->kyc_documents['business_doc'] ?? null);
    }

    // ──────────────────────────────────────────────
    // Re-upload (existing docs — fields become nullable)
    // ──────────────────────────────────────────────

    public function test_re_upload_merges_with_existing_documents(): void
    {
        ['retailer' => $retailer] = $this->actAsRetailer([
            'kyc_documents' => [
                'cnic_front' => 'kyc/1/front.jpg',
                'cnic_back'  => 'kyc/1/back.jpg',
            ],
        ]);

        // Only upload a new business_doc — the cnic files should be preserved
        $this->postJson('/api/retailer/kyc/upload', [
            'business_doc' => $this->fakePdf('business.pdf'),
        ])->assertOk();

        $retailer->refresh();
        $this->assertNotNull($retailer->kyc_documents['cnic_front']);
        $this->assertNotNull($retailer->kyc_documents['business_doc']);
    }

    // ──────────────────────────────────────────────
    // Validation failures
    // ──────────────────────────────────────────────

    public function test_first_upload_requires_cnic_front_and_back(): void
    {
        $this->actAsRetailer();

        $this->postJson('/api/retailer/kyc/upload', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['cnic_front', 'cnic_back']);
    }

    public function test_upload_rejects_files_exceeding_5mb(): void
    {
        $this->actAsRetailer();

        $big = UploadedFile::fake()->create('big.pdf', 6000, 'application/pdf'); // 6 MB

        $this->postJson('/api/retailer/kyc/upload', [
            'cnic_front' => $big,
            'cnic_back'  => $this->fakeImage('back.jpg'),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['cnic_front']);
    }

    public function test_upload_rejects_disallowed_mime_types(): void
    {
        $this->actAsRetailer();

        $txt = UploadedFile::fake()->create('note.txt', 10, 'text/plain');

        $this->postJson('/api/retailer/kyc/upload', [
            'cnic_front' => $txt,
            'cnic_back'  => $this->fakeImage('back.jpg'),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['cnic_front']);
    }

    // ──────────────────────────────────────────────
    // Authorization
    // ──────────────────────────────────────────────

    public function test_non_retailer_is_403(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin, ['*']);

        $this->postJson('/api/retailer/kyc/upload', [
            'cnic_front' => $this->fakeImage('front.jpg'),
            'cnic_back'  => $this->fakeImage('back.jpg'),
        ])->assertForbidden();
    }

    public function test_unauthenticated_request_is_401(): void
    {
        $this->postJson('/api/retailer/kyc/upload', [])
            ->assertUnauthorized();
    }
}
