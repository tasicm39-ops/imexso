<?php

namespace Database\Seeders;

use App\Models\SitePage;
use Illuminate\Database\Seeder;

class SitePageSeeder extends Seeder
{
    /**
     * Seed default CMS page keys (content is edited in admin).
     */
    public function run(): void
    {
        foreach (['installations', 'historique', 'engagements', 'equipe'] as $slug) {
            SitePage::query()->firstOrCreate(
                ['slug' => $slug],
                ['published_at' => null]
            );
        }
    }
}
