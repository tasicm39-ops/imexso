<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Services\RecaptchaService;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    public function __construct(private RecaptchaService $recaptchaService) {}

    /**
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $this->rejectHoneypot($input);
        $this->verifyRecaptcha($input);

        Validator::make($input, $this->registrationRules())->validate();

        $name = trim(($input['first_name'] ?? '').' '.($input['last_name'] ?? ''));

        return User::create([
            'name' => $name ?: $input['last_name'],
            'first_name' => $input['first_name'] ?? null,
            'last_name' => $input['last_name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'company_name' => $input['company_name'] ?? null,
            'vat_number' => $input['vat_number'] ?? null,
            'phone' => $input['phone'] ?? null,
            'fax' => $input['fax'] ?? null,
            'address' => $input['address'] ?? null,
            'postal_code' => $input['postal_code'] ?? null,
            'city' => $input['city'] ?? null,
            'country' => $input['country'] ?? null,
            'website' => $input['website'] ?? null,
            'language' => $input['language'] ?? null,
            'is_professional' => (bool) ($input['is_professional'] ?? false),
            'is_validated' => false,
            'gdpr_accepted_at' => ! empty($input['gdpr_accepted']) ? now() : null,
        ]);
    }

    /**
     * @return array<string, array<int, Rule|string>>
     */
    private function registrationRules(): array
    {
        return [
            'last_name' => ['required', 'string', 'max:100'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::default()],
            'company_name' => ['nullable', 'string', 'max:200'],
            'vat_number' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:20'],
            'fax' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:200'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'city' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'string', 'max:250'],
            'language' => ['nullable', 'string', 'max:5'],
            'is_professional' => ['nullable', 'boolean'],
            'gdpr_accepted' => ['required', 'accepted'],
        ];
    }

    /**
     * @param  array<string, string>  $input
     */
    private function rejectHoneypot(array $input): void
    {
        if (! empty($input['website_url'])) {
            throw ValidationException::withMessages([
                'email' => ['Registration failed.'],
            ]);
        }
    }

    /**
     * @param  array<string, string>  $input
     */
    private function verifyRecaptcha(array $input): void
    {
        if (! $this->recaptchaService->verify($input['recaptcha_token'] ?? null)) {
            throw ValidationException::withMessages([
                'recaptcha_token' => ['reCAPTCHA verification failed. Please try again.'],
            ]);
        }
    }
}
