<?php

namespace Tests\Feature\Admin;

use App\Models\Car;
use App\Models\CarHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarHistoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_guests_cannot_access_car_history(): void
    {
        $this->get(route('admin.cars.history.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_view_car_history_index(): void
    {
        $car = Car::factory()->create(['id' => 50001]);
        CarHistory::query()->create([
            'car_id' => $car->id,
            'status' => 'SOLD',
            'buyer_info' => ['id_client' => 'C100'],
            'created_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.cars.history.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/cars/history/index')
                ->has('histories.data', 1)
                ->where('histories.data.0.status', 'SOLD')
                ->where('histories.data.0.car_id', 50001));
    }

    public function test_admin_can_filter_history_by_car_id(): void
    {
        $carA = Car::factory()->create(['id' => 50002]);
        $carB = Car::factory()->create(['id' => 50003]);

        CarHistory::query()->create([
            'car_id' => $carA->id,
            'status' => 'IMPORTED',
            'created_at' => now(),
        ]);
        CarHistory::query()->create([
            'car_id' => $carB->id,
            'status' => 'SOLD',
            'created_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.cars.history.index', ['car_id' => $carA->id]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('histories.data', 1)
                ->where('histories.data.0.car_id', $carA->id));
    }

    public function test_marketing_page_includes_car_history(): void
    {
        $car = Car::factory()->create(['id' => 50004]);
        CarHistory::query()->create([
            'car_id' => $car->id,
            'status' => 'IMPORTED',
            'created_at' => now()->subDay(),
        ]);
        CarHistory::query()->create([
            'car_id' => $car->id,
            'status' => 'SOLD',
            'buyer_info' => ['id_client' => 'C200'],
            'created_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.cars.marketing', $car))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/cars/marketing')
                ->has('history.data', 2)
                ->where('car.id', 50004));
    }
}
