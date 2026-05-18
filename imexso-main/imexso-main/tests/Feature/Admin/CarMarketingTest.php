<?php

namespace Tests\Feature\Admin;

use App\Models\Car;
use App\Models\CarMarketing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarMarketingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_guests_cannot_access_marketing_page(): void
    {
        $car = Car::factory()->create();

        $this->get(route('admin.cars.marketing', $car))
            ->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_marketing_page(): void
    {
        $user = User::factory()->validated()->create();
        $car = Car::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.cars.marketing', $car))
            ->assertForbidden();
    }

    public function test_admin_can_view_marketing_page(): void
    {
        $car = Car::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.cars.marketing', $car));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/cars/marketing')
                ->has('car')
                ->has('marketing')
            );
    }

    public function test_admin_can_create_marketing_settings(): void
    {
        $car = Car::factory()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.cars.marketing.update', $car), $this->validMarketingPayload([
                'limited_stock_enabled' => true,
                'limited_stock_count' => 5,
                'new_price_enabled' => false,
                'new_price_amount' => null,
                'promotion_enabled' => true,
                'promotion_label' => 'Big Sale!',
                'badge_text' => null,
                'is_active' => true,
            ]));

        $response->assertRedirect(route('admin.cars.marketing', $car))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('car_marketings', [
            'car_id' => $car->id,
            'limited_stock_enabled' => true,
            'limited_stock_count' => 5,
            'promotion_enabled' => true,
            'promotion_label' => 'Big Sale!',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_existing_marketing_settings(): void
    {
        $car = Car::factory()->create();
        CarMarketing::factory()->create([
            'car_id' => $car->id,
            'limited_stock_enabled' => false,
            'promotion_enabled' => false,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.cars.marketing.update', $car), $this->validMarketingPayload([
                'limited_stock_enabled' => true,
                'limited_stock_count' => 3,
                'new_price_enabled' => true,
                'new_price_amount' => 15000,
                'promotion_enabled' => false,
                'promotion_label' => null,
                'badge_text' => 'Best Deal',
                'is_active' => true,
            ]));

        $this->assertDatabaseCount('car_marketings', 1);
        $this->assertDatabaseHas('car_marketings', [
            'car_id' => $car->id,
            'limited_stock_enabled' => true,
            'limited_stock_count' => 3,
            'new_price_enabled' => true,
            'badge_text' => 'Best Deal',
        ]);
    }

    public function test_marketing_validation_rejects_invalid_data(): void
    {
        $car = Car::factory()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.cars.marketing.update', $car), $this->validMarketingPayload([
                'limited_stock_enabled' => 'not-boolean',
            ]));

        $response->assertSessionHasErrors('limited_stock_enabled');
    }

    public function test_car_list_shows_marketing_status(): void
    {
        $car = Car::factory()->create();
        CarMarketing::factory()->create([
            'car_id' => $car->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.cars.index'));

        $response->assertOk();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validMarketingPayload(array $overrides = []): array
    {
        return array_merge([
            'limited_stock_enabled' => false,
            'limited_stock_count' => null,
            'new_price_enabled' => false,
            'new_price_amount' => null,
            'promotion_enabled' => false,
            'promotion_label' => null,
            'badge_text' => null,
            'sold_enabled' => false,
            'sold_visible_days' => 5,
            'is_active' => true,
        ], $overrides);
    }
}
