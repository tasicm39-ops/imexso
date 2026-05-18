<?php

namespace App\Services;

use App\Models\Car;
use App\Models\CarHistory;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

class CarIdMigrationService
{
    /**
     * Tables that reference cars.id via car_id.
     *
     * @var list<string>
     */
    private const CAR_ID_TABLES = [
        'car_photos',
        'car_options',
        'car_marketings',
        'favourites',
        'offers',
        'orders',
        'car_cart_items',
        'car_history',
    ];

    public function __construct(
        private readonly CarXmlImportService $carXmlImportService,
        private readonly CarHistoryService $carHistoryService,
    ) {}

    /**
     * @return array{
     *     mapped: int,
     *     migrated: int,
     *     merged: int,
     *     skipped: int,
     *     conflicts: int,
     *     errors: list<string>
     * }
     */
    public function migrateFromXml(string $xmlContent, bool $dryRun = false): array
    {
        $xml = $this->carXmlImportService->parseXml($xmlContent);
        $items = $this->carXmlImportService->extractItems($xml);

        $idProduitToXmlId = [];
        foreach ($items as $item) {
            $xmlId = $this->extractInt($item, 'id');
            $idProduit = $this->extractString($item, 'id_produit');

            if ($xmlId > 0 && $idProduit !== '') {
                $idProduitToXmlId[$idProduit] = $xmlId;
            }
        }

        return $this->migrateFromMapping($idProduitToXmlId, $dryRun);
    }

    /**
     * @param  array<string, int>  $idProduitToXmlId
     * @return array{
     *     mapped: int,
     *     migrated: int,
     *     merged: int,
     *     skipped: int,
     *     conflicts: int,
     *     errors: list<string>
     * }
     */
    public function migrateFromMapping(array $idProduitToXmlId, bool $dryRun = false): array
    {
        $stats = [
            'mapped' => count($idProduitToXmlId),
            'migrated' => 0,
            'merged' => 0,
            'skipped' => 0,
            'conflicts' => 0,
            'errors' => [],
        ];

        $cars = Car::query()->get();

        foreach ($cars as $car) {
            $targetId = $idProduitToXmlId[$car->id_produit]
                ?? $this->inferXmlIdFromIdProduit($car->id_produit);

            if ($targetId === null || $targetId <= 0) {
                $stats['skipped']++;

                continue;
            }

            if ((int) $car->id === $targetId) {
                $stats['skipped']++;

                continue;
            }

            $occupant = Car::query()->find($targetId);

            if ($occupant !== null && (int) $occupant->id !== (int) $car->id) {
                if ($occupant->id_produit === $car->id_produit) {
                    if ($dryRun) {
                        $stats['merged']++;

                        continue;
                    }

                    try {
                        $this->mergeDuplicateIntoCanonical((int) $car->id, $targetId);
                        $stats['merged']++;
                    } catch (\Throwable $e) {
                        $stats['errors'][] = "Failed merge car id={$car->id} into {$targetId}: {$e->getMessage()}";
                    }

                    continue;
                }

                $stats['conflicts']++;
                $stats['errors'][] = "Conflict: car id={$car->id} ({$car->id_produit}) wants xml id={$targetId}, already used by car id={$occupant->id} ({$occupant->id_produit}).";

                continue;
            }

            if ($dryRun) {
                $stats['migrated']++;

                continue;
            }

            try {
                $this->reassignCarPrimaryKey((int) $car->id, $targetId);
                $stats['migrated']++;
            } catch (\Throwable $e) {
                $stats['errors'][] = "Failed car id={$car->id} -> {$targetId}: {$e->getMessage()}";
            }
        }

        return $stats;
    }

    public function mergeDuplicateIntoCanonical(int $duplicateId, int $canonicalId): void
    {
        if ($duplicateId === $canonicalId) {
            return;
        }

        DB::transaction(function () use ($duplicateId, $canonicalId): void {
            $this->mergeTableRows('car_photos', $duplicateId, $canonicalId);
            $this->mergeTableRows('car_options', $duplicateId, $canonicalId);
            $this->mergeTableRows('offers', $duplicateId, $canonicalId);
            $this->mergeTableRows('orders', $duplicateId, $canonicalId);
            $this->mergeMarketingRows($duplicateId, $canonicalId);
            $this->mergeFavouriteRows($duplicateId, $canonicalId);
            $this->mergeCartItemRows($duplicateId, $canonicalId);
            $this->mergeHistoryRows($duplicateId, $canonicalId);

            DB::table('cars')->where('id', $duplicateId)->delete();
        });
    }

