<?php

namespace Database\Seeders;

use App\Models\SaleHistory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LegacySaleHistorySeeder extends Seeder
{
    public function run(): void
    {
        $sqlPath = database_path('data/legacy_history.sql');

        if (! file_exists($sqlPath)) {
            $this->command->warn("Legacy history SQL file not found at: {$sqlPath}");
            $this->command->info('Place the exported legacy history SQL file at database/data/legacy_history.sql');

            return;
        }

        $sql = file_get_contents($sqlPath);

        if ($sql === false || $sql === '') {
            $this->command->error('Failed to read legacy history SQL file.');

            return;
        }

        $rows = $this->parseLegacySql($sql);

        $this->command->info("Found {$rows->count()} legacy sale history records to import.");

        $imported = 0;

        foreach ($rows as $row) {
            SaleHistory::query()->create([
                'id_produit' => $row['id_produit'] ?? '',
                'vin' => $row['chassis'] ?? '',
                'condition_type' => $row['vn_vo'] ?? '',
                'make' => $row['marque'] ?? '',
                'model' => $row['modeles'] ?? '',
                'trim_level' => $this->nullIfEmpty($row['finition'] ?? ''),
                'radio_code' => $this->nullIfEmpty($row['code_radio'] ?? ''),
                'color' => $this->nullIfEmpty($row['couleur'] ?? ''),
                'location' => $this->nullIfEmpty($row['emplacement'] ?? ''),
                'price' => $this->nullIfEmpty($row['prix'] ?? ''),
                'tax_type' => $this->nullIfEmpty($row['taxe'] ?? ''),
                'client_id' => $row['id_client'] ?? '',
                'status' => $row['statut'] ?? '',
                'order_date' => $this->parseDate($row['date_commande'] ?? ''),
                'documents' => $this->nullIfEmpty($row['documents'] ?? ''),
                'invoices' => $this->nullIfEmpty($row['bc_fv'] ?? ''),
                'to_unblock' => $this->nullIfEmpty($row['a_debloquer'] ?? ''),
                'assignment_count' => is_numeric($row['cession'] ?? '') ? (int) $row['cession'] : null,
                'assignment_1' => $this->nullIfEmpty($row['cession1'] ?? ''),
                'assignment_2' => $this->nullIfEmpty($row['cession2'] ?? ''),
                'days_available' => (int) ($row['jours_dispo'] ?? 0),
                'lot_number' => $this->nullIfEmpty($row['num_lot'] ?? ''),
            ]);

            $imported++;
        }

        $this->command->info("Imported {$imported} sale history records.");
    }

    /**
     * @return Collection<int, array<string, string|null>>
     */
    private function parseLegacySql(string $sql): Collection
    {
        $tempConnection = config('database.default') === 'sqlite' ? 'sqlite' : config('database.default');

        DB::connection($tempConnection)->unprepared('CREATE TABLE IF NOT EXISTS legacy_history_import (
            id_produit VARCHAR(10),
            chassis VARCHAR(100),
            vn_vo VARCHAR(20),
            marque VARCHAR(100),
            modeles VARCHAR(100),
            finition VARCHAR(100),
            code_radio VARCHAR(50),
            couleur VARCHAR(200),
            emplacement VARCHAR(50),
            prix VARCHAR(200),
            taxe VARCHAR(10),
            id_client VARCHAR(10),
            statut VARCHAR(20),
            date_commande DATE,
            documents TEXT,
            bc_fv TEXT,
            a_debloquer VARCHAR(5),
            cession INTEGER,
            cession1 TEXT,
            cession2 TEXT,
            jours_dispo INTEGER,
            num_lot VARCHAR(255)
        )');

        $insertStatements = preg_match_all('/INSERT INTO.*?;/s', $sql, $matches);
        if ($insertStatements && ! empty($matches[0])) {
            foreach ($matches[0] as $statement) {
                $adapted = str_replace('INSERT INTO `history`', 'INSERT INTO legacy_history_import', $statement);
                $adapted = str_replace('\\\'', "''", $adapted);
                DB::connection($tempConnection)->unprepared($adapted);
            }
        }

        $rows = collect(DB::connection($tempConnection)->select('SELECT * FROM legacy_history_import'))
            ->map(fn ($row) => (array) $row);

        DB::connection($tempConnection)->unprepared('DROP TABLE IF EXISTS legacy_history_import');

        return $rows;
    }

    private function nullIfEmpty(string $value): ?string
    {
        $trimmed = trim($value);

        return ($trimmed === '' || $trimmed === 'NULL') ? null : $trimmed;
    }

    private function parseDate(string $value): ?string
    {
        $trimmed = trim($value);

        if ($trimmed === '' || $trimmed === 'NULL' || $trimmed === '0000-00-00') {
            return null;
        }

        return $trimmed;
    }
}
