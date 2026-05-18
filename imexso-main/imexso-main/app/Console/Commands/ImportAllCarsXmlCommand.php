<?php

namespace App\Console\Commands;

use App\Services\VenduXmlImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ImportAllCarsXmlCommand extends Command
{
    protected $signature = 'cars:import-all
                            {--file-stock= : Local cars.xml path}
                            {--file-vendu= : Local vendu.xml path}
                            {--no-create-missing : Do not create cars from vendu when missing in database}';

    protected $description = 'Import stock from cars.xml then sold history from vendu.xml';

    public function handle(VenduXmlImportService $venduImportService): int
    {
        $stockOptions = [];
        $stockFile = $this->option('file-stock');
        if (is_string($stockFile) && $stockFile !== '') {
            $stockOptions['--file'] = $stockFile;
        }

        $this->info('Step 1/3: Importing stock (cars.xml)...');
        $stockExit = Artisan::call('cars:import-stock', $stockOptions);
        $this->output->write(Artisan::output());

        if ($stockExit !== self::SUCCESS) {
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Step 2/3: Importing sold cars (vendu.xml)...');

        try {
            $venduContent = $this->resolveVenduContent();
            $createMissing = ! (bool) $this->option('no-create-missing');
            $stats = $venduImportService->import($venduContent, $createMissing);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Metric', 'Count'],
            collect($stats)->map(fn (int $count, string $key) => [$key, $count])->values()->all(),
        );

        if (! (bool) $this->option('no-create-missing')) {
            $this->newLine();
            $this->info('Step 3/3: Enriching vendu archive cars (photos + stock data)...');

            try {
                $enrichStats = app(\App\Services\CarXmlImportService::class)->enrichVenduArchiveCars(
                    is_string($stockFile) && $stockFile !== ''
                        ? file_get_contents($stockFile) ?: null
                        : app(\App\Services\CarXmlImportService::class)->fetchStockXml(),
                );
                $this->table(
                    ['Metric', 'Count'],
                    [
                        ['Enriched from cars.xml', $enrichStats['enriched']],
                        ['Photos attached', $enrichStats['photos_attached']],
                        ['Skipped', $enrichStats['skipped']],
                    ],
                );
            } catch (\Throwable $e) {
                $this->warn('Archive enrichment skipped: '.$e->getMessage());
            }
        }

        return self::SUCCESS;
    }

    private function resolveVenduContent(): ?string
    {
        $file = $this->option('file-vendu');

        if (! is_string($file) || $file === '') {
            return null;
        }

        if (! is_file($file)) {
            throw new \InvalidArgumentException("Vendu XML file not found: {$file}");
        }

        $content = file_get_contents($file);

        if ($content === false || $content === '') {
            throw new \InvalidArgumentException("Vendu XML file is empty or unreadable: {$file}");
        }

        return $content;
    }
}
