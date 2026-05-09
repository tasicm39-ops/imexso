<?php

namespace Tests\Feature\Api;

use App\Models\Car;
use App\Models\Favourite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavouriteTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->validated()->create();
    }

    public function test_guest_cannot_access_favourites(): void
    {
        $this->getJson('/api/favourites')
            ->assertUnauthorized();
    }

    public function test_user_can_list_their_favourites(): void
    {
        $car = Car::factory()->create();
        Favourite::factory()->create(['user_id' => $this->user->id, 'car_id' => $car->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/favourites')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_only_sees_their_own_favourites(): void
    {
        $otherUser = User::factory()->validated()->create();
        $car = Car::factory()->create();
        Favourite::factory()->create(['user_id' => $otherUser->id, 'car_id' => $car->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/favourites')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_user_can_add_favourite(): void
    {
        $car = Car::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/favourites', ['car_id' => $car->id]);

        $response->assertCreated();
        $this->assertDatabaseHas('favourites', [
            'user_id' => $this->user->id,
            'car_id' => $car->id,
        ]);
    }

    public function test_user_can_toggle_favourite_off(): void
    {
        $car = Car::factory()->create();
        Favourite::factory()->create(['user_id' => $this->user->id, 'car_id' => $car->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/favourites', ['car_id' => $car->id]);

        $response->assertOk()
            ->assertJson(['favourited' => false]);

        $this->assertDatabaseMissing('favourites', [
            'user_id' => $this->user->id,
            'car_id' => $car->id,
        ]);
    }

    public function test_user_can_remove_favourite_via_delete(): void
    {
        $car = Car::factory()->create();
        Favourite::factory()->create(['user_id' => $this->user->id, 'car_id' => $car->id]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/favourites/{$car->id}")
            ->assertOk()
            ->assertJson(['favourited' => false]);

        $this->assertDatabaseMissing('favourites', [
            'user_id' => $this->user->id,
            'car_id' => $car->id,
        ]);
    }

    public function test_delete_nonexistent_favourite_returns_404(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/favourites/9999')
            ->assertNotFound();
    }

    public function test_user_can_get_favourite_car_ids(): void
    {
        $car1 = Car::factory()->create();
        $car2 = Car::factory()->create();
        Favourite::factory()->create(['user_id' => $this->user->id, 'car_id' => $car1->id]);
        Favourite::factory()->create(['user_id' => $this->user->id, 'car_id' => $car2->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/favourites/car-ids');

        $response->assertOk()
            ->assertJsonCount(2, 'car_ids');
    }

    public function test_favourite_requires_valid_car_id(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/favourites', ['car_id' => 9999])
            ->assertUnprocessable();
    }

    public function test_favourite_requires_car_id(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/favourites', [])
            ->assertUnprocessable();
    }
}
