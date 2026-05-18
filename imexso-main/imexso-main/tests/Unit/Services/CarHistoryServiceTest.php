<?php

namespace Tests\Unit\Services;

use App\Models\Car;
use App\Models\CarHistory;
use App\Models\User;
use App\Services\CarHistoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarHistoryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_sold_updates_buyer_info_on_reimport(): void
    {
        $car = Car::factory()->sold()->create(['id' => 196318]);

        CarHistory::query()->create([
            'car_id' => $car->id,
            'status' => 'SOLD',
            'buyer_info' => ['id_client' => 'CLIENT42'],
            'created_at' => now(),
        ]);

        $service = app(CarHistoryService::class);

        $this->assertSame(
            CarHistoryService::RESULT_UPDATED,
            $service->recordSold($car, ['id_client' => 'CLIENT99', 'nom_client' => 'ACME']),
        );

        $history = CarHistory::query()->where('car_id', $car->id)->where('status', 'SOLD')->first();
        $this->assertNotNull($history);
        $this->assertSame('CLIENT99', $history->buyer_info['id_client'] ?? null);
        $this->assertSame('ACME', $history->buyer_info['nom_client'] ?? null);
        $this->assertDatabaseCount('car_history', 1);
    }

    public function test_record_sold_from_online_order_creates_history(): void
    {
        $car = Car::factory()->create(['sync_status' => 'active']);
        $user = User::factory()->validated()->create([
            'legacy_client_id' => 'C1234',
            'company_name' => 'Test Co',
        ]);

        $result = app(CarHistoryService::class)->recordSoldFromOnlineOrder($car, $user, 'Urgent delivery');

        $this->assertSame(CarHistoryService::RESULT_CREATED, $result);
        $this->assertDatabaseHas('car_history', [
            'car_id' => $car->id,
            'status' => 'SOLD',
        ]);

        $history = CarHistory::query()->where('car_id', $car->id)->first();
        $this->assertSame('online_order', $history->buyer_info['source'] ?? null);
        $this->assertSame('C1234', $history->buyer_info['client_id'] ?? null);
        $this->assertSame('Urgent delivery', $history->buyer_info['notes'] ?? null);
    }
}
