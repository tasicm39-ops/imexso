<?php

namespace Tests\Feature\Api;

use App\Models\SavedSearch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavedSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->validated()->create();
    }

    public function test_guest_cannot_access_saved_searches(): void
    {
        $this->getJson('/api/saved-searches')
            ->assertUnauthorized();
    }

    public function test_user_can_list_their_saved_searches(): void
    {
        SavedSearch::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/saved-searches')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_only_sees_their_own_saved_searches(): void
    {
        $otherUser = User::factory()->validated()->create();
        SavedSearch::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/saved-searches')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_user_can_save_a_search(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/saved-searches', [
                'name' => 'My BMW Search',
                'filters' => [
                    'make' => 'BMW',
                    'fuel_type' => 'DIESEL',
                    'price_max' => 30000,
                ],
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'My BMW Search');

        $this->assertDatabaseHas('saved_searches', [
            'user_id' => $this->user->id,
            'name' => 'My BMW Search',
        ]);
    }

    public function test_user_can_save_search_without_name(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/saved-searches', [
                'filters' => [
                    'make' => 'AUDI',
                ],
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', null);
    }

    public function test_user_can_delete_their_saved_search(): void
    {
        $search = SavedSearch::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/saved-searches/{$search->id}")
            ->assertOk();

        $this->assertDatabaseMissing('saved_searches', ['id' => $search->id]);
    }

    public function test_user_cannot_delete_another_users_saved_search(): void
    {
        $otherUser = User::factory()->validated()->create();
        $search = SavedSearch::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/saved-searches/{$search->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('saved_searches', ['id' => $search->id]);
    }

    public function test_saved_search_requires_filters(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/saved-searches', [
                'name' => 'Test',
            ])
            ->assertUnprocessable();
    }

    public function test_saved_search_filters_must_be_array(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/saved-searches', [
                'filters' => 'not-an-array',
            ])
            ->assertUnprocessable();
    }
}
