<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SendOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'margin_type' => ['nullable', 'string', 'in:percentage,fixed'],
            'margin_amount' => ['nullable', 'numeric', 'min:0'],
            'vat_rate' => ['required', 'numeric', 'in:0,19,20,21,22'],
            'validity_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'price_excl_vat' => ['required', 'numeric', 'min:0'],
            'price_incl_vat' => ['required', 'numeric', 'min:0'],
            'client_name' => ['required', 'string', 'max:255'],
            'client_email' => ['required', 'email', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
            'setup_fees' => ['nullable', 'numeric', 'min:0'],
            'registration_fees' => ['nullable', 'numeric', 'min:0'],
            'admin_fees' => ['nullable', 'numeric', 'min:0'],
            'bonus_malus' => ['nullable', 'numeric'],
            'ww_fees' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'client_name.required' => 'The client name is required.',
            'client_email.required' => 'The client email is required.',
            'client_email.email' => 'Please provide a valid email address.',
            'vat_rate.required' => 'The VAT rate is required.',
            'vat_rate.in' => 'The VAT rate must be 0, 19, 20, 21, or 22.',
            'price_excl_vat.required' => 'The price excluding VAT is required.',
            'price_incl_vat.required' => 'The price including VAT is required.',
        ];
    }
}
