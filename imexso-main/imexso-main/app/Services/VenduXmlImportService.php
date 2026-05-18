<?php

namespace App\Services;

use App\Enums\CarHistoryStatus;
use App\Enums\CarStockStatus;
use App\Models\Car;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class VenduXmlImportService
{
    public function __construct(
        private readonly CarXmlImportService $carXmlImportService,
        private readonly CarHistoryService $carHistoryService,
    ) {}

    /**
     * @return array{
     *     processed: int,
     *     updated: int,
     *     cars_created: int,
     *     history_created: int,
     *     history_updated: int,
     *     skipped: int,
     *     missing_cars: int
     * }
     */
    public function import(?string $xmlContent = null, ?bool $createMissing = null): array
    {
        $createMissing ??= (bool) config('imexso.vendu_create_missing_cars', true);

        $content = $xmlContent ?? $this->fetchXml();
        $xml = $this->carXmlImportService->parseXml($content);
        $items = $this->carXmlImportService->extractItems($xml);

        $stats = [
            'processed' => 0,
            'updated' => 0,
            'cars_created' => 0,
            'history_created' => 0,
            'history_updated' => 0,
            'skipped' => 0,
            'missing_cars' => 0,
        ];

        DB::transaction(function () use ($items, $createMissing, &$stats) {
            foreach ($items as $item) {
                $stats['processed']++;

                $car = $this->resolveCar($item);

                if ($car === null && $createMissing) {
                    $car = $this->carXmlImportService->createCarFromVenduItem($item);

                    if ($car !== null) {
                        $stats['cars_created']++;
                    }
                }

                if ($car === null) {
                    $stats['missing_cars']++;

                    continue;
                }

                $buyerInfo = $this->extractBuyerInfo($item);

                if ($car->stock_status !== CarStockStatus::Sold->value) {
                    $car->update([
                        'stock_status' => CarStockStatus::Sold->value,
                        'sync_status' => 'sold',
                        'last_synced_at' => now(),
                    ]);
                    $stats['updated']++;
                }

                $historyResult = $this->carHistoryService->recordSold($car, $buyerInfo);

                match ($historyResult) {
                    CarHistoryService::RESULT_CREATED => $stats['history_created']++,
                    CarHistoryService::RESULT_UPDATED => $stats['history_updated']++,
                    default => $stats['skipped']++,
                };
            }
        });

        return $stats;
    }

    public function fetchXml(): string
    {
        $url = config('imexso.vendu_xml_url');
        $response = Http::timeout(120)->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException(
                "Failed to fetch vendu XML from {$url}: HTTP {$response->status()}",
            );
        }

        $body = $response->body();

        if ($body === '') {
            throw new \RuntimeException("Vendu XML response from {$url} was empty.");
        }

        return $body;
    }

    private function resolveCar(SimpleXMLElement $item): ?Car
    {
        $xmlId = $this->extractInt($item, 'id');

        if ($xmlId > 0) {
            $car = Car::query()->find($xmlId);
            if ($car !== null) {
                return $car;
            }
        }

        $idProduit = $this->extractString($item, 'id_produit');

        if ($idProduit === '') {
            return null;
        }

        return Car::query()->where('id_produit', $idProduit)->first();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function extractBuyerInfo(SimpleXMLElement $item): ?array
    {
        $fields = [
            'id_client' => $this->extractNullableString($item, 'id_client'),
            'client_id' => $this->extractNullableString($item, 'client_id'),
            'client' => $this->extractNullableString($item, 'client'),
            'nom_client' => $this->extractNullableString($item, 'nom_client'),
            'acheteur' => $this->extractNullableString($item, 'acheteur'),
            'buyer' => $this->extractNullableString($item, 'buyer'),
            'date_vente' => $this->extractNullableString($item, 'date_vente'),
            'date_commande' => $this->extractNullableString($item, 'date_commande'),
            'statut' => $this->extractNullableString($item, 'statut'),
        ];

        $filtered = array_filter($fields, fn (?string $value) => $value !== null && $value !== '');

        return $filtered === [] ? null : $filtered;
    }

    private function extractString(SimpleXMLElement $item, string $field, string $default = ''): string
    {
        if (! isset($item->{$field})) {
            return $default;
        }

        $value = trim((string) $item->{$field});

        return $value !== '' ? $value : $default;
    }

    private function extractNullableString(SimpleXMLElement $item, string $field): ?string
    {
        if (! isset($item->{$field})) {
            return null;
        }

        $value = trim((string) $item->{$field});

        return $value !== '' ? $value : null;
    }

    private function extractInt(SimpleXMLElement $item, string $field, int $default = 0): int
    {
        $value = trim((string) ($item->{$field} ?? ''));

        return $value !== '' && is_numeric($value) ? (int) $value : $default;
    }
}
