<?php

namespace Tests\Feature\Admin;

use App\Models\Car;
use App\Models\Offer;
use App\Models\SegmentEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarStatsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_cars_index_includes_offer_and_view_stats(): void
    {
        $car = Car::factory()->create();

        Offer::factory()->create([
            'car_id' => $car->id,
            'created_at' => now(),
        ]);

        SegmentEvent::factory()->create([
            'event_type' => 'view_car',
            'payload' => ['car_id' => $car->id],
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.cars.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/cars/index')
                ->has('offerStats')
                ->has('viewStats')
            );
    }

    public function test_stats_contain_five_day_entries_per_car(): void
    {
        $car = Car::factory()->create();

        Offer::factory()->create([
            'car_id' => $car->id,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.cars.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has("offerStats.{$car->id}", 5)
                ->has("viewStats.{$car->id}", 5)
            );
    }

    public function test_stats_are_empty_when_no_cars(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.cars.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('offerStats', [])
                ->where('viewStats', [])
            );
    }

    public function test_old_offers_outside_five_days_are_excluded(): void
    {
        $car = Car::factory()->create();

        Offer::factory()->create([
            'car_id' => $car->id,
            'created_at' => now()->subDays(10),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.cars.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has("offerStats.{$car->id}", 5, fn ($day) => $day
                    ->where('count', 0)
                    ->etc()
                )
            );
    }
}
