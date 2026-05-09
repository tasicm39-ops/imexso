<?php

namespace Tests\Feature\Api;

use App\Models\ApiClient;
use App\Models\Car;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiClientTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->admin()->create();
    }

    public function test_guests_cannot_list_api_clients(): void
    {
        $this->getJson(route('api.api-clients.index'))
            ->assertUnauthorized();
    }

    public function test_list_api_clients(): void
    {
        ApiClient::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson(route('api.api-clients.index'));

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_create_api_client_returns_token(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('api.api-clients.store'), [
                'name' => 'Test Client',
                'contact_email' => 'test@example.com',
                'allowed_abilities' => ['cars:read'],
            ]);

        $response->assertCreated()
            ->assertJsonStructure(['data' => ['client', 'token']]);

        $this->assertDatabaseHas('api_clients', [
            'name' => 'Test Client',
            'contact_email' => 'test@example.com',
        ]);
    }

    public function test_create_api_client_validates_required_fields(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('api.api-clients.store'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'contact_email']);
    }

    public function test_show_api_client(): void
    {
        $client = ApiClient::factory()->create(['name' => 'My Client']);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.api-clients.show', $client));

        $response->assertOk()
            ->assertJsonPath('data.name', 'My Client');
    }

    public function test_generate_token_for_api_client(): void
    {
        $client = ApiClient::factory()->create();

        $response = $this->actingAs($this->user)
            ->postJson(route('api.api-clients.generate-token', $client), [
                'name' => 'new-token',
                'abilities' => ['cars:read'],
            ]);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['token']]);

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_revoke_tokens_for_api_client(): void
    {
        $client = ApiClient::factory()->create();
        $client->createToken('test-token-1', ['cars:read']);
        $client->createToken('test-token-2', ['cars:read']);

        $response = $this->actingAs($this->user)
            ->deleteJson(route('api.api-clients.revoke-tokens', $client));

        $response->assertOk()
            ->assertJsonPath('message', 'Revoked 2 token(s).');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_api_client_can_authenticate_with_token(): void
    {
        Car::factory()->count(2)->create();

        $client = ApiClient::factory()->create();
        $token = $client->createToken('test-token', ['cars:read']);

        $response = $this->getJson(route('api.cars.index'), [
            'Authorization' => 'Bearer '.$token->plainTextToken,
        ]);

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_invalid_token_returns_unauthorized(): void
    {
        $this->getJson(route('api.cars.index'), [
            'Authorization' => 'Bearer invalid-token-12345',
        ])->assertUnauthorized();
    }
}
