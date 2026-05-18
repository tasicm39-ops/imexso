<?php

namespace App\Console\Commands;

use App\Services\VenduXmlImportService;
use Illuminate\Console\Command;

class ImportVenduXmlCommand extends Command
{
    protected $signature = 'cars:import-vendu
                            {--file= : Path to a local vendu.xml file instead of fetching the remote URL}
                            {--no-create-missing : Do not create archive cars for vendu rows missing in the database}';

    protected $description = 'Import sold cars from vendu.xml and update car history';

    public function handle(VenduXmlImportService $importService): int
    {
        $file = $this->option('file');

        try {
            $xmlContent = is_string($file) && $file !== ''
                ? $this->readLocalFile($file)
                : null;

            $createMissing = ! (bool) $this->option('no-create-missing');
            $stats = $importService->import($xmlContent, $createMissing);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Vendu import completed.');
        $this->table(
            ['Metric', 'Count'],
            collect($stats)->map(fn (int $count, string $key) => [$key, $count])->values()->all(),
        );

        return self::SUCCESS;
    }

    private function readLocalFile(string $path): string
    {
        if (! is_file($path)) {
            throw new \InvalidArgumentException("Vendu XML file not found: {$path}");
        }

        $content = file_get_contents($path);

        if ($content === false || $content === '') {
            throw new \InvalidArgumentException("Vendu XML file is empty or unreadable: {$path}");
        }

        return $content;
    }
}
