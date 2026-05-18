<?php

namespace App\Console\Commands;

use App\Services\CarXmlImportService;
use Illuminate\Console\Command;

class ImportCarsStockXmlCommand extends Command
{
    protected $signature = 'cars:import-stock
                            {--file= : Path to a local cars.xml file instead of fetching the remote URL}';

    protected $description = 'Import stock cars from cars.xml (creates/updates catalog and IMPORTED history)';

    public function handle(CarXmlImportService $importService): int
    {
        try {
            $file = $this->option('file');

            $content = is_string($file) && $file !== ''
                ? $this->readLocalFile($file)
                : $importService->fetchStockXml();

            $import = $importService->import([
                'content' => $content,
                'filename' => is_string($file) && $file !== '' ? basename($file) : 'cars.xml',
                'user_id' => null,
            ]);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $yearsUpdated = $importService->backfillManufacturingYearsFromRegistrationDates();

        $this->info('Stock import completed.');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total in XML', $import->total_items_in_xml],
                ['New', $import->new_count],
                ['Updated', $import->updated_count],
                ['Unchanged', $import->unchanged_count],
                ['Years backfilled from registration date', $yearsUpdated],
            ],
        );

        return self::SUCCESS;
    }

    private function readLocalFile(string $path): string
    {
        if (! is_file($path)) {
            throw new \InvalidArgumentException("Cars XML file not found: {$path}");
        }

        $content = file_get_contents($path);

        if ($content === false || $content === '') {
            throw new \InvalidArgumentException("Cars XML file is empty or unreadable: {$path}");
        }

        return $content;
    }
}
