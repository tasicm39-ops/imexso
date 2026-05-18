<?php

namespace App\Console\Commands;

use App\Services\CarIdMigrationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class MigrateCarIdsFromXmlCommand extends Command
{
    protected $signature = 'cars:migrate-ids
                            {--file= : Path to a local cars.xml file instead of fetching the remote URL}
                            {--dry-run : Show what would change without writing to the database}';

    protected $description = 'Align cars.id with XML <id> values using cars.xml (updates all related tables)';

    public function handle(CarIdMigrationService $migrationService): int
    {
        $dryRun = (bool) $this->option('dry-run');

        try {
            $xmlContent = $this->resolveXmlContent();
            $stats = $migrationService->migrateFromXml($xmlContent, $dryRun);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info($dryRun ? 'Dry run completed (no changes written).' : 'Car ID migration completed.');
        $this->table(
            ['Metric', 'Count'],
            [
                ['XML mappings', $stats['mapped']],
                [$dryRun ? 'Would migrate' : 'Migrated', $stats['migrated']],
                [$dryRun ? 'Would merge duplicates' : 'Merged duplicates', $stats['merged']],
                ['Skipped (already correct or unknown)', $stats['skipped']],
                ['Conflicts', $stats['conflicts']],
            ],
        );

        if ($stats['errors'] !== []) {
            $this->newLine();
            $this->warn('Issues:');
            foreach ($stats['errors'] as $error) {
                $this->line("  - {$error}");
            }
        }

        return $stats['conflicts'] > 0 || $stats['errors'] !== [] ? self::FAILURE : self::SUCCESS;
    }

    private function resolveXmlContent(): string
    {
        $file = $this->option('file');

        if (is_string($file) && $file !== '') {
            if (! is_file($file)) {
                throw new \InvalidArgumentException("Cars XML file not found: {$file}");
            }

            $content = file_get_contents($file);

            if ($content === false || $content === '') {
                throw new \InvalidArgumentException("Cars XML file is empty or unreadable: {$file}");
            }

            return $content;
        }

        $url = config('imexso.cars_xml_url');
        $response = Http::timeout(120)->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException(
                "Failed to fetch cars XML from {$url}: HTTP {$response->status()}",
            );
        }

        $body = $response->body();

        if ($body === '') {
            throw new \RuntimeException("Cars XML response from {$url} was empty.");
        }

        return $body;
    }
}
