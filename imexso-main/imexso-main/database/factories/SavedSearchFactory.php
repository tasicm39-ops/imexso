<?php

namespace Database\Factories;

use App\Models\SavedSearch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavedSearch>
 */
class SavedSearchFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->sentence(3),
            'filters' => [
                'make' => fake()->randomElement(['BMW', 'AUDI', 'CITROEN']),
                'fuel_type' => fake()->randomElement(['ESSENCE', 'DIESEL']),
                'price_min' => fake()->numberBetween(5000, 15000),
                'price_max' => fake()->numberBetween(20000, 60000),
            ],
        ];
    }
}
