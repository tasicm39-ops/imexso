<?php

namespace Tests\Feature\Api;

use App\Models\ContactSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_stores_submission(): void
    {
        Mail::fake();

        $response = $this->postJson(route('api.contact.store'), [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean@example.com',
            'phone' => '+32 2 000 00 00',
            'message' => 'Bonjour, je souhaite plus d’informations.',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Your message has been sent successfully.');

        $this->assertDatabaseHas('contact_submissions', [
            'email' => 'jean@example.com',
            'first_name' => 'Jean',
        ]);

        $this->assertSame(1, ContactSubmission::query()->count());
    }

    public function test_contact_form_validates_required_fields(): void
    {
        $this->postJson(route('api.contact.store'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'phone', 'message']);
    }
}
