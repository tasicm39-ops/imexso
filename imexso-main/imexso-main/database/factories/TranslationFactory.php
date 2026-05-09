<?php

namespace Database\Factories;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Translation>
 */
class TranslationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group' => fake()->randomElement(['ui', 'filters', 'car']),
            'key' => fake()->unique()->slug(2),
            'locale' => fake()->randomElement(['en', 'fr', 'de', 'nl']),
            'value' => fake()->sentence(3),
        ];
    }
}
