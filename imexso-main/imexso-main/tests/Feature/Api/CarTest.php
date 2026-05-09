<?php

namespace Tests\Feature\Api;

use App\Models\Car;
use App\Models\CarOption;
use App\Models\CarPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->validated()->create();
    }

    public function test_guests_cannot_list_cars(): void
    {
        $this->getJson(route('api.cars.index'))
            ->assertUnauthorized();
    }

    public function test_guests_cannot_view_a_car(): void
    {
        $car = Car::factory()->create();

        $this->getJson(route('api.cars.show', $car))
            ->assertUnauthorized();
    }

    public function test_list_cars_returns_only_active_by_default(): void
    {
        Car::factory()->count(3)->create(['sync_status' => 'active']);
        Car::factory()->count(2)->create(['sync_status' => 'sold']);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index'));

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_list_cars_can_filter_by_sync_status(): void
    {
        Car::factory()->count(3)->create(['sync_status' => 'active']);
        Car::factory()->count(2)->create(['sync_status' => 'sold']);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index', ['sync_status' => 'sold']));

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_list_cars_can_filter_by_make(): void
    {
        Car::factory()->count(2)->create(['make' => 'CITROEN']);
        Car::factory()->create(['make' => 'PEUGEOT']);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index', ['make' => 'CITROEN']));

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_list_cars_can_filter_by_fuel_type(): void
    {
        Car::factory()->count(2)->create(['fuel_type' => 'DIESEL']);
        Car::factory()->create(['fuel_type' => 'ESSENCE']);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index', ['fuel_type' => 'DIESEL']));

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_list_cars_can_filter_by_condition_type(): void
    {
        Car::factory()->count(2)->create(['condition_type' => 'VN']);
        Car::factory()->create(['condition_type' => 'VO']);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index', ['condition_type' => 'VN']));

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_list_cars_can_filter_by_user_type(): void
    {
        Car::factory()->count(2)->create(['user_type' => 'BE']);
        Car::factory()->create(['user_type' => 'INT']);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index', ['user_type' => 'BE']));

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_list_cars_can_filter_by_category(): void
    {
        Car::factory()->count(2)->create(['category' => 'SUV 4x2']);
        Car::factory()->create(['category' => 'Berline']);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index', ['category' => 'SUV 4x2']));

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_list_cars_is_paginated(): void
    {
        Car::factory()->count(20)->create();

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index', ['per_page' => 5]));

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure(['meta' => ['current_page', 'last_page', 'per_page', 'total']]);
    }

    public function test_show_car_returns_car_with_photos_and_options(): void
    {
        $car = Car::factory()->create();
        CarPhoto::factory()->count(3)->create(['car_id' => $car->id]);
        CarOption::factory()->count(2)->create(['car_id' => $car->id]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.show', $car));

        $response->assertOk()
            ->assertJsonPath('data.id_produit', $car->id_produit)
            ->assertJsonCount(3, 'data.photos')
            ->assertJsonCount(2, 'data.options');
    }

    public function test_show_car_never_exposes_vin_in_api(): void
    {
        $car = Car::factory()->create(['vin' => 'CONFIDENTIALVIN12345']);

        $this->actingAs($this->user)
            ->getJson(route('api.cars.show', $car))
            ->assertOk()
            ->assertJsonPath('data.vin', null);
    }

    public function test_show_car_returns_all_expected_fields(): void
    {
        $car = Car::factory()->create([
            'make' => 'CITROEN',
            'model' => 'C5 X',
            'fuel_type' => 'ESSENCE',
            'professional_price' => 20100.00,
            'is_clearance' => true,
            'status' => ['fr' => 'En stock', 'de' => 'Auf Lager', 'en' => 'On Stock', 'nl' => 'Stock'],
            'excluded_countries' => ['Belgium', 'Luxembourg'],
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.show', $car));

        $response->assertOk()
            ->assertJsonPath('data.make', 'CITROEN')
            ->assertJsonPath('data.model', 'C5 X')
            ->assertJsonPath('data.fuel_type', 'ESSENCE')
            ->assertJsonPath('data.professional_price', '20100.00')
            ->assertJsonPath('data.is_clearance', true)
            ->assertJsonPath('data.status.fr', 'En stock')
            ->assertJsonPath('data.status.en', 'On Stock')
            ->assertJsonPath('data.excluded_countries', ['Belgium', 'Luxembourg']);
    }

    public function test_show_car_returns_404_for_nonexistent_car(): void
    {
        $this->actingAs($this->user)
            ->getJson(route('api.cars.show', 99999))
            ->assertNotFound();
    }

    public function test_list_cars_includes_photos_and_options(): void
    {
        $car = Car::factory()->create();
        CarPhoto::factory()->create(['car_id' => $car->id]);
        CarOption::factory()->create(['car_id' => $car->id]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'id_produit', 'make', 'model', 'photos', 'options'],
                ],
            ]);
    }

    public function test_list_cars_can_filter_by_model(): void
    {
        Car::factory()->count(2)->create(['model' => 'C5 X']);
        Car::factory()->create(['model' => '308']);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index', ['model' => 'C5 X']));

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_list_cars_can_filter_by_gearbox(): void
    {
        Car::factory()->count(2)->create(['gearbox' => 'EAT8']);
        Car::factory()->create(['gearbox' => 'PDK']);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index', ['gearbox' => 'EAT8']));

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_list_cars_can_filter_by_color(): void
    {
        Car::factory()->count(2)->create(['color' => 'BLEU', 'color_code' => null]);
        Car::factory()->create(['color' => 'ROUGE', 'color_code' => null]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index', ['color' => 'BLEU']));

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_list_cars_can_filter_by_color_code(): void
    {
        Car::factory()->count(2)->create(['color' => 'BLEU ECLIPSE', 'color_code' => '4']);
        Car::factory()->create(['color' => 'ROUGE', 'color_code' => '5']);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index', ['color' => '4']));

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_list_cars_can_filter_by_horsepower_range(): void
    {
        Car::factory()->create(['horsepower' => 100]);
        Car::factory()->create(['horsepower' => 200]);
        Car::factory()->create(['horsepower' => 300]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index', ['horsepower_min' => 150, 'horsepower_max' => 250]));

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_list_cars_can_filter_by_price_range(): void
    {
        Car::factory()->create(['professional_price' => 10000]);
        Car::factory()->create(['professional_price' => 25000]);
        Car::factory()->create(['professional_price' => 50000]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index', ['price_min' => 20000, 'price_max' => 30000]));

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_list_cars_can_filter_by_mileage_range(): void
    {
        Car::factory()->create(['mileage' => 5000]);
        Car::factory()->create(['mileage' => 50000]);
        Car::factory()->create(['mileage' => 150000]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index', ['mileage_min' => 10000, 'mileage_max' => 100000]));

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_list_cars_can_filter_by_year_range(): void
    {
        Car::factory()->create(['manufacturing_year' => 2018]);
        Car::factory()->create(['manufacturing_year' => 2022]);
        Car::factory()->create(['manufacturing_year' => 2024]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index', ['year_min' => 2020, 'year_max' => 2023]));

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_list_cars_can_search_by_text(): void
    {
        Car::factory()->create(['make' => 'CITROEN', 'model' => 'C5 X']);
        Car::factory()->create(['make' => 'PEUGEOT', 'model' => '308']);
        Car::factory()->create(['make' => 'CITROEN', 'model' => 'C3']);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index', ['search' => 'CITROEN']));

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_list_cars_can_sort_by_price_ascending(): void
    {
        Car::factory()->create(['professional_price' => 30000]);
        Car::factory()->create(['professional_price' => 10000]);
        Car::factory()->create(['professional_price' => 20000]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index', ['sort' => 'price_asc']));

        $response->assertOk();
        $prices = collect($response->json('data'))->pluck('professional_price')->toArray();
        $this->assertEquals(['10000.00', '20000.00', '30000.00'], $prices);
    }

    public function test_list_cars_can_sort_by_price_descending(): void
    {
        Car::factory()->create(['professional_price' => 30000]);
        Car::factory()->create(['professional_price' => 10000]);
        Car::factory()->create(['professional_price' => 20000]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index', ['sort' => 'price_desc']));

        $response->assertOk();
        $prices = collect($response->json('data'))->pluck('professional_price')->toArray();
        $this->assertEquals(['30000.00', '20000.00', '10000.00'], $prices);
    }

    public function test_filters_endpoint_returns_available_values(): void
    {
        Car::factory()->create(['make' => 'CITROEN', 'fuel_type' => 'ESSENCE', 'horsepower' => 130, 'professional_price' => 20000]);
        Car::factory()->create(['make' => 'PEUGEOT', 'fuel_type' => 'DIESEL', 'horsepower' => 200, 'professional_price' => 35000]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.filters'));

        $response->assertOk()
            ->assertJsonStructure([
                'makes',
                'models',
                'fuel_types',
                'gearboxes',
                'colors',
                'categories',
                'condition_types',
                'ranges' => [
                    'horsepower' => ['min', 'max'],
                    'price' => ['min', 'max'],
                    'mileage' => ['min', 'max'],
                    'year' => ['min', 'max'],
                ],
            ]);

        $this->assertContains('CITROEN', $response->json('makes'));
        $this->assertContains('PEUGEOT', $response->json('makes'));
    }

    public function test_country_exclusion_hides_excluded_cars_for_user(): void
    {
        $user = User::factory()->validated()->create(['country' => 'Belgium']);

        Car::factory()->create([
            'id_produit' => 'VISIBLE',
            'excluded_countries' => ['France'],
        ]);
        Car::factory()->create([
            'id_produit' => 'HIDDEN',
            'excluded_countries' => ['Belgium', 'Luxembourg'],
        ]);
        Car::factory()->create([
            'id_produit' => 'NO_EXCLUSION',
            'excluded_countries' => null,
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('api.cars.index'));

        $response->assertOk()
            ->assertJsonCount(2, 'data');

        $produits = collect($response->json('data'))->pluck('id_produit')->toArray();
        $this->assertContains('VISIBLE', $produits);
        $this->assertContains('NO_EXCLUSION', $produits);
        $this->assertNotContains('HIDDEN', $produits);
    }

    public function test_country_exclusion_shows_all_cars_when_no_country(): void
    {
        $user = User::factory()->validated()->create(['country' => null]);

        Car::factory()->create(['excluded_countries' => ['Belgium']]);
        Car::factory()->create(['excluded_countries' => null]);

        $response = $this->actingAs($user)
            ->getJson(route('api.cars.index'));

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_country_parameter_overrides_user_country(): void
    {
        $user = User::factory()->validated()->create(['country' => 'France']);

        Car::factory()->create([
            'id_produit' => 'EXCL_BELGIUM',
            'excluded_countries' => ['Belgium'],
        ]);
        Car::factory()->create([
            'id_produit' => 'EXCL_FRANCE',
            'excluded_countries' => ['France'],
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('api.cars.index', ['country' => 'Belgium']));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id_produit', 'EXCL_FRANCE');
    }

    public function test_country_exclusion_with_empty_array(): void
    {
        $user = User::factory()->validated()->create(['country' => 'Belgium']);

        Car::factory()->create([
            'id_produit' => 'EMPTY_ARRAY',
            'excluded_countries' => [],
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('api.cars.index'));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id_produit', 'EMPTY_ARRAY');
    }
}
