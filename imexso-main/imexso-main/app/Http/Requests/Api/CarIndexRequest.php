<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CarIndexRequest extends FormRequest
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
            'make' => ['sometimes', 'string', 'max:100'],
            'model' => ['sometimes', 'string', 'max:100'],
            'fuel_type' => ['sometimes', 'string', 'max:50'],
            'condition_type' => ['sometimes', 'string', 'max:10'],
            'sync_status' => ['sometimes', 'string', 'max:20'],
            'user_type' => ['sometimes', 'string', 'max:15'],
            'category' => ['sometimes', 'string', 'max:50'],
            'gearbox' => ['sometimes', 'string', 'max:500'],
            'color' => ['sometimes', 'string', 'max:500'],
            'search' => ['sometimes', 'string', 'max:255'],
            'horsepower_min' => ['sometimes', 'integer', 'min:0'],
            'horsepower_max' => ['sometimes', 'integer', 'min:0'],
            'price_min' => ['sometimes', 'numeric', 'min:0'],
            'price_max' => ['sometimes', 'numeric', 'min:0'],
            'mileage_min' => ['sometimes', 'integer', 'min:0'],
            'mileage_max' => ['sometimes', 'integer', 'min:0'],
            'year_min' => ['sometimes', 'integer', 'min:1900'],
            'year_max' => ['sometimes', 'integer', 'min:1900'],
            'country' => ['sometimes', 'string', 'max:50'],
            'is_new' => ['sometimes', 'boolean'],
            'has_promotion' => ['sometimes', 'boolean'],
            'sort' => ['sometimes', 'string', 'in:price_asc,price_desc,year_desc,mileage_asc,newest'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
