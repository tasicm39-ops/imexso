<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->validated()->create();
    }

    public function test_guests_cannot_update_profile(): void
    {
        $this->postJson(route('api.profile.update'), [
            'name' => 'Test',
            'email' => 'test@example.com',
        ])->assertUnauthorized();
    }

    public function test_user_can_update_company_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('api.profile.update'), [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'company_name' => 'PolovniAutomobili',
                'slogan' => 'Drive Your Dream',
            ]);

        $response->assertOk()
            ->assertJson(['message' => 'Profile updated successfully.']);

        $this->user->refresh();
        $this->assertEquals('PolovniAutomobili', $this->user->company_name);
        $this->assertEquals('Drive Your Dream', $this->user->slogan);
    }

    public function test_user_can_upload_logo_via_api(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->user)
            ->post(route('api.profile.update'), [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'logo' => UploadedFile::fake()->image('logo.jpg', 300, 100),
            ]);

        $response->assertOk();

        $this->user->refresh();
        $this->assertNotNull($this->user->logo);
        Storage::disk('public')->assertExists($this->user->logo);
    }

    public function test_api_profile_validates_email(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('api.profile.update'), [
                'name' => 'Test',
                'email' => 'not-an-email',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_api_profile_validates_logo_type(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->user)
            ->post(route('api.profile.update'), [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'logo' => UploadedFile::fake()->create('file.txt', 100, 'text/plain'),
            ]);

        $response->assertSessionHasErrors('logo');
    }
}
