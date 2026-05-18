<?php

namespace App\Console\Commands;

use App\Services\CarXmlImportService;
use Illuminate\Console\Command;

class BackfillCarYearsCommand extends Command
{
    protected $signature = 'cars:backfill-years';

    protected $description = 'Set manufacturing_year from registration_date when year is missing';

    public function handle(CarXmlImportService $importService): int
    {
        $updated = $importService->backfillManufacturingYearsFromRegistrationDates();

        $this->info("Updated manufacturing_year on {$updated} cars.");

        return self::SUCCESS;
    }
}
