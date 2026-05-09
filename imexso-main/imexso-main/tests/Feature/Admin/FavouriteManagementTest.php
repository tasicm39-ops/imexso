<?php

namespace Tests\Feature\Admin;

use App\Models\Car;
use App\Models\Favourite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavouriteManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_guests_cannot_access_favourites_admin(): void
    {
        $this->get(route('admin.favourites.index'))
            ->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_favourites_admin(): void
    {
        $user = User::factory()->validated()->create();

        $this->actingAs($user)
            ->get(route('admin.favourites.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_favourites_list(): void
    {
        $user = User::factory()->validated()->create();
        $car = Car::factory()->create();
        Favourite::factory()->create(['user_id' => $user->id, 'car_id' => $car->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.favourites.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/favourites/index')
                ->has('favourites.data', 1)
                ->has('users')
            );
    }

    public function test_admin_can_filter_favourites_by_user(): void
    {
        $user1 = User::factory()->validated()->create();
        $user2 = User::factory()->validated()->create();
        $car = Car::factory()->create();

        Favourite::factory()->create(['user_id' => $user1->id, 'car_id' => $car->id]);
        Favourite::factory()->create(['user_id' => $user2->id, 'car_id' => Car::factory()->create()->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.favourites.index', ['user_id' => $user1->id]));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('favourites.data', 1)
            );
    }

    public function test_admin_can_search_favourites(): void
    {
        $user = User::factory()->validated()->create(['name' => 'John Doe']);
        $car = Car::factory()->create();
        Favourite::factory()->create(['user_id' => $user->id, 'car_id' => $car->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.favourites.index', ['search' => 'John']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('favourites.data', 1)
            );
    }
}