    public function reassignCarPrimaryKey(int $oldId, int $newId): void
    {
        if ($oldId === $newId) {
            return;
        }

        DB::transaction(function () use ($oldId, $newId): void {
            $car = Car::query()->findOrFail($oldId);
            $attributes = $car->getAttributes();
            $attributes['id'] = $newId;

            DB::table('cars')->insert($attributes);

            foreach (self::CAR_ID_TABLES as $table) {
                DB::table($table)->where('car_id', $oldId)->update(['car_id' => $newId]);
            }

            DB::table('cars')->where('id', $oldId)->delete();
        });
    }

    private function mergeTableRows(string $table, int $duplicateId, int $canonicalId): void
    {
        DB::table($table)->where('car_id', $duplicateId)->update(['car_id' => $canonicalId]);
    }

    private function mergeMarketingRows(int $duplicateId, int $canonicalId): void
    {
        $canonicalHasMarketing = DB::table('car_marketings')->where('car_id', $canonicalId)->exists();

        if ($canonicalHasMarketing) {
            DB::table('car_marketings')->where('car_id', $duplicateId)->delete();

            return;
        }

        $this->mergeTableRows('car_marketings', $duplicateId, $canonicalId);
    }

    private function mergeFavouriteRows(int $duplicateId, int $canonicalId): void
    {
        $duplicateRows = DB::table('favourites')->where('car_id', $duplicateId)->get();

        foreach ($duplicateRows as $row) {
            $exists = DB::table('favourites')
                ->where('user_id', $row->user_id)
                ->where('car_id', $canonicalId)
                ->exists();

            if ($exists) {
                DB::table('favourites')->where('id', $row->id)->delete();
            } else {
                DB::table('favourites')->where('id', $row->id)->update(['car_id' => $canonicalId]);
            }
        }
    }

    private function mergeCartItemRows(int $duplicateId, int $canonicalId): void
    {
        $duplicateRows = DB::table('car_cart_items')->where('car_id', $duplicateId)->get();

        foreach ($duplicateRows as $row) {
            $exists = DB::table('car_cart_items')
                ->where('user_id', $row->user_id)
                ->where('car_id', $canonicalId)
                ->exists();

            if ($exists) {
                DB::table('car_cart_items')->where('id', $row->id)->delete();
            } else {
                DB::table('car_cart_items')->where('id', $row->id)->update(['car_id' => $canonicalId]);
            }
        }
    }

    private function mergeHistoryRows(int $duplicateId, int $canonicalId): void
    {
        $duplicateHistory = CarHistory::query()->where('car_id', $duplicateId)->get();
        $canonicalCar = Car::query()->findOrFail($canonicalId);

        foreach ($duplicateHistory as $entry) {
            $canonicalEntry = CarHistory::query()
                ->where('car_id', $canonicalId)
                ->where('status', $entry->status)
                ->first();

            if ($canonicalEntry === null) {
                $entry->update(['car_id' => $canonicalId]);

                continue;
            }

            if ($entry->buyer_info !== null && $entry->buyer_info !== []) {
                $this->carHistoryService->recordSold(
                    $canonicalCar,
                    $entry->buyer_info,
                    $entry->status,
                );
            }

            $entry->delete();
        }
    }

    public function inferXmlIdFromIdProduit(string $idProduit): ?int
    {
        if (preg_match('/(\d+)\s*$/', $idProduit, $matches) !== 1) {
            return null;
        }

        $id = (int) $matches[1];

        return $id > 0 ? $id : null;
    }

    private function extractString(SimpleXMLElement $item, string $field, string $default = ''): string
    {
        if (! isset($item->{$field})) {
            return $default;
        }

        $value = trim((string) $item->{$field});

        return $value !== '' ? $value : $default;
    }

    private function extractInt(SimpleXMLElement $item, string $field, int $default = 0): int
    {
        $value = trim((string) ($item->{$field} ?? ''));

        return $value !== '' && is_numeric($value) ? (int) $value : $default;
    }
}
