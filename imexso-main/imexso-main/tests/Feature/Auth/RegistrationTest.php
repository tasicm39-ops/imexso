<?php

namespace Tests\Feature\Auth;

use App\Services\RecaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Mockery\MockInterface;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyFeature(Features::registration());

        $this->mock(RecaptchaService::class, function (MockInterface $mock) {
            $mock->shouldReceive('verify')->andReturn(true);
        });
    }

    public function test_registration_screen_redirects_to_frontend()
    {
        $response = $this->get(route('register'));

        $response->assertRedirect('/register');
    }

    public function test_new_users_can_register_via_api()
    {
        $response = $this->postJson(route('register.store'), [
            'last_name' => 'Doe',
            'first_name' => 'John',
            'email' => 'john@example.com',
            'password' => 'password1',
            'company_name' => 'ACME Corp',
            'vat_number' => 'BE0827449689',
            'phone' => '+32 2 669 28 31',
            'country' => 'Belgium',
            'gdpr_accepted' => true,
            'recaptcha_token' => 'valid-token',
        ]);

        $this->assertAuthenticated();
        $response->assertCreated();

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'last_name' => 'Doe',
            'first_name' => 'John',
            'name' => 'John Doe',
            'company_name' => 'ACME Corp',
            'vat_number' => 'BE0827449689',
            'country' => 'Belgium',
            'is_validated' => false,
            'is_professional' => false,
        ]);
    }

    public function test_registration_with_professional_flag()
    {
        $response = $this->postJson(route('register.store'), [
            'last_name' => 'Pro',
            'email' => 'pro@example.com',
            'password' => 'password1',
            'is_professional' => true,
            'gdpr_accepted' => true,
            'recaptcha_token' => 'valid-token',
        ]);

        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'pro@example.com',
            'is_professional' => true,
        ]);
    }

    public function test_registration_sets_gdpr_accepted_at()
    {
        $this->postJson(route('register.store'), [
            'last_name' => 'GDPR',
            'email' => 'gdpr@example.com',
            'password' => 'password1',
            'gdpr_accepted' => true,
            'recaptcha_token' => 'valid-token',
        ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'gdpr@example.com',
            'gdpr_accepted_at' => null,
        ]);
    }

    public function test_registration_requires_gdpr_acceptance()
    {
        $response = $this->postJson(route('register.store'), [
            'last_name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'password1',
            'recaptcha_token' => 'valid-token',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('gdpr_accepted');
    }

    public function test_registration_requires_email()
    {
        $response = $this->postJson(route('register.store'), [
            'last_name' => 'Test',
            'password' => 'password1',
            'gdpr_accepted' => true,
            'recaptcha_token' => 'valid-token',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('email');
    }

    public function test_registration_requires_last_name()
    {
        $response = $this->postJson(route('register.store'), [
            'email' => 'test@example.com',
            'password' => 'password1',
            'gdpr_accepted' => true,
            'recaptcha_token' => 'valid-token',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('last_name');
    }

    public function test_honeypot_rejects_bots()
    {
        $response = $this->postJson(route('register.store'), [
            'last_name' => 'Bot',
            'email' => 'bot@example.com',
            'password' => 'password1',
            'gdpr_accepted' => true,
            'recaptcha_token' => 'valid-token',
            'website_url' => 'http://spam.com',
        ]);

        $response->assertUnprocessable();
        $this->assertGuest();
    }

    public function test_recaptcha_failure_rejects_registration()
    {
        $this->mock(RecaptchaService::class, function (MockInterface $mock) {
            $mock->shouldReceive('verify')->andReturn(false);
        });

        $response = $this->postJson(route('register.store'), [
            'last_name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'password1',
            'gdpr_accepted' => true,
            'recaptcha_token' => 'invalid-token',
        ]);

        $response->assertUnprocessable();
        $this->assertGuest();
    }

    public function test_registration_prevents_duplicate_emails()
    {
        $this->postJson(route('register.store'), [
            'last_name' => 'First',
            'email' => 'duplicate@example.com',
            'password' => 'password1',
            'gdpr_accepted' => true,
            'recaptcha_token' => 'valid-token',
        ]);

        $this->post(route('logout'));

        $response = $this->postJson(route('register.store'), [
            'last_name' => 'Second',
            'email' => 'duplicate@example.com',
            'password' => 'password1',
            'gdpr_accepted' => true,
            'recaptcha_token' => 'valid-token',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('email');
    }
}
