<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LegacyUserSeeder extends Seeder
{
    public function run(): void
    {
        $sqlPath = database_path('data/legacy_users.sql');

        if (! file_exists($sqlPath)) {
            $this->command->warn("Legacy users SQL file not found at: {$sqlPath}");
            $this->command->info('Place the exported legacy users SQL file at database/data/legacy_users.sql');

            return;
        }

        $sql = file_get_contents($sqlPath);

        if ($sql === false || $sql === '') {
            $this->command->error('Failed to read legacy users SQL file.');

            return;
        }

        $rows = $this->parseLegacySql($sql);

        $this->command->info("Found {$rows->count()} legacy users to import.");

        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $email = trim($row['email'] ?? '');

            if ($email === '' || $email === 'NULL') {
                $skipped++;

                continue;
            }

            if (User::query()->where('email', $email)->exists()) {
                $skipped++;

                continue;
            }

            User::query()->create([
                'name' => trim(($row['prenom'] ?? '').' '.($row['nom'] ?? '')),
                'first_name' => $this->nullIfEmpty($row['prenom'] ?? ''),
                'last_name' => $this->nullIfEmpty($row['nom'] ?? ''),
                'email' => $email,
                'password' => Hash::make('changeme-'.uniqid()),
                'legacy_user_id' => $this->nullIfEmpty($row['id_utilisateur'] ?? ''),
                'legacy_client_id' => $this->nullIfEmpty($row['id_client'] ?? ''),
                'phone' => $this->nullIfEmpty($row['tel'] ?? ''),
                'language' => $this->nullIfEmpty(strtolower($row['lg'] ?? '')),
                'user_type' => $this->nullIfEmpty($row['type'] ?? ''),
                'company_name' => $this->nullIfEmpty($row['client'] ?? ''),
                'legal_form' => $this->nullIfEmpty($row['formej'] ?? ''),
                'address' => $this->nullIfEmpty($row['adresse'] ?? ''),
                'postal_code' => $this->nullIfEmpty($row['cp'] ?? ''),
                'city' => $this->nullIfEmpty($row['ville'] ?? ''),
                'country' => $this->nullIfEmpty($row['pays'] ?? ''),
                'fax' => $this->nullIfEmpty($row['fax'] ?? ''),
                'vat_number' => $this->nullIfEmpty($row['tva'] ?? ''),
                'is_active' => ($row['actif'] ?? 'n') === 'y',
                'is_validated' => ($row['validation'] ?? 'n') === 'y',
                'is_seller' => ($row['vendeur'] ?? 'n') === 'y',
                'activity' => $this->nullIfEmpty($row['activite'] ?? ''),
                'website' => $this->nullIfEmpty($row['website'] ?? ''),
                'last_contact_date' => $this->parseDate($row['last_contact_date'] ?? ''),
                'last_order_date' => $this->parseDate($row['date_derniere_commande'] ?? ''),
                'new_car_seller' => $this->nullIfEmpty($row['vendeur_vn'] ?? ''),
                'used_car_seller' => $this->nullIfEmpty($row['vendeur_vo'] ?? ''),
                'logo' => $this->nullIfEmpty($row['logo'] ?? ''),
                'slogan' => $this->nullIfEmpty($row['slogan'] ?? ''),
                'car_volume' => (int) ($row['volume_voitures'] ?? 0),
                'revenue' => (float) ($row['chiffre_affaire'] ?? 0),
                'activity_follow_up' => $this->nullIfEmpty($row['activite_suivi'] ?? ''),
                'vehicles_searched' => $this->nullIfEmpty($row['vehicules_recherche'] ?? ''),
                'comments' => $this->nullIfEmpty($row['commentaires'] ?? ''),
                'old_email' => $this->nullIfEmpty($row['old_mail'] ?? ''),
            ]);

            $imported++;
        }

        $this->command->info("Imported {$imported} users, skipped {$skipped}.");
    }

    /**
     * @return Collection<int, array<string, string|null>>
     */
    private function parseLegacySql(string $sql): Collection
    {
        $tempConnection = config('database.default') === 'sqlite' ? 'sqlite' : config('database.default');

        DB::connection($tempConnection)->unprepared('CREATE TABLE IF NOT EXISTS legacy_users_import (
            id_utilisateur VARCHAR(15),
            date_creation DATETIME,
            prenom VARCHAR(100),
            nom VARCHAR(100),
            email VARCHAR(50),
            tel VARCHAR(20),
            lg VARCHAR(5),
            type VARCHAR(15),
            login VARCHAR(50),
            pw VARCHAR(32),
            login_permanent VARCHAR(1),
            client VARCHAR(200),
            id_client VARCHAR(15),
            formej VARCHAR(20),
            adresse VARCHAR(200),
            cp VARCHAR(6),
            ville VARCHAR(50),
            pays VARCHAR(50),
            fax VARCHAR(20),
            tva VARCHAR(15),
            actif VARCHAR(1),
            date_last_activation DATETIME,
            date_last_connexion DATETIME,
            date_last_update DATETIME,
            validation VARCHAR(1),
            activite VARCHAR(150),
            website VARCHAR(250),
            vendeur VARCHAR(1),
            appel DATE,
            texte TEXT,
            connection_token VARCHAR(30),
            client_demarche_par VARCHAR(100),
            date_derniere_commande DATE,
            vendeur_vn VARCHAR(100),
            vendeur_vo VARCHAR(100),
            logo VARCHAR(255),
            slogan TEXT,
            id_status INTEGER,
            volume_voitures INTEGER,
            chiffre_affaire DECIMAL(10,2),
            status_mod INTEGER,
            last_contact_date DATE,
            activite_suivi TEXT,
            vehicules_recherche TEXT,
            commentaires TEXT,
            token_reset_pswd VARCHAR(255),
            old_mail VARCHAR(50)
        )');

        $insertStatements = preg_match_all('/INSERT INTO.*?;/s', $sql, $matches);
        if ($insertStatements && ! empty($matches[0])) {
            foreach ($matches[0] as $statement) {
                $adapted = str_replace('INSERT INTO `users`', 'INSERT INTO legacy_users_import', $statement);
                $adapted = str_replace('\\\'', "''", $adapted);
                DB::connection($tempConnection)->unprepared($adapted);
            }
        }

        $rows = collect(DB::connection($tempConnection)->select('SELECT * FROM legacy_users_import'))
            ->map(fn ($row) => (array) $row);

        DB::connection($tempConnection)->unprepared('DROP TABLE IF EXISTS legacy_users_import');

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
