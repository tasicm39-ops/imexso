<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SegmentEventRequest extends FormRequest
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
            'event_type' => ['required', 'string', 'in:search,view_car,filter,page_view,login,register,favourite,save_search,add_to_cart,remove_from_cart'],
            'payload' => ['sometimes', 'array'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'event_type.required' => 'The event type is required.',
            'event_type.in' => 'The event type must be one of: search, view_car, filter, page_view, login, register, favourite, save_search, add_to_cart, remove_from_cart.',
            'payload.array' => 'The payload must be an array.',
        ];
    }
}
