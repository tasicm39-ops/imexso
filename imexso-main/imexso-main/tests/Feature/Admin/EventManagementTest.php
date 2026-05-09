<?php

namespace Tests\Feature\Admin;

use App\Models\SegmentEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_guests_cannot_access_events(): void
    {
        $this->get(route('admin.events.index'))
            ->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_events(): void
    {
        $user = User::factory()->validated()->create();

        $this->actingAs($user)
            ->get(route('admin.events.index'))
            ->assertForbidden();
    }

    public function test_admin_can_list_events(): void
    {
        SegmentEvent::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.events.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/events/index')
                ->has('events.data', 5)
                ->has('eventTypes')
            );
    }

    public function test_admin_can_filter_events_by_type(): void
    {
        SegmentEvent::factory()->count(3)->create(['event_type' => 'page_view']);
        SegmentEvent::factory()->count(2)->create(['event_type' => 'car_click']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.events.index', ['event_type' => 'page_view']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('events.data', 3)
            );
    }

    public function test_admin_can_search_events_by_user(): void
    {
        $user1 = User::factory()->create(['name' => 'Xylophone Unique']);
        $user2 = User::factory()->create(['name' => 'Jane Smith']);

        SegmentEvent::factory()->count(2)->create(['user_id' => $user1->id]);
        SegmentEvent::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.events.index', ['search' => 'Xylophone']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('events.data', 2)
            );
    }

    public function test_admin_can_filter_events_by_date_range(): void
    {
        SegmentEvent::factory()->create(['created_at' => now()->subDays(5)]);
        SegmentEvent::factory()->create(['created_at' => now()->subDays(3)]);
        SegmentEvent::factory()->create(['created_at' => now()]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.events.index', [
                'date_from' => now()->subDays(4)->format('Y-m-d'),
                'date_to' => now()->format('Y-m-d'),
            ]));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('events.data', 2)
            );
    }

    public function test_events_are_ordered_by_newest_first(): void
    {
        $old = SegmentEvent::factory()->create(['created_at' => now()->subDay()]);
        $new = SegmentEvent::factory()->create(['created_at' => now()]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.events.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('events.data', 2)
                ->where('events.data.0.id', $new->id)
            );
    }

    public function test_events_include_user_relationship(): void
    {
        $user = User::factory()->create(['name' => 'Test User']);
        SegmentEvent::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.events.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('events.data.0.user')
                ->where('events.data.0.user.name', 'Test User')
            );
    }

    public function test_admin_can_filter_events_by_payload_make(): void
    {
        SegmentEvent::factory()->create([
            'event_type' => 'view_car',
            'payload' => ['car_id' => 1, 'make' => 'RENAULT', 'model' => 'CLIO'],
        ]);
        SegmentEvent::factory()->create([
            'event_type' => 'view_car',
            'payload' => ['car_id' => 2, 'make' => 'PEUGEOT', 'model' => '208'],
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.events.index', ['payload_make' => 'RENAULT']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('events.data', 1)
            );
    }

    public function test_admin_can_filter_events_by_payload_model(): void
    {
        SegmentEvent::factory()->create([
            'event_type' => 'filter',
            'payload' => ['make' => 'RENAULT', 'model' => 'CLIO'],
        ]);
        SegmentEvent::factory()->create([
            'event_type' => 'filter',
            'payload' => ['make' => 'RENAULT', 'model' => 'MEGANE'],
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.events.index', ['payload_model' => 'CLIO']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('events.data', 1)
            );
    }

    public function test_admin_can_filter_events_by_country(): void
    {
        $frenchUser = User::factory()->create(['country' => 'France']);
        $germanUser = User::factory()->create(['country' => 'Germany']);

        SegmentEvent::factory()->count(2)->create(['user_id' => $frenchUser->id]);
        SegmentEvent::factory()->create(['user_id' => $germanUser->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.events.index', ['country' => 'France']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('events.data', 2)
            );
    }

    public function test_admin_can_search_by_company_name(): void
    {
        $user = User::factory()->create(['company_name' => 'RENT TRANSAK']);
        SegmentEvent::factory()->create(['user_id' => $user->id]);
        SegmentEvent::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.events.index', ['search' => 'RENT TRANSAK']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('events.data', 1)
            );
    }

    public function test_admin_can_change_per_page(): void
    {
        SegmentEvent::factory()->count(30)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.events.index', ['per_page' => '50']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('events.data', 30)
                ->where('events.per_page', 50)
            );
    }

    public function test_response_includes_filter_dropdowns(): void
    {
        SegmentEvent::factory()->create([
            'event_type' => 'view_car',
            'payload' => ['car_id' => 1, 'make' => 'RENAULT', 'model' => 'CLIO'],
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.events.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('payloadMakes')
                ->has('payloadModels')
                ->has('countries')
            );
    }
}
