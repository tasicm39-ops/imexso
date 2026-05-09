<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOnlyLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_admin_can_login_via_inertia(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);

        $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_non_admin_cannot_login_via_inertia(): void
    {
        User::factory()->validated()->create([
            'email' => 'user@example.com',
        ]);

        $response = $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_non_admin_can_login_via_api(): void
    {
        $user = User::factory()->validated()->create([
            'email' => 'user@example.com',
        ]);

        $response = $this->postJson('/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $response->assertOk();
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_login_via_api(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);

        $response = $this->postJson('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertOk();
        $this->assertAuthenticatedAs($admin);
    }

    public function test_register_view_redirects_to_frontend(): void
    {
        $response = $this->get('/register');

        $response->assertRedirect('/register');
    }

    public function test_login_view_does_not_show_register_link(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('auth/login')
                ->where('canRegister', false)
            );
    }

    public function test_invalid_credentials_still_fail(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);

        $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors();

        $this->assertGuest();
    }
}
