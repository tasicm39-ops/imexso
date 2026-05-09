<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SavedSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'filters' => ['required', 'array'],
            'filters.make' => ['nullable', 'string', 'max:100'],
            'filters.model' => ['nullable', 'string', 'max:100'],
            'filters.fuel_type' => ['nullable', 'string', 'max:50'],
            'filters.condition_type' => ['nullable', 'string', 'max:10'],
            'filters.category' => ['nullable', 'string', 'max:50'],
            'filters.gearbox' => ['nullable', 'string', 'max:500'],
            'filters.color' => ['nullable', 'string', 'max:500'],
            'filters.search' => ['nullable', 'string', 'max:255'],
            'filters.price_min' => ['nullable', 'numeric', 'min:0'],
            'filters.price_max' => ['nullable', 'numeric', 'min:0'],
            'filters.year_min' => ['nullable', 'integer', 'min:1900'],
            'filters.year_max' => ['nullable', 'integer', 'min:1900'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'filters.required' => 'Search filters are required.',
            'filters.array' => 'Search filters must be an array.',
        ];
    }
}
