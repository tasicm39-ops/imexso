<?php

namespace Tests\Feature;

use App\Models\SitePage;
use App\Models\SitePageContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitePageApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unpublished_page_returns_404(): void
    {
        $page = SitePage::query()->create([
            'slug' => 'installations',
            'published_at' => null,
        ]);
        SitePageContent::query()->create([
            'site_page_id' => $page->id,
            'locale' => 'fr',
            'payload' => ['bannerSrc' => 'banners/test.jpg'],
        ]);

        $this->getJson(route('api.site-pages.show', ['slug' => 'installations']).'?locale=fr')
            ->assertNotFound();
    }

    public function test_published_page_returns_json(): void
    {
        $page = SitePage::query()->create([
            'slug' => 'installations',
            'published_at' => now()->subMinute(),
        ]);
        SitePageContent::query()->create([
            'site_page_id' => $page->id,
            'locale' => 'fr',
            'payload' => ['bannerSrc' => 'banners/test.jpg'],
        ]);

        $response = $this->getJson(route('api.site-pages.show', ['slug' => 'installations']).'?locale=fr');

        $response->assertOk()
            ->assertJsonPath('slug', 'installations')
            ->assertJsonPath('locale', 'fr')
            ->assertJsonPath('payload.bannerSrc', 'banners/test.jpg');
    }

    public function test_unknown_slug_returns_404(): void
    {
        $this->getJson(route('api.site-pages.show', ['slug' => 'unknown-page']))
            ->assertNotFound();
    }
}
