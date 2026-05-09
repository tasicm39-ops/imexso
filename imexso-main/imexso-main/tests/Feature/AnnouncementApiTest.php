<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\AnnouncementTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_fetch_active_announcements(): void
    {
        $this->getJson(route('api.announcements.active'))
            ->assertUnauthorized();
    }

    public function test_pending_user_cannot_fetch_active_announcements(): void
    {
        $this->actingAs(User::factory()->create(['is_validated' => false]))
            ->getJson(route('api.announcements.active'))
            ->assertForbidden();
    }

    public function test_validated_user_receives_active_announcement(): void
    {
        $announcement = Announcement::query()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'is_active' => true,
            'show_to_validated_only' => true,
        ]);
        foreach (config('locales.supported') as $locale) {
            AnnouncementTranslation::query()->create([
                'announcement_id' => $announcement->id,
                'locale' => $locale,
                'title' => 'Title '.$locale,
                'body' => 'Body '.$locale,
            ]);
        }

        $this->actingAs(User::factory()->validated()->create())
            ->getJson(route('api.announcements.active').'?locale=fr')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Title fr');
    }

    public function test_active_announcement_falls_back_to_french_when_requested_locale_is_missing(): void
    {
        $announcement = Announcement::query()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'is_active' => true,
            'show_to_validated_only' => true,
        ]);
        AnnouncementTranslation::query()->create([
            'announcement_id' => $announcement->id,
            'locale' => 'fr',
            'title' => 'Title fr',
            'body' => 'Body fr',
        ]);

        $this->actingAs(User::factory()->validated()->create())
            ->getJson(route('api.announcements.active').'?locale=de')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Title fr')
            ->assertJsonPath('data.0.body', 'Body fr');
    }
}
