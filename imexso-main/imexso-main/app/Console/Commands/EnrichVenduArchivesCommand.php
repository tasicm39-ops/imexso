<?php

namespace App\Console\Commands;

use App\Services\CarXmlImportService;
use Illuminate\Console\Command;

class EnrichVenduArchivesCommand extends Command
{
    protected $signature = 'cars:enrich-vendu-archives
                            {--file-stock= : Optional cars.xml to merge full vehicle data when the car is still in stock feed}';

    protected $description = 'Attach photos and stock data to vendu archive cars (make UNKNOWN)';

    public function handle(CarXmlImportService $importService): int
    {
        $stockFile = $this->option('file-stock');
        $stockContent = null;

        try {
            if (is_string($stockFile) && $stockFile !== '') {
                if (! is_file($stockFile)) {
                    throw new \InvalidArgumentException("Cars XML file not found: {$stockFile}");
                }
                $stockContent = file_get_contents($stockFile) ?: '';
            } else {
                $stockContent = $importService->fetchStockXml();
            }

            $stats = $importService->enrichVenduArchiveCars($stockContent);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Vendu archive enrichment completed.');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Enriched from cars.xml', $stats['enriched']],
                ['Photos attached (CDN)', $stats['photos_attached']],
                ['Skipped (no data)', $stats['skipped']],
            ],
        );

        return self::SUCCESS;
    }
}
