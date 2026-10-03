<?php

namespace Tests\Feature\Auth;

use App\Livewire\Retailer\Auth\Login;
use App\Models\Retailer;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests for the multiple-auth-guard / multi-role system.
 *
 * Scenarios covered:
 *  A. hasPortalRole() — pivot lookup, legacy fallback, grant, revoke
 *  B. Retailer guard — login success & failure paths
 *  C. Retailer guard — logout clears only the retailer session
 *  D. Middleware — RetailerAuth, EnsureRetailer enforce correct guard
 *  E. Simultaneous sessions — admin guard and retailer guard coexist
 *  F. canAccessPanel() — panel routing respects multi-role
 *  G. Inactive / non-retailer users are rejected
 */
class MultiRoleGuardTest extends TestCase
{
    use RefreshDatabase;

    // ═══════════════════════════════════════════════════════════════
    // Helpers
    // ═══════════════════════════════════════════════════════════════

    /** Plain user with role='retailer' on the legacy column only (no pivot row). */
    private function makeRetailerLegacy(array $attrs = []): User
    {
        return User::factory()->retailer()->create($attrs);
    }

    /** User with role='admin' on the legacy column + pivot row. */
    private function makeAdmin(array $attrs = []): User
    {
        $user = User::factory()->admin()->create($attrs);
        UserRole::create(['user_id' => $user->id, 'role' => 'admin']);
        return $user;
    }

    /** User with both admin and retailer pivot rows (the dual-role scenario). */
    private function makeDualRole(array $attrs = []): User
    {
        $user = User::factory()->admin()->create($attrs);
        UserRole::create(['user_id' => $user->id, 'role' => 'admin']);
        UserRole::create(['user_id' => $user->id, 'role' => 'retailer']);
        return $user;
    }

    /** Retailer with a pivot row + approved KYC profile. */
    private function makeApprovedRetailer(array $attrs = []): User
    {
        $user = User::factory()->retailer()->create($attrs);
        UserRole::create(['user_id' => $user->id, 'role' => 'retailer']);
        Retailer::factory()->approved()->create(['user_id' => $user->id]);
        return $user;
    }

    /** Retailer with a pivot row + pending KYC profile. */
    private function makePendingRetailer(array $attrs = []): User
    {
        $user = User::factory()->retailer()->create($attrs);
        UserRole::create(['user_id' => $user->id, 'role' => 'retailer']);
        Retailer::factory()->create(['user_id' => $user->id]); // default: pending
        return $user;
    }

    // ═══════════════════════════════════════════════════════════════
    // A. hasPortalRole() — pivot lookup & legacy fallback
    // ═══════════════════════════════════════════════════════════════

    public function test_hasPortalRole_returns_true_from_pivot(): void
    {
        $user = User::factory()->admin()->create();
        UserRole::create(['user_id' => $user->id, 'role' => 'retailer']);

        $this->assertTrue($user->hasPortalRole('retailer'));
    }

    public function test_hasPortalRole_falls_back_to_legacy_role_column(): void
    {
        // No pivot row — relies on users.role
        $user = $this->makeRetailerLegacy();

        $this->assertTrue($user->hasPortalRole('retailer'));
    }

    public function test_hasPortalRole_returns_false_when_role_absent(): void
    {
        $user = User::factory()->admin()->create();
        // No pivot row for 'retailer', and legacy column = 'admin'

        $this->assertFalse($user->hasPortalRole('retailer'));
    }

    public function test_hasPortalRole_works_with_eager_loaded_relation(): void
    {
        $user = User::factory()->retailer()->create();
        UserRole::create(['user_id' => $user->id, 'role' => 'retailer']);

        $loaded = User::with('portalRoles')->find($user->id);

        $this->assertTrue($loaded->hasPortalRole('retailer'));
    }

    public function test_grantPortalRole_adds_pivot_row(): void
    {
        $user = User::factory()->admin()->create();
        $this->assertFalse($user->hasPortalRole('retailer'));

        $user->grantPortalRole('retailer');

        $this->assertTrue($user->fresh()->hasPortalRole('retailer'));
        $this->assertDatabaseHas('user_roles', ['user_id' => $user->id, 'role' => 'retailer']);
    }

