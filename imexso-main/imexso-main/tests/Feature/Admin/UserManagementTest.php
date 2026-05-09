<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_guests_cannot_access_user_list(): void
    {
        $this->get(route('admin.users.index'))
            ->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_user_list(): void
    {
        $user = User::factory()->validated()->create();

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_list_users(): void
    {
        User::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/users/index')
                ->has('users.data', 6)
            );
    }

    public function test_admin_can_filter_users_by_pending_status(): void
    {
        User::factory()->count(3)->create(['is_validated' => false]);
        User::factory()->count(2)->validated()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['status' => 'pending']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('users.data', 3)
            );
    }

    public function test_admin_can_search_users(): void
    {
        User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['search' => 'John']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('users.data', 1)
            );
    }

    public function test_admin_can_approve_user(): void
    {
        $user = User::factory()->create(['is_validated' => false]);

        $this->actingAs($this->admin)
            ->post(route('admin.users.approve', $user))
            ->assertRedirect();

        $user->refresh();
        $this->assertTrue($user->is_validated);
        $this->assertTrue($user->is_active);
    }

    public function test_admin_can_reject_user(): void
    {
        $user = User::factory()->validated()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.users.reject', $user))
            ->assertRedirect();

        $user->refresh();
        $this->assertFalse($user->is_validated);
        $this->assertFalse($user->is_active);
    }

    public function test_admin_can_toggle_admin_role(): void
    {
        $user = User::factory()->validated()->create(['is_admin' => false]);

        $this->actingAs($this->admin)
            ->post(route('admin.users.toggle-admin', $user))
            ->assertRedirect();

        $user->refresh();
        $this->assertTrue($user->is_admin);

        $this->actingAs($this->admin)
            ->post(route('admin.users.toggle-admin', $user))
            ->assertRedirect();

        $user->refresh();
        $this->assertFalse($user->is_admin);
    }

    public function test_non_admin_cannot_approve_user(): void
    {
        $user = User::factory()->validated()->create();
        $pendingUser = User::factory()->create(['is_validated' => false]);

        $this->actingAs($user)
            ->post(route('admin.users.approve', $pendingUser))
            ->assertForbidden();

        $pendingUser->refresh();
        $this->assertFalse($pendingUser->is_validated);
    }
}
