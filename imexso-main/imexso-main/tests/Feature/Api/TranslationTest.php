<?php

namespace Tests\Feature\Api;

use App\Models\Translation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_translations_for_locale(): void
    {
        Translation::factory()->create(['group' => 'ui', 'key' => 'nav.home', 'locale' => 'en', 'value' => 'Home']);
        Translation::factory()->create(['group' => 'ui', 'key' => 'nav.cars', 'locale' => 'en', 'value' => 'Cars']);
        Translation::factory()->create(['group' => 'filters', 'key' => 'filter.make', 'locale' => 'en', 'value' => 'Make']);

        $response = $this->getJson(route('api.translations', ['locale' => 'en']));

        $response->assertOk();

        $data = $response->json();

        $this->assertEquals('Home', $data['ui']['nav.home']);
        $this->assertEquals('Cars', $data['ui']['nav.cars']);
        $this->assertEquals('Make', $data['filters']['filter.make']);
    }

    public function test_translations_are_grouped_by_group_field(): void
    {
        Translation::factory()->create(['group' => 'ui', 'key' => 'nav.home', 'locale' => 'en', 'value' => 'Home']);
        Translation::factory()->create(['group' => 'filters', 'key' => 'filter.make', 'locale' => 'en', 'value' => 'Make']);
        Translation::factory()->create(['group' => 'car', 'key' => 'car.price', 'locale' => 'en', 'value' => 'Price']);

        $response = $this->getJson(route('api.translations', ['locale' => 'en']));

        $response->assertOk();

        $data = $response->json();

        $this->assertArrayHasKey('ui', $data);
        $this->assertArrayHasKey('filters', $data);
        $this->assertArrayHasKey('car', $data);
        $this->assertEquals('Home', $data['ui']['nav.home']);
        $this->assertEquals('Make', $data['filters']['filter.make']);
        $this->assertEquals('Price', $data['car']['car.price']);
    }

    public function test_returns_empty_groups_for_unknown_locale(): void
    {
        Translation::factory()->create(['group' => 'ui', 'key' => 'nav.home', 'locale' => 'en', 'value' => 'Home']);

        $response = $this->getJson(route('api.translations', ['locale' => 'xx']));

        $response->assertOk()
            ->assertExactJson([]);
    }

    public function test_translation_endpoint_is_publicly_accessible(): void
    {
        Translation::factory()->create(['group' => 'ui', 'key' => 'nav.home', 'locale' => 'en', 'value' => 'Home']);

        $response = $this->getJson(route('api.translations', ['locale' => 'en']));

        $response->assertOk();
    }
}
