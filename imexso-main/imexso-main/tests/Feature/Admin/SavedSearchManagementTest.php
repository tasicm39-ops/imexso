<?php

namespace Tests\Feature\Admin;

use App\Models\SavedSearch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavedSearchManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_guests_cannot_access_saved_searches_admin(): void
    {
        $this->get(route('admin.saved-searches.index'))
            ->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_saved_searches_admin(): void
    {
        $user = User::factory()->validated()->create();

        $this->actingAs($user)
            ->get(route('admin.saved-searches.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_saved_searches_list(): void
    {
        $user = User::factory()->validated()->create();
        SavedSearch::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.saved-searches.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/saved-searches/index')
                ->has('savedSearches.data', 1)
                ->has('users')
            );
    }

    public function test_admin_can_filter_saved_searches_by_user(): void
    {
        $user1 = User::factory()->validated()->create();
        $user2 = User::factory()->validated()->create();

        SavedSearch::factory()->create(['user_id' => $user1->id]);
        SavedSearch::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.saved-searches.index', ['user_id' => $user1->id]));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('savedSearches.data', 1)
            );
    }

    public function test_admin_can_search_saved_searches(): void
    {
        $user = User::factory()->validated()->create(['name' => 'Jane Smith']);
        SavedSearch::factory()->create([
            'user_id' => $user->id,
            'name' => 'My BMW search',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.saved-searches.index', ['search' => 'BMW']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('savedSearches.data', 1)
            );
    }
}
