<?php

namespace Database\Factories;

use App\Models\ApiClient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApiClient>
 */
class ApiClientFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'contact_email' => fake()->unique()->companyEmail(),
            'description' => fake()->optional(0.7)->sentence(),
            'is_active' => true,
            'country' => fake()->optional(0.5)->country(),
            'allowed_abilities' => ['cars:read'],
            'rate_limit_per_minute' => fake()->optional(0.5)->randomElement([60, 120, 300]),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withAllAbilities(): static
    {
        return $this->state(fn (array $attributes) => [
            'allowed_abilities' => ['*'],
        ]);
    }
}
