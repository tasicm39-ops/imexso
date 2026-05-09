<?php

namespace Tests\Feature\Middleware;

use App\Models\Car;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureUserIsValidatedTest extends TestCase
{
    use RefreshDatabase;

    public function test_validated_user_can_access_car_routes(): void
    {
        $user = User::factory()->validated()->create();
        Car::factory()->create();

        $this->actingAs($user)
            ->getJson(route('api.cars.index'))
            ->assertOk();
    }

    public function test_unvalidated_user_cannot_access_car_routes(): void
    {
        $user = User::factory()->create(['is_validated' => false]);

        $this->actingAs($user)
            ->getJson(route('api.cars.index'))
            ->assertForbidden()
            ->assertJsonPath('message', 'Your account is pending approval. Please wait for an administrator to validate your account.');
    }

    public function test_unvalidated_user_cannot_view_single_car(): void
    {
        $user = User::factory()->create(['is_validated' => false]);
        $car = Car::factory()->create();

        $this->actingAs($user)
            ->getJson(route('api.cars.show', $car))
            ->assertForbidden();
    }

    public function test_unvalidated_user_cannot_access_sale_histories(): void
    {
        $user = User::factory()->create(['is_validated' => false]);

        $this->actingAs($user)
            ->getJson(route('api.sale-histories.index'))
            ->assertForbidden();
    }

    public function test_guest_still_gets_unauthorized_not_forbidden(): void
    {
        $this->getJson(route('api.cars.index'))
            ->assertUnauthorized();
    }
}
