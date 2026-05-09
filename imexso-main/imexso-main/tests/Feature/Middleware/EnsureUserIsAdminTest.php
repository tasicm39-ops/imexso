<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureUserIsAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_can_access_admin_routes(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->getJson(route('api.api-clients.index'))
            ->assertOk();
    }

    public function test_non_admin_user_cannot_access_admin_routes(): void
    {
        $user = User::factory()->validated()->create();

        $this->actingAs($user)
            ->getJson(route('api.api-clients.index'))
            ->assertForbidden();
    }

    public function test_unvalidated_user_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create(['is_validated' => false, 'is_admin' => false]);

        $this->actingAs($user)
            ->getJson(route('api.api-clients.index'))
            ->assertForbidden();
    }
}
