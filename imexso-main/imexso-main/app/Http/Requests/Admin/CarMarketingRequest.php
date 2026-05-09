<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CarMarketingRequest extends FormRequest
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
            'limited_stock_enabled' => ['required', 'boolean'],
            'limited_stock_count' => ['nullable', 'integer', 'min:1', 'max:999'],
            'new_price_enabled' => ['required', 'boolean'],
            'new_price_amount' => ['nullable', 'numeric', 'min:0'],
            'promotion_enabled' => ['required', 'boolean'],
            'promotion_label' => ['nullable', 'string', 'max:255'],
            'badge_text' => ['nullable', 'string', 'max:100'],
            'sold_enabled' => ['required', 'boolean'],
            'sold_visible_days' => ['required', 'integer', 'min:1', 'max:365'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'limited_stock_count.min' => 'Stock count must be at least 1.',
            'new_price_amount.min' => 'Price must be a positive number.',
            'promotion_label.max' => 'Promotion label cannot exceed 255 characters.',
            'badge_text.max' => 'Badge text cannot exceed 100 characters.',
            'sold_visible_days.min' => 'Sold visibility must be at least 1 day.',
            'sold_visible_days.max' => 'Sold visibility cannot exceed 365 days.',
        ];
    }
}
