<?php

namespace Tests\Feature\Api;

use App\Models\Car;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarFilterGroupingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->validated()->create();
    }

    public function test_filters_endpoint_returns_transmission_groups(): void
    {
        Car::factory()->create(['gearbox' => '5v']);
        Car::factory()->create(['gearbox' => '6v']);
        Car::factory()->create(['gearbox' => 'EAT8']);
        Car::factory()->create(['gearbox' => 'PDK']);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.filters'));

        $response->assertOk()
            ->assertJsonStructure(['transmission_groups']);

        $groups = $response->json('transmission_groups');
        $this->assertArrayHasKey('Manual', $groups);
        $this->assertArrayHasKey('Automatic', $groups);
        $this->assertContains('5v', $groups['Manual']);
        $this->assertContains('6v', $groups['Manual']);
        $this->assertContains('EAT8', $groups['Automatic']);
        $this->assertContains('PDK', $groups['Automatic']);
    }

    public function test_filters_endpoint_returns_color_groups(): void
    {
        Car::factory()->create(['color' => 'BLANC KAOLIN', 'color_code' => '1']);
        Car::factory()->create(['color' => 'BLANC NACRE', 'color_code' => '1']);
        Car::factory()->create(['color' => 'BLEU ECLIPSE', 'color_code' => '4']);
        Car::factory()->create(['color' => 'NOIR OBSIDIEN', 'color_code' => '2']);
        Car::factory()->create(['color' => 'GRIS ARTENSE', 'color_code' => '3']);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.filters'));

        $response->assertOk()
            ->assertJsonStructure([
                'color_groups' => [
                    '*' => ['key', 'codes', 'legacy_colors', 'hex'],
                ],
            ]);

        $groups = $response->json('color_groups');
        $byKey = collect($groups)->keyBy('key');

        $this->assertArrayHasKey('white', $byKey);
        $this->assertArrayHasKey('blue', $byKey);
        $this->assertArrayHasKey('black', $byKey);
        $this->assertArrayHasKey('grey', $byKey);
        $this->assertContains('1', $byKey['white']['codes']);
        $this->assertContains('4', $byKey['blue']['codes']);
        $this->assertContains('2', $byKey['black']['codes']);
        $this->assertContains('3', $byKey['grey']['codes']);
    }

    public function test_list_cars_can_filter_by_comma_separated_gearboxes(): void
    {
        Car::factory()->create(['gearbox' => '5v']);
        Car::factory()->create(['gearbox' => '6v']);
        Car::factory()->create(['gearbox' => 'EAT8']);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index', ['gearbox' => '5v,6v']));

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_list_cars_can_filter_by_comma_separated_colors(): void
    {
        Car::factory()->create(['color' => 'BLANC KAOLIN', 'color_code' => '1']);
        Car::factory()->create(['color' => 'BLANC NACRE', 'color_code' => '1']);
        Car::factory()->create(['color' => 'BLEU ECLIPSE', 'color_code' => '4']);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index', ['color' => '1']));

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_list_cars_single_gearbox_filter_still_works(): void
    {
        Car::factory()->count(2)->create(['gearbox' => 'EAT8']);
        Car::factory()->create(['gearbox' => 'PDK']);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index', ['gearbox' => 'EAT8']));

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_list_cars_single_color_filter_still_works(): void
    {
        Car::factory()->count(2)->create(['color' => 'BLEU', 'color_code' => '4']);
        Car::factory()->create(['color' => 'ROUGE', 'color_code' => '5']);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.index', ['color' => '4']));

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_transmission_groups_with_only_automatic(): void
    {
        Car::factory()->create(['gearbox' => 'EAT8']);
        Car::factory()->create(['gearbox' => 'PDK']);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.filters'));

        $groups = $response->json('transmission_groups');
        $this->assertArrayNotHasKey('Manual', $groups);
        $this->assertArrayHasKey('Automatic', $groups);
    }

    public function test_color_groups_puts_unknown_colors_in_other(): void
    {
        Car::factory()->create(['color' => 'ECOTRONIC GREY', 'color_code' => null]);
        Car::factory()->create(['color' => 'GRIGIO VESUVIO', 'color_code' => null]);
        Car::factory()->create(['color' => 'TITANIUM FLASH', 'color_code' => null]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.cars.filters'));

        $groups = $response->json('color_groups');
        $byKey = collect($groups)->keyBy('key');

        $this->assertArrayHasKey('grey', $byKey);
        $this->assertContains('ECOTRONIC GREY', $byKey['grey']['legacy_colors']);
        $this->assertContains('GRIGIO VESUVIO', $byKey['grey']['legacy_colors']);
        $this->assertArrayHasKey('other', $byKey);
        $this->assertContains('TITANIUM FLASH', $byKey['other']['legacy_colors']);
    }
}
