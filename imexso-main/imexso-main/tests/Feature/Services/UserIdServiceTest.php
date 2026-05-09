<?php

namespace Tests\Feature\Services;

use App\Models\User;
use App\Services\UserIdService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserIdServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserIdService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new UserIdService;
    }

    public function test_generate_user_id_starts_at_u0001(): void
    {
        $id = $this->service->generateUserId();

        $this->assertEquals('U0001', $id);
    }

    public function test_generate_user_id_increments_from_existing(): void
    {
        User::factory()->create(['legacy_user_id' => 'U0029']);
        User::factory()->create(['legacy_user_id' => 'U9745']);

        $id = $this->service->generateUserId();

        $this->assertEquals('U9746', $id);
    }

    public function test_generate_client_id_starts_at_c0001(): void
    {
        $id = $this->service->generateClientId();

        $this->assertEquals('C0001', $id);
    }

    public function test_generate_client_id_increments_from_existing(): void
    {
        User::factory()->create(['legacy_client_id' => 'C1412']);
        User::factory()->create(['legacy_client_id' => 'C7728']);

        $id = $this->service->generateClientId();

        $this->assertEquals('C7729', $id);
    }

    public function test_generate_user_id_handles_large_numbers(): void
    {
        User::factory()->create(['legacy_user_id' => 'U11034']);

        $id = $this->service->generateUserId();

        $this->assertEquals('U11035', $id);
    }
}