    public function test_grantPortalRole_is_idempotent(): void
    {
        $user = User::factory()->retailer()->create();
        $user->grantPortalRole('retailer');
        $user->grantPortalRole('retailer'); // second call must not throw or duplicate

        $this->assertCount(
            1,
            UserRole::where(['user_id' => $user->id, 'role' => 'retailer'])->get()
        );
    }

    public function test_revokePortalRole_removes_pivot_row(): void
    {
        $user = User::factory()->retailer()->create();
        UserRole::create(['user_id' => $user->id, 'role' => 'retailer']);

        $user->revokePortalRole('retailer');

        $this->assertDatabaseMissing('user_roles', ['user_id' => $user->id, 'role' => 'retailer']);
    }

    public function test_allPortalRoles_returns_merged_role_list(): void
    {
        $user = $this->makeDualRole();

        $roles = $user->allPortalRoles();

        $this->assertContains('admin', $roles);
        $this->assertContains('retailer', $roles);
    }

    // ═══════════════════════════════════════════════════════════════
    // B. Retailer guard — login paths
    //
    // retailer.login is a GET-only Livewire page (App\Livewire\Retailer\Auth\Login);
    // there is no POST route to submit to, so the login flow is exercised via
    // Livewire::test() against the component's authenticate() action instead
    // of $this->post(route('retailer.login')).
    // ═══════════════════════════════════════════════════════════════

    public function test_retailer_can_login_and_is_authenticated_on_retailer_guard(): void
    {
        $user = $this->makeApprovedRetailer(['password' => bcrypt('Secret99!')]);

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'Secret99!')
            ->call('authenticate')
            ->assertRedirect(route('retailer.dashboard'));

