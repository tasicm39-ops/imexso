<?php

namespace Tests\Feature\Api;

use App\Services\VatValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class VatValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_vat_validation_returns_valid_result()
    {
        $this->mock(VatValidationService::class, function (MockInterface $mock) {
            $mock->shouldReceive('validate')
                ->with('BE', '0827449689')
                ->andReturn([
                    'valid' => true,
                    'company_name' => 'SRL DATASPILOT',
                    'address' => 'Rue Faignart 20',
                    'postal_code' => '7100',
                    'city' => 'La Louviere',
                    'country_code' => 'BE',
                    'vat_number' => '0827449689',
                ]);
        });

        $response = $this->postJson(route('api.vat.validate'), [
            'country_code' => 'BE',
            'vat_number' => '0827449689',
        ]);

        $response->assertOk();
        $response->assertJson([
            'valid' => true,
            'company_name' => 'SRL DATASPILOT',
            'country_code' => 'BE',
        ]);
    }

    public function test_vat_validation_returns_invalid_result()
    {
        $this->mock(VatValidationService::class, function (MockInterface $mock) {
            $mock->shouldReceive('validate')
                ->andReturn([
                    'valid' => false,
                    'company_name' => null,
                    'address' => null,
                    'postal_code' => null,
                    'city' => null,
                    'country_code' => 'BE',
                    'vat_number' => null,
                ]);
        });

        $response = $this->postJson(route('api.vat.validate'), [
            'country_code' => 'BE',
            'vat_number' => '0000000000',
        ]);

        $response->assertOk();
        $response->assertJson(['valid' => false]);
    }

    public function test_vat_validation_requires_country_code()
    {
        $response = $this->postJson(route('api.vat.validate'), [
            'vat_number' => '0827449689',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('country_code');
    }

    public function test_vat_validation_requires_vat_number()
    {
        $response = $this->postJson(route('api.vat.validate'), [
            'country_code' => 'BE',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('vat_number');
    }

    public function test_vat_validation_rejects_invalid_country_code()
    {
        $response = $this->postJson(route('api.vat.validate'), [
            'country_code' => 'XX',
            'vat_number' => '0827449689',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('country_code');
    }

    public function test_vat_validation_rejects_special_characters_in_vat_number()
    {
        $response = $this->postJson(route('api.vat.validate'), [
            'country_code' => 'BE',
            'vat_number' => '082-744.968',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('vat_number');
    }
}
