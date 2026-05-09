<?php

namespace Tests\Feature\Api;

use App\Models\Car;
use App\Models\CarCartItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_place_order(): void
    {
        $this->postJson(route('api.orders.store'), ['car_ids' => [1]])
            ->assertUnauthorized();
    }

    public function test_user_without_client_id_cannot_place_order(): void
    {
        $user = User::factory()->validated()->create([
            'legacy_client_id' => null,
        ]);

        $car = Car::factory()->create(['sync_status' => 'active']);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.orders.store'), ['car_ids' => [$car->id]])
            ->assertUnprocessable()
            ->assertJsonPath('requires_client_id', true);

        $this->assertDatabaseMissing('orders', ['user_id' => $user->id]);
        $this->assertDatabaseHas('cars', ['id' => $car->id, 'sync_status' => 'active']);
    }

    public function test_user_with_client_id_can_place_order(): void
    {
        $user = User::factory()->validated()->create([
            'legacy_client_id' => 'C1234',
        ]);

        $car = Car::factory()->create([
            'sync_status' => 'active',
            'id_produit' => 'REF-001',
        ]);

        CarCartItem::factory()->create([
            'user_id' => $user->id,
            'car_id' => $car->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.orders.store'), ['car_ids' => [$car->id]]);

        $response->assertCreated()
            ->assertJsonPath('order_count', 1)
            ->assertJsonPath('references.0', 'REF-001');

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'car_id' => $car->id,
            'client_id' => 'C1234',
            'car_reference' => 'REF-001',
            'status' => 'confirmed',
        ]);

        $this->assertDatabaseHas('cars', [
            'id' => $car->id,
            'sync_status' => 'sold',
        ]);

        $this->assertDatabaseMissing('car_cart_items', [
            'user_id' => $user->id,
            'car_id' => $car->id,
        ]);
    }

    public function test_cannot_order_already_sold_car(): void
    {
        $user = User::factory()->validated()->create([
            'legacy_client_id' => 'C5678',
        ]);

        $car = Car::factory()->create([
            'sync_status' => 'sold',
            'id_produit' => 'SOLD-001',
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.orders.store'), ['car_ids' => [$car->id]])
            ->assertUnprocessable()
            ->assertJsonFragment(['unavailable_references' => ['SOLD-001']]);

        $this->assertDatabaseMissing('orders', ['user_id' => $user->id]);
    }

    public function test_order_with_multiple_cars(): void
    {
        $user = User::factory()->validated()->create([
            'legacy_client_id' => 'C9999',
        ]);

        $car1 = Car::factory()->create(['sync_status' => 'active', 'id_produit' => 'MULTI-001']);
        $car2 = Car::factory()->create(['sync_status' => 'active', 'id_produit' => 'MULTI-002']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.orders.store'), ['car_ids' => [$car1->id, $car2->id]]);

        $response->assertCreated()
            ->assertJsonPath('order_count', 2);

        $this->assertDatabaseCount('orders', 2);
        $this->assertDatabaseHas('cars', ['id' => $car1->id, 'sync_status' => 'sold']);
        $this->assertDatabaseHas('cars', ['id' => $car2->id, 'sync_status' => 'sold']);
    }

    public function test_order_requires_car_ids(): void
    {
        $user = User::factory()->validated()->create([
            'legacy_client_id' => 'C1111',
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.orders.store'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('car_ids');
    }

    public function test_user_can_list_their_orders(): void
    {
        $user = User::factory()->validated()->create([
            'legacy_client_id' => 'C2222',
        ]);

        $car = Car::factory()->create(['sync_status' => 'sold']);

        Order::factory()->create([
            'user_id' => $user->id,
            'car_id' => $car->id,
            'client_id' => 'C2222',
            'status' => 'confirmed',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.orders.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
