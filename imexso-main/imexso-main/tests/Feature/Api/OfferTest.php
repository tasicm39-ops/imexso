<?php

namespace Tests\Feature\Api;

use App\Mail\OfferMail;
use App\Models\Car;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfferTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Car $car;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->validated()->create([
            'company_name' => 'Test Company',
            'vat_number' => 'BE0123456789',
            'address' => '123 Test Street',
            'postal_code' => '1000',
            'city' => 'Brussels',
            'phone' => '+32 123 456 789',
        ]);

        $this->car = Car::factory()->create([
            'professional_price' => 20000,
        ]);
    }

    private function validOfferData(array $overrides = []): array
    {
        return array_merge([
            'margin_type' => 'percentage',
            'margin_amount' => 10,
            'vat_rate' => 21,
            'validity_days' => 30,
            'price_excl_vat' => 22000,
            'price_incl_vat' => 26620,
            'client_name' => 'John Doe',
            'client_email' => 'john@example.com',
            'message' => 'Please find our best offer.',
            'setup_fees' => 250,
            'registration_fees' => 150,
            'admin_fees' => 100,
            'bonus_malus' => 0,
            'ww_fees' => 0,
        ], $overrides);
    }

    public function test_guests_cannot_generate_offer_pdf(): void
    {
        $this->postJson(route('api.offers.pdf', $this->car), $this->validOfferData())
            ->assertUnauthorized();
    }

    public function test_guests_cannot_send_offer_email(): void
    {
        $this->postJson(route('api.offers.email', $this->car), $this->validOfferData())
            ->assertUnauthorized();
    }

    public function test_unvalidated_users_cannot_generate_offer_pdf(): void
    {
        $unvalidated = User::factory()->create(['is_validated' => false]);

        $this->actingAs($unvalidated)
            ->postJson(route('api.offers.pdf', $this->car), $this->validOfferData())
            ->assertForbidden();
    }

    public function test_unvalidated_users_cannot_send_offer_email(): void
    {
        $unvalidated = User::factory()->create(['is_validated' => false]);

        $this->actingAs($unvalidated)
            ->postJson(route('api.offers.email', $this->car), $this->validOfferData())
            ->assertForbidden();
    }

    public function test_validated_user_can_generate_offer_pdf(): void
    {
        Storage::fake('local');

        $response = $this->actingAs($this->user)
            ->postJson(route('api.offers.pdf', $this->car), $this->validOfferData());

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');

        $this->assertDatabaseHas('offers', [
            'user_id' => $this->user->id,
            'car_id' => $this->car->id,
            'client_name' => 'John Doe',
            'client_email' => 'john@example.com',
            'delivery_type' => 'pdf',
            'margin_type' => 'percentage',
            'vat_rate' => 21,
        ]);
    }

    public function test_validated_user_can_send_offer_email(): void
    {
        Mail::fake();
        Storage::fake('local');

        $response = $this->actingAs($this->user)
            ->postJson(route('api.offers.email', $this->car), $this->validOfferData());

        $response->assertOk()
            ->assertJson(['message' => 'Offer sent successfully.']);

        Mail::assertSent(OfferMail::class, function (OfferMail $mail) {
            return $mail->hasTo('john@example.com');
        });

        Mail::assertSent(OfferMail::class, function (OfferMail $mail) {
            return $mail->hasTo($this->user->email);
        });

        $this->assertDatabaseHas('offers', [
            'user_id' => $this->user->id,
            'car_id' => $this->car->id,
            'delivery_type' => 'email',
        ]);
    }

    public function test_offer_pdf_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('api.offers.pdf', $this->car), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'vat_rate',
                'price_excl_vat',
                'price_incl_vat',
                'client_name',
                'client_email',
            ]);
    }

    public function test_offer_validates_email_format(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('api.offers.pdf', $this->car), $this->validOfferData([
                'client_email' => 'not-an-email',
            ]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['client_email']);
    }

    public function test_offer_validates_vat_rate(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('api.offers.pdf', $this->car), $this->validOfferData([
                'vat_rate' => 25,
            ]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['vat_rate']);
    }

    public function test_offer_validates_margin_type(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('api.offers.pdf', $this->car), $this->validOfferData([
                'margin_type' => 'invalid',
            ]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['margin_type']);
    }

    public function test_offer_with_fixed_margin_is_persisted(): void
    {
        Storage::fake('local');

        $this->actingAs($this->user)
            ->postJson(route('api.offers.pdf', $this->car), $this->validOfferData([
                'margin_type' => 'fixed',
                'margin_amount' => 1500,
                'price_excl_vat' => 21500,
                'price_incl_vat' => 26015,
            ]));

        $this->assertDatabaseHas('offers', [
            'margin_type' => 'fixed',
            'margin_amount' => 1500,
            'price_excl_vat' => 21500,
        ]);
    }

    public function test_offer_with_no_margin_is_persisted(): void
    {
        Storage::fake('local');

        $this->actingAs($this->user)
            ->postJson(route('api.offers.pdf', $this->car), $this->validOfferData([
                'margin_type' => null,
                'margin_amount' => null,
                'price_excl_vat' => 20000,
                'price_incl_vat' => 24200,
            ]));

        $this->assertDatabaseHas('offers', [
            'margin_type' => null,
            'price_excl_vat' => 20000,
        ]);
    }

    public function test_offer_with_ttc_vat_rate_is_persisted(): void
    {
        Storage::fake('local');

        $this->actingAs($this->user)
            ->postJson(route('api.offers.pdf', $this->car), $this->validOfferData([
                'vat_rate' => 0,
                'price_excl_vat' => 22000,
                'price_incl_vat' => 22000,
            ]));

        $this->assertDatabaseHas('offers', [
            'vat_rate' => 0,
            'price_incl_vat' => 22000,
        ]);
    }

    public function test_offer_persists_all_fee_fields(): void
    {
        Storage::fake('local');

        $this->actingAs($this->user)
            ->postJson(route('api.offers.pdf', $this->car), $this->validOfferData([
                'setup_fees' => 350,
                'registration_fees' => 200,
                'admin_fees' => 150,
                'bonus_malus' => 500,
                'ww_fees' => 75,
            ]));

        $this->assertDatabaseHas('offers', [
            'setup_fees' => 350,
            'registration_fees' => 200,
            'admin_fees' => 150,
            'bonus_malus' => 500,
            'ww_fees' => 75,
        ]);
    }

    public function test_offer_pdf_path_is_stored(): void
    {
        Storage::fake('local');

        $this->actingAs($this->user)
            ->postJson(route('api.offers.pdf', $this->car), $this->validOfferData());

        $offer = Offer::query()->latest()->first();
        $this->assertNotNull($offer->pdf_path);
        $this->assertStringStartsWith('offers/', $offer->pdf_path);
    }

    public function test_offer_pdf_does_not_embed_vehicle_vin(): void
    {
        Storage::fake('local');

        $this->car->update(['vin' => 'UNIQUEOFFERVIN1234567']);

        $response = $this->actingAs($this->user)
            ->postJson(route('api.offers.pdf', $this->car->fresh()), $this->validOfferData());

        $response->assertOk();
        $this->assertStringNotContainsString(
            'UNIQUEOFFERVIN1234567',
            $response->getContent()
        );
    }
}
