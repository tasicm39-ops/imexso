<?php

namespace Tests\Feature\Api;

use App\Models\SegmentEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SegmentEventTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->validated()->create();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_guest_cannot_create_segment_event(): void
    {
        $this->postJson(route('api.segment-events.store'), [
            'event_type' => 'page_view',
        ])->assertUnauthorized();
    }

    public function test_authenticated_user_can_create_segment_event(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('api.segment-events.store'), [
                'event_type' => 'search',
                'payload' => ['query' => 'citroen c5'],
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.event_type', 'search')
            ->assertJsonPath('data.user_id', $this->user->id)
            ->assertJsonPath('data.payload.query', 'citroen c5');

        $this->assertDatabaseHas('segment_events', [
            'user_id' => $this->user->id,
            'event_type' => 'search',
        ]);
    }

    public function test_segment_event_stores_ip_and_user_agent(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('api.segment-events.store'), [
                'event_type' => 'page_view',
            ], [
                'User-Agent' => 'TestBrowser/1.0',
            ]);

        $response->assertCreated();

        $event = SegmentEvent::query()->first();
        $this->assertNotNull($event->ip_address);
        $this->assertEquals('TestBrowser/1.0', $event->user_agent);
    }

    public function test_segment_event_requires_valid_event_type(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('api.segment-events.store'), [
                'event_type' => 'invalid_type',
            ])->assertUnprocessable()
            ->assertJsonValidationErrors('event_type');

        $this->actingAs($this->user)
            ->postJson(route('api.segment-events.store'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('event_type');
    }

    public function test_guest_cannot_list_segment_events(): void
    {
        $this->getJson(route('api.segment-events.index'))
            ->assertUnauthorized();
    }

    public function test_non_admin_cannot_list_segment_events(): void
    {
        $this->actingAs($this->user)
            ->getJson(route('api.segment-events.index'))
            ->assertForbidden();
    }

    public function test_admin_can_list_segment_events(): void
    {
        SegmentEvent::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->getJson(route('api.segment-events.index'));

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'user_id', 'event_type', 'payload', 'ip_address', 'created_at'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_admin_can_filter_events_by_type(): void
    {
        SegmentEvent::factory()->count(2)->create(['event_type' => 'search']);
        SegmentEvent::factory()->count(3)->create(['event_type' => 'page_view']);

        $response = $this->actingAs($this->admin)
            ->getJson(route('api.segment-events.index', ['event_type' => 'search']));

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_admin_can_filter_events_by_user_id(): void
    {
        $targetUser = User::factory()->create();
        SegmentEvent::factory()->count(2)->create(['user_id' => $targetUser->id]);
        SegmentEvent::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->getJson(route('api.segment-events.index', ['user_id' => $targetUser->id]));

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }
}
