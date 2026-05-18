<?php

namespace Tests\Feature\Services;

use App\Models\Car;
use App\Models\CarHistory;
use App\Services\VenduXmlImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VenduXmlImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendu_import_marks_car_as_sold_and_records_history(): void
    {
        $car = Car::factory()->create([
            'id' => 196318,
            'id_produit' => 'N196318',
            'sync_status' => 'active',
            'stock_status' => 'AVAILABLE',
        ]);

        $stats = app(VenduXmlImportService::class)->import($this->buildVenduXml([
            [
                'id' => 196318,
                'id_produit' => 'N196318',
                'id_client' => 'CLIENT42',
            ],
        ]));

        $this->assertSame(1, $stats['processed']);
        $this->assertSame(1, $stats['updated']);
        $this->assertSame(1, $stats['history_created']);
        $this->assertSame(0, $stats['skipped']);

        $car->refresh();
        $this->assertSame('sold', $car->sync_status);
        $this->assertSame('SOLD', $car->stock_status);

        $this->assertDatabaseHas('car_history', [
            'car_id' => $car->id,
            'status' => 'SOLD',
        ]);

        $history = CarHistory::query()->where('car_id', $car->id)->first();
        $this->assertNotNull($history);
        $this->assertSame('CLIENT42', $history->buyer_info['id_client'] ?? null);
    }

    public function test_vendu_import_updates_buyer_info_when_sold_history_already_exists(): void
    {
        Car::factory()->sold()->create([
            'id' => 196318,
            'id_produit' => 'N196318',
        ]);

        CarHistory::query()->create([
            'car_id' => 196318,
            'status' => 'SOLD',
            'buyer_info' => ['id_client' => 'CLIENT42'],
            'created_at' => now(),
        ]);

        $stats = app(VenduXmlImportService::class)->import($this->buildVenduXml([
            ['id' => 196318, 'id_produit' => 'N196318', 'id_client' => 'CLIENT99'],
        ]));

        $this->assertSame(1, $stats['processed']);
        $this->assertSame(0, $stats['updated']);
        $this->assertSame(0, $stats['history_created']);
        $this->assertSame(1, $stats['history_updated']);
        $this->assertSame(0, $stats['skipped']);
        $this->assertDatabaseCount('car_history', 1);

        $history = CarHistory::query()->where('car_id', 196318)->first();
        $this->assertSame('CLIENT99', $history->buyer_info['id_client'] ?? null);
    }

    public function test_vendu_import_creates_missing_car_when_enabled(): void
    {
        $stats = app(VenduXmlImportService::class)->import($this->buildVenduXml([
            [
                'id' => 888777,
                'id_produit' => 'N888777',
                'id_client' => 'ARCHIVE1',
            ],
        ]), createMissing: true);

        $this->assertSame(1, $stats['processed']);
        $this->assertSame(1, $stats['cars_created']);
        $this->assertSame(0, $stats['missing_cars']);
        $this->assertSame(1, $stats['history_created']);

        $this->assertDatabaseHas('cars', [
            'id' => 888777,
            'id_produit' => 'N888777',
            'stock_status' => 'SOLD',
            'sync_status' => 'sold',
        ]);

        $this->assertDatabaseHas('car_history', [
            'car_id' => 888777,
            'status' => 'IMPORTED',
        ]);
        $this->assertDatabaseHas('car_history', [
            'car_id' => 888777,
            'status' => 'SOLD',
        ]);
    }

    public function test_vendu_import_does_not_create_missing_car_when_disabled(): void
    {
        $stats = app(VenduXmlImportService::class)->import($this->buildVenduXml([
            ['id' => 888778, 'id_produit' => 'N888778'],
        ]), createMissing: false);

        $this->assertSame(1, $stats['missing_cars']);
        $this->assertSame(0, $stats['cars_created']);
        $this->assertDatabaseMissing('cars', ['id' => 888778]);
    }

    public function test_vendu_import_resolves_car_by_id_produit_when_needed(): void
    {
        Car::factory()->create([
            'id' => 196318,
            'id_produit' => 'O196318',
            'sync_status' => 'active',
            'stock_status' => 'AVAILABLE',
        ]);

        app(VenduXmlImportService::class)->import($this->buildVenduXml([
            ['id' => 0, 'id_produit' => 'O196318', 'id_client' => 'CLIENT42'],
        ]));

        $this->assertDatabaseHas('cars', [
            'id' => 196318,
            'stock_status' => 'SOLD',
        ]);
    }

    /**
     * @param  list<array{id: int, id_produit: string, id_client?: string}>  $items
     */
    private function buildVenduXml(array $items): string
    {
        $totalItems = count($items);
        $xmlItems = '';
        foreach ($items as $item) {
            $client = $item['id_client'] ?? '';
            $xmlItems .= <<<XML
<item>
<id>{$item['id']}</id>
<id_produit>{$item['id_produit']}</id_produit>
<id_client>{$client}</id_client>
</item>
XML;
        }

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<data>
<time>18/05/2026 10:00:00</time>
<total_items>{$totalItems}</total_items>
{$xmlItems}
</data>
XML;
    }
}