        $this->assertAuthenticatedAs($user, 'retailer');
        $this->assertGuest('web'); // web guard must remain untouched
    }

    public function test_pending_retailer_is_redirected_to_pending_page(): void
    {
        $user = $this->makePendingRetailer(['password' => bcrypt('Secret99!')]);

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'Secret99!')
            ->call('authenticate')
            ->assertRedirect(route('retailer.pending'));

        $this->assertAuthenticatedAs($user, 'retailer');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = $this->makeApprovedRetailer(['password' => bcrypt('Correct1!')]);

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'WrongPass!')
            ->call('authenticate')
            ->assertHasErrors('email');

        $this->assertGuest('retailer');
    }

    public function test_non_retailer_user_is_rejected_at_login(): void
    {
        // User with legacy role='admin', no retailer pivot row
        $admin = $this->makeAdmin(['password' => bcrypt('Admin123!')]);

        Livewire::test(Login::class)
            ->set('email', $admin->email)
            ->set('password', 'Admin123!')
            ->call('authenticate')
            ->assertHasErrors('email');

        $this->assertGuest('retailer');
    }

    public function test_inactive_user_is_rejected_at_login(): void
    {
        $user = $this->makeApprovedRetailer([
            'password'  => bcrypt('Secret99!'),
            'is_active' => false,
        ]);

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'Secret99!')
            ->call('authenticate')
            ->assertHasErrors('email');

        $this->assertGuest('retailer');
    }

    // ═══════════════════════════════════════════════════════════════
    // C. Retailer guard — logout
    // ═══════════════════════════════════════════════════════════════

    public function test_logout_clears_retailer_guard_session(): void
    {
        $user = $this->makeApprovedRetailer();

        $this->actingAs($user, 'retailer')
             ->post(route('retailer.logout'))
             ->assertRedirect(route('public.home'));

        $this->assertGuest('retailer');
    }

    public function test_logout_does_not_affect_web_guard_session(): void
    {
        $retailer = $this->makeApprovedRetailer();
        $webUser  = User::factory()->create(); // any user on the web guard

        // Simulate web guard session being active
        $this->actingAs($webUser, 'web');

        // Logout on retailer guard
        $this->actingAs($retailer, 'retailer')
             ->post(route('retailer.logout'));

        // Web guard session should be untouched
        $this->assertAuthenticatedAs($webUser, 'web');
    }

    // ═══════════════════════════════════════════════════════════════
    // D. Middleware — RetailerAuth, EnsureRetailer
    // ═══════════════════════════════════════════════════════════════

    public function test_protected_retailer_route_redirects_unauthenticated_user(): void
    {
        $this->get(route('retailer.dashboard'))
             ->assertRedirect(route('retailer.login'));
    }

    public function test_protected_retailer_route_rejects_web_guard_user(): void
    {
        // Authenticated on web guard, NOT on retailer guard
        $user = User::factory()->retailer()->create();
        $this->actingAs($user, 'web')
             ->get(route('retailer.dashboard'))
             ->assertRedirect(route('retailer.login'));
    }

    public function test_protected_route_allows_authenticated_retailer_guard_user(): void
    {
        $user = $this->makeApprovedRetailer();

        $this->actingAs($user, 'retailer')
             ->get(route('retailer.dashboard'))
             ->assertOk();
    }

    public function test_non_retailer_on_retailer_guard_is_forbidden(): void
    {
        // Admin user authenticated on retailer guard — EnsureRetailer must block them
        $admin = $this->makeAdmin();

        $this->actingAs($admin, 'retailer')
             ->get(route('retailer.dashboard'))
             ->assertForbidden();
    }

    // ═══════════════════════════════════════════════════════════════
    // E. Simultaneous sessions — dual guard, no overlap
    // ═══════════════════════════════════════════════════════════════

    public function test_dual_role_user_can_authenticate_on_both_guards_independently(): void
    {
        $user = $this->makeDualRole(['password' => bcrypt('Dual123!')]);

        // Authenticate on retailer guard
        Auth::guard('retailer')->login($user);
        // Authenticate on admin guard (Filament would do this)
        Auth::guard('admin')->login($user);

        $this->assertAuthenticatedAs($user, 'retailer');
        $this->assertAuthenticatedAs($user, 'admin');
    }

    public function test_logging_out_of_retailer_guard_does_not_clear_admin_guard(): void
    {
        $user = $this->makeDualRole();

        Auth::guard('admin')->login($user);
        Auth::guard('retailer')->login($user);

        // Logout retailer only
        Auth::guard('retailer')->logout();

        $this->assertGuest('retailer');
        $this->assertAuthenticatedAs($user, 'admin');
    }

    public function test_logging_out_of_admin_guard_does_not_clear_retailer_guard(): void
    {
        $user = $this->makeDualRole();

        Auth::guard('admin')->login($user);
        Auth::guard('retailer')->login($user);

        // Logout admin only
        Auth::guard('admin')->logout();

        $this->assertGuest('admin');
        $this->assertAuthenticatedAs($user, 'retailer');
    }

    public function test_single_role_admin_cannot_access_retailer_portal(): void
    {
        $admin = $this->makeAdmin(); // admin only, no retailer pivot row

        $this->actingAs($admin, 'retailer')
             ->get(route('retailer.dashboard'))
             ->assertForbidden();
    }

    // ═══════════════════════════════════════════════════════════════
    // F. canAccessPanel()
    // ═══════════════════════════════════════════════════════════════

    public function test_admin_user_can_access_admin_panel(): void
    {
        $user  = $this->makeAdmin();
        $panel = \Filament\Facades\Filament::getPanel('admin');

        $this->assertTrue($user->canAccessPanel($panel));
    }

    public function test_admin_user_cannot_access_store_panel(): void
    {
        $user  = $this->makeAdmin();
        $panel = \Filament\Facades\Filament::getPanel('store');

        $this->assertFalse($user->canAccessPanel($panel));
    }

    public function test_dual_role_user_can_access_admin_panel(): void
    {
        $user  = $this->makeDualRole();
        $panel = \Filament\Facades\Filament::getPanel('admin');

        $this->assertTrue($user->canAccessPanel($panel));
    }

    public function test_inactive_user_cannot_access_any_panel(): void
    {
        $user = $this->makeAdmin(['is_active' => false]);

        foreach (['admin', 'store', 'huashu'] as $panelId) {
            $this->assertFalse(
                $user->canAccessPanel(\Filament\Facades\Filament::getPanel($panelId)),
                "Expected inactive user to be denied panel [{$panelId}]"
            );
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // G. Guest middleware — logged-in retailer is redirected away
    // ═══════════════════════════════════════════════════════════════

    public function test_authenticated_retailer_is_redirected_from_login_page(): void
    {
        // AppServiceProvider::boot() sends an already-authenticated retailer
        // to retailer.home (the storefront), not retailer.dashboard.
        $user = $this->makeApprovedRetailer();

        $this->actingAs($user, 'retailer')
             ->get(route('retailer.login'))
             ->assertRedirect(route('retailer.home'));
    }

    public function test_unauthenticated_user_can_access_login_page(): void
    {
        $this->get(route('retailer.login'))->assertOk();
    }
}
