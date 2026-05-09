<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileCompanyTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->user = User::factory()->admin()->create();
    }

    public function test_profile_page_shows_logo_url(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.edit'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('settings/profile')
                ->has('logoUrl')
            );
    }

    public function test_user_can_update_company_name_and_slogan(): void
    {
        $response = $this->actingAs($this->user)
            ->patch(route('profile.update'), [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'company_name' => 'Dataspilot',
                'slogan' => 'Best Cars Ever',
            ]);

        $response->assertRedirect(route('profile.edit'));

        $this->user->refresh();
        $this->assertEquals('Dataspilot', $this->user->company_name);
        $this->assertEquals('Best Cars Ever', $this->user->slogan);
    }

    public function test_user_can_upload_logo(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->user)
            ->patch(route('profile.update'), [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'logo' => UploadedFile::fake()->image('logo.png', 200, 100),
            ]);

        $response->assertRedirect(route('profile.edit'));

        $this->user->refresh();
        $this->assertNotNull($this->user->logo);
        Storage::disk('public')->assertExists($this->user->logo);
    }

    public function test_old_logo_is_deleted_on_new_upload(): void
    {
        Storage::fake('public');

        $oldPath = UploadedFile::fake()->image('old.png')->store('logos', 'public');
        $this->user->update(['logo' => $oldPath]);

        $this->actingAs($this->user)
            ->patch(route('profile.update'), [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'logo' => UploadedFile::fake()->image('new.png', 200, 100),
            ]);

        Storage::disk('public')->assertMissing($oldPath);
        $this->user->refresh();
        Storage::disk('public')->assertExists($this->user->logo);
    }

    public function test_logo_validation_rejects_non_image(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->user)
            ->patch(route('profile.update'), [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'logo' => UploadedFile::fake()->create('doc.pdf', 500, 'application/pdf'),
            ]);

        $response->assertSessionHasErrors('logo');
    }

    public function test_logo_validation_rejects_oversized_file(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->user)
            ->patch(route('profile.update'), [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'logo' => UploadedFile::fake()->image('huge.png')->size(3000),
            ]);

        $response->assertSessionHasErrors('logo');
    }
}
