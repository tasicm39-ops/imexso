<?php

namespace Tests\Feature\Api;

use App\Models\Car;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarListingDedupTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->validated()->create();
    }

    public function test_index_collapses_rows_with_identical_listing_tuple_and_keeps_newest(): void
    {
        $tuple = [
            'make' => 'DEDUPMAKE',
            'model' => 'DEDUPMODEL',
            'trim_level' => 'SHINE',
            'fuel_type' => 'ESSENCE',
            'professional_price' => 25500.50,
            'mileage' => 42000,
            'color' => 'NOIR',
            'sync_status' => 'active',
        ];

        Car::factory()->create(array_merge($tuple, [
            'id_produit' => 'OLD1',
            'created_at' => Carbon::parse('2020-06-01 10:00:00'),
        ]));
        Car::factory()->create(array_merge($tuple, [
            'id_produit' => 'MID2',
            'created_at' => Carbon::parse('2023-01-15 12:00:00'),
        ]));
        $newest = Car::factory()->create(array_merge($tuple, [
            'id_produit' => 'NEW3',
            'created_at' => Carbon::parse('2025-03-20 08:00:00'),
        ]));

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index'));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $newest->id);
    }

    public function test_index_does_not_merge_rows_that_differ_only_by_mileage(): void
    {
        $base = [
            'make' => 'SPLITMAKE',
            'model' => 'SPLITMODEL',
            'trim_level' => 'FEEL',
            'fuel_type' => 'DIESEL',
            'professional_price' => 18999.00,
            'color' => 'BLANC',
            'sync_status' => 'active',
        ];

        Car::factory()->create(array_merge($base, [
            'id_produit' => 'KM_A',
            'mileage' => 10000,
        ]));
        Car::factory()->create(array_merge($base, [
            'id_produit' => 'KM_B',
            'mileage' => 20000,
        ]));

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index'));

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }
}
