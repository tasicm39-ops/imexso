<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AnnouncementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after_or_equal:starts_at',
        ];

        foreach (config('locales.supported') as $locale) {
            $rules['title_'.$locale] = 'nullable|string|max:255';
            $rules['body_'.$locale] = 'nullable|string';
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $data = $validator->getData();
            if (! $this->hasFrenchOrEnglishContent($data)) {
                $validator->errors()->add(
                    'title_fr',
                    'Provide a title and body in French or in English. Other languages are optional and reuse that text when left empty.'
                );
            }
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function hasFrenchOrEnglishContent(array $data): bool
    {
        $frTitle = trim((string) ($data['title_fr'] ?? ''));
        $frBody = trim((string) ($data['body_fr'] ?? ''));
        $enTitle = trim((string) ($data['title_en'] ?? ''));
        $enBody = trim((string) ($data['body_en'] ?? ''));

        $frComplete = $frTitle !== '' && $frBody !== '';
        $enComplete = $enTitle !== '' && $enBody !== '';

        return $frComplete || $enComplete;
    }

    /**
     * @return array<string, array{title: string, body: string}>
     */
    public function normalizedTranslations(): array
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();
        $primary = $this->primaryTexts($validated);

        $out = [];
        foreach (config('locales.supported') as $locale) {
            $title = trim((string) ($validated['title_'.$locale] ?? ''));
            $body = trim((string) ($validated['body_'.$locale] ?? ''));
            if ($title === '' || $body === '') {
                $title = $primary['title'];
                $body = $primary['body'];
            }
            $out[$locale] = [
                'title' => $title,
                'body' => $body,
            ];
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{title: string, body: string}
     */
    private function primaryTexts(array $validated): array
    {
        $frTitle = trim((string) ($validated['title_fr'] ?? ''));
        $frBody = trim((string) ($validated['body_fr'] ?? ''));
        $enTitle = trim((string) ($validated['title_en'] ?? ''));
        $enBody = trim((string) ($validated['body_en'] ?? ''));

        if ($frTitle !== '' && $frBody !== '') {
            return ['title' => $frTitle, 'body' => $frBody];
        }

        return ['title' => $enTitle, 'body' => $enBody];
    }
}
