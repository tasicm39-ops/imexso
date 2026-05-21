<?php

namespace Tests\Feature\Api;

use App\Models\Car;
use App\Models\SaleHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->validated()->create();
    }

    public function test_guests_cannot_list_sale_histories(): void
    {
        $this->getJson(route('api.sale-histories.index'))
            ->assertUnauthorized();
    }

    public function test_guests_cannot_view_a_sale_history(): void
    {
        $history = SaleHistory::factory()->create();

        $this->getJson(route('api.sale-histories.show', $history))
            ->assertUnauthorized();
    }

    public function test_list_sale_histories_returns_paginated_results(): void
    {
        SaleHistory::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson(route('api.sale-histories.index'));

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['meta' => ['current_page', 'last_page', 'per_page', 'total']]);
    }

    public function test_list_sale_histories_can_filter_by_client_id(): void
    {
        SaleHistory::factory()->count(2)->create(['client_id' => 'C1234']);
        SaleHistory::factory()->create(['client_id' => 'C5678']);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.sale-histories.index', ['client_id' => 'C1234']));

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_list_sale_histories_can_filter_by_id_produit(): void
    {
        SaleHistory::factory()->create(['id_produit' => 'O123456']);
        SaleHistory::factory()->create(['id_produit' => 'N789012']);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.sale-histories.index', ['id_produit' => 'O123456']));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id_produit', 'O123456');
    }

    public function test_list_sale_histories_can_filter_by_status(): void
    {
        SaleHistory::factory()->count(2)->create(['status' => 'LIVRE']);
        SaleHistory::factory()->create(['status' => 'ANNULE']);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.sale-histories.index', ['status' => 'LIVRE']));

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_show_sale_history_returns_all_fields(): void
    {
        $history = SaleHistory::factory()->create([
            'id_produit' => 'O138454',
            'make' => 'PEUGEOT',
            'model' => '308',
            'client_id' => 'C1412',
            'status' => 'LIVRE',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.sale-histories.show', $history));

        $response->assertOk()
            ->assertJsonPath('data.id_produit', 'O138454')
            ->assertJsonPath('data.make', 'PEUGEOT')
            ->assertJsonPath('data.model', '308')
            ->assertJsonPath('data.client_id', 'C1412')
            ->assertJsonPath('data.status', 'LIVRE');
    }

    public function test_show_sale_history_never_exposes_vin_in_api(): void
    {
        $history = SaleHistory::factory()->create(['vin' => 'SECRETVIN987654321']);

        $this->actingAs($this->user)
            ->getJson(route('api.sale-histories.show', $history))
            ->assertOk()
            ->assertJsonPath('data.vin', null);
    }

    public function test_show_sale_history_returns_404_for_nonexistent(): void
    {
        $this->actingAs($this->user)
            ->getJson(route('api.sale-histories.show', 99999))
            ->assertNotFound();
    }

    public function test_list_sale_histories_scoped_to_authenticated_client_when_legacy_id_set(): void
    {
        $user = User::factory()->validated()->create(['legacy_client_id' => 'C1234']);

        SaleHistory::factory()->count(2)->create(['client_id' => 'C1234']);
        SaleHistory::factory()->create(['client_id' => 'C9999']);

        $response = $this->actingAs($user)
            ->getJson(route('api.sale-histories.index'));

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_sale_history_includes_retention_date_from_matching_car(): void
    {
        Car::factory()->create([
            'id' => 100001,
            'id_produit' => 'O138454',
            'retention_date' => '2026-06-01',
        ]);

        $history = SaleHistory::factory()->create([
            'id_produit' => 'O138454',
            'client_id' => 'C1412',
        ]);

        $this->actingAs($this->user)
            ->getJson(route('api.sale-histories.show', $history))
            ->assertOk()
            ->assertJsonPath('data.retention_date', '2026-06-01')
            ->assertJsonMissingPath('data.radio_code');
    }
}
