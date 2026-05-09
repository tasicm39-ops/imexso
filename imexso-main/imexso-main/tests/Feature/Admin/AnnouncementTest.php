<?php

namespace Tests\Feature\Admin;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_announcement_with_only_french_and_optional_locales_copy_french(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.announcements.store'), [
                'starts_at' => now()->format('Y-m-d\TH:i'),
                'ends_at' => now()->addDay()->format('Y-m-d\TH:i'),
                'is_active' => true,
                'show_to_validated_only' => true,
                'title_fr' => 'Bonjour',
                'body_fr' => 'Texte',
                'title_nl' => '',
                'body_nl' => '',
                'title_de' => '',
                'body_de' => '',
                'title_it' => '',
                'body_it' => '',
                'title_en' => '',
                'body_en' => '',
            ])
            ->assertRedirect(route('admin.announcements.index'));

        $announcement = Announcement::query()->first();
        $this->assertNotNull($announcement);
        $nl = $announcement->translations->firstWhere('locale', 'nl');
        $this->assertNotNull($nl);
        $this->assertSame('Bonjour', $nl->title);
        $this->assertSame('Texte', $nl->body);
    }

    public function test_store_requires_french_or_english_pair(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.announcements.create'))
            ->post(route('admin.announcements.store'), [
                'starts_at' => now()->format('Y-m-d\TH:i'),
                'ends_at' => now()->addDay()->format('Y-m-d\TH:i'),
                'is_active' => true,
                'show_to_validated_only' => true,
                'title_fr' => '',
                'body_fr' => '',
                'title_nl' => 'x',
                'body_nl' => 'y',
                'title_de' => '',
                'body_de' => '',
                'title_it' => '',
                'body_it' => '',
                'title_en' => '',
                'body_en' => '',
            ])
            ->assertSessionHasErrors('title_fr');

        $this->assertDatabaseCount('announcements', 0);
    }
}
