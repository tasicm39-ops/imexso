<?php

namespace Tests\Feature\Api;

use App\Models\Car;
use App\Models\CarCartItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarCartTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->validated()->create();
    }

    public function test_guests_cannot_add_to_cart(): void
    {
        $car = Car::factory()->create(['sync_status' => 'active']);

        $this->postJson(route('api.cart.store'), ['car_id' => $car->id])
            ->assertUnauthorized();
    }

    public function test_user_can_add_car_to_cart(): void
    {
        $car = Car::factory()->create(['sync_status' => 'active']);

        $this->actingAs($this->user)
            ->postJson(route('api.cart.store'), ['car_id' => $car->id])
            ->assertCreated()
            ->assertJsonPath('in_cart', true)
            ->assertJsonPath('created', true);

        $this->assertDatabaseHas('car_cart_items', [
            'user_id' => $this->user->id,
            'car_id' => $car->id,
        ]);
    }

    public function test_adding_same_car_twice_is_idempotent(): void
    {
        $car = Car::factory()->create(['sync_status' => 'active']);

        $this->actingAs($this->user)
            ->postJson(route('api.cart.store'), ['car_id' => $car->id])
            ->assertCreated();

        $this->actingAs($this->user)
            ->postJson(route('api.cart.store'), ['car_id' => $car->id])
            ->assertOk()
            ->assertJsonPath('in_cart', true)
            ->assertJsonPath('created', false);

        $this->assertDatabaseCount('car_cart_items', 1);
    }

    public function test_cannot_add_sold_car_to_cart(): void
    {
        $car = Car::factory()->create(['sync_status' => 'sold']);

        $this->actingAs($this->user)
            ->postJson(route('api.cart.store'), ['car_id' => $car->id])
            ->assertStatus(422);
    }

    public function test_user_can_remove_car_from_cart(): void
    {
        $car = Car::factory()->create(['sync_status' => 'active']);
        CarCartItem::factory()->create([
            'user_id' => $this->user->id,
            'car_id' => $car->id,
        ]);

        $this->actingAs($this->user)
            ->deleteJson(route('api.cart.destroy', $car))
            ->assertOk()
            ->assertJsonPath('in_cart', false);

        $this->assertDatabaseMissing('car_cart_items', [
            'user_id' => $this->user->id,
            'car_id' => $car->id,
        ]);
    }

    public function test_show_car_includes_cart_and_other_users_count(): void
    {
        $car = Car::factory()->create(['sync_status' => 'active']);
        $other = User::factory()->validated()->create();
        CarCartItem::factory()->create(['user_id' => $other->id, 'car_id' => $car->id]);

        $this->actingAs($this->user)
            ->getJson(route('api.cars.show', $car))
            ->assertOk()
            ->assertJsonPath('data.is_in_cart', false)
            ->assertJsonPath('data.cart_other_users_count', 1);

        CarCartItem::factory()->create(['user_id' => $this->user->id, 'car_id' => $car->id]);

        $this->actingAs($this->user)
            ->getJson(route('api.cars.show', $car))
            ->assertOk()
            ->assertJsonPath('data.is_in_cart', true)
            ->assertJsonPath('data.cart_other_users_count', 1);
    }

    public function test_cart_count_ignores_items_when_car_is_sold(): void
    {
        $car = Car::factory()->create(['sync_status' => 'sold']);
        $other = User::factory()->validated()->create();
        CarCartItem::factory()->create(['user_id' => $other->id, 'car_id' => $car->id]);

        $this->actingAs($this->user)
            ->getJson(route('api.cars.show', $car))
            ->assertOk()
            ->assertJsonPath('data.cart_other_users_count', 0);
    }

    public function test_user_can_list_cart_items(): void
    {
        $car = Car::factory()->create(['sync_status' => 'active']);
        CarCartItem::factory()->create([
            'user_id' => $this->user->id,
            'car_id' => $car->id,
        ]);

        $this->actingAs($this->user)
            ->getJson(route('api.cart.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.car_id', $car->id);
    }

    public function test_user_can_fetch_cart_car_ids(): void
    {
        $car = Car::factory()->create(['sync_status' => 'active']);
        CarCartItem::factory()->create([
            'user_id' => $this->user->id,
            'car_id' => $car->id,
        ]);

        $this->actingAs($this->user)
            ->getJson(route('api.cart.car-ids'))
            ->assertOk()
            ->assertJsonPath('car_ids.0', $car->id);
    }

    public function test_list_cars_includes_cart_users_count(): void
    {
        $car = Car::factory()->create(['sync_status' => 'active']);
        CarCartItem::factory()->create([
            'car_id' => $car->id,
            'user_id' => User::factory()->validated(),
        ]);
        CarCartItem::factory()->create([
            'car_id' => $car->id,
            'user_id' => User::factory()->validated(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index', ['per_page' => 50]))
            ->assertOk();

        $row = collect($response->json('data'))->firstWhere('id', $car->id);
        $this->assertNotNull($row);
        $this->assertSame(2, $row['cart_users_count']);
    }
}
