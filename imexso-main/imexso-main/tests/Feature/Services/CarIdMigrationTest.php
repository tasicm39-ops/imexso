<?php

namespace Tests\Feature\Services;

use App\Models\Car;
use App\Models\CarHistory;
use App\Services\CarIdMigrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarIdMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrates_car_id_and_related_foreign_keys(): void
    {
        $oldId = 900001;
        $xmlId = 196318;

        Car::factory()->create([
            'id' => $oldId,
            'id_produit' => 'N196318',
            'sync_status' => 'active',
        ]);

        CarHistory::query()->create([
            'car_id' => $oldId,
            'status' => 'IMPORTED',
            'buyer_info' => null,
            'created_at' => now(),
        ]);

        $stats = app(CarIdMigrationService::class)->migrateFromMapping([
            'N196318' => $xmlId,
        ]);

        $this->assertEmpty($stats['errors'], implode('; ', $stats['errors']));
        $this->assertSame(1, $stats['mapped']);
        $this->assertSame(1, $stats['migrated']);
        $this->assertSame(0, $stats['conflicts']);
        $this->assertDatabaseMissing('cars', ['id' => $oldId]);
        $this->assertDatabaseHas('cars', ['id' => $xmlId, 'id_produit' => 'N196318']);
        $this->assertDatabaseHas('car_history', ['car_id' => $xmlId, 'status' => 'IMPORTED']);
    }

    public function test_dry_run_does_not_change_database(): void
    {
        Car::factory()->create([
            'id' => 900002,
            'id_produit' => 'N196319',
        ]);

        $stats = app(CarIdMigrationService::class)->migrateFromMapping([
            'N196319' => 196319,
        ], dryRun: true);

        $this->assertSame(1, $stats['migrated']);
        $this->assertDatabaseHas('cars', ['id' => 900002]);
    }

    public function test_merges_legacy_duplicate_when_canonical_xml_id_already_exists(): void
    {
        Car::factory()->create([
            'id' => 196318,
            'id_produit' => 'N196318',
            'sync_status' => 'active',
        ]);

        Car::factory()->create([
            'id' => 1,
            'id_produit' => 'N196318',
            'sync_status' => 'sold',
        ]);

        CarHistory::query()->create([
            'car_id' => 1,
            'status' => 'SOLD',
            'buyer_info' => ['id_client' => 'LEGACY'],
            'created_at' => now(),
        ]);

        $stats = app(CarIdMigrationService::class)->migrateFromMapping([
            'N196318' => 196318,
        ]);

        $this->assertEmpty($stats['errors'], implode('; ', $stats['errors']));
        $this->assertSame(1, $stats['merged']);
        $this->assertSame(0, $stats['conflicts']);
        $this->assertDatabaseMissing('cars', ['id' => 1]);
        $this->assertDatabaseHas('cars', ['id' => 196318, 'id_produit' => 'N196318']);
        $this->assertDatabaseHas('car_history', [
            'car_id' => 196318,
            'status' => 'SOLD',
        ]);

        $history = CarHistory::query()->where('car_id', 196318)->where('status', 'SOLD')->first();
        $this->assertSame('LEGACY', $history->buyer_info['id_client'] ?? null);
    }

    public function test_reports_conflict_when_target_id_is_taken(): void
    {
        Car::factory()->create(['id' => 196320, 'id_produit' => 'N196320']);
        Car::factory()->create(['id' => 900003, 'id_produit' => 'O196320']);

        $stats = app(CarIdMigrationService::class)->migrateFromMapping([
            'O196320' => 196320,
        ]);

        $this->assertSame(1, $stats['conflicts']);
        $this->assertDatabaseHas('cars', ['id' => 900003]);
        $this->assertDatabaseHas('cars', ['id' => 196320]);
    }

    public function test_migrate_from_xml_builds_mapping_from_items(): void
    {
        Car::factory()->create([
            'id' => 900004,
            'id_produit' => 'N196321',
        ]);

        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<data>
<total_items>1</total_items>
<item>
<id>196321</id>
<id_produit>N196321</id_produit>
</item>
</data>
XML;

        $stats = app(CarIdMigrationService::class)->migrateFromXml($xml);

        $this->assertSame(1, $stats['migrated']);
        $this->assertDatabaseHas('cars', ['id' => 196321, 'id_produit' => 'N196321']);
    }

    public function test_infer_xml_id_from_id_produit_suffix(): void
    {
        $service = app(CarIdMigrationService::class);

        $this->assertSame(196318, $service->inferXmlIdFromIdProduit('N196318'));
        $this->assertSame(196318, $service->inferXmlIdFromIdProduit('O196318'));
        $this->assertNull($service->inferXmlIdFromIdProduit('INVALID'));
    }
}
