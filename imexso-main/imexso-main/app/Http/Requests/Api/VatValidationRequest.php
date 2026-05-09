<?php

namespace App\Http\Requests\Api;

use App\Services\VatValidationService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VatValidationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'country_code' => ['required', 'string', 'size:2', Rule::in(VatValidationService::VAT_COUNTRIES)],
            'vat_number' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9]+$/'],
        ];
    }
}
