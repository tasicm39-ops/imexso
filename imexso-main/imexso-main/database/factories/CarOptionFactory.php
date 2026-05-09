<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\CarOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CarOption>
 */
class CarOptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $options = [
            'peinture métallisée',
            'sièges chauffants',
            'toit ouvrant',
            'jantes alliage',
            'navigation',
            'caméra de recul',
            'pack cuir',
        ];

        return [
            'car_id' => Car::factory(),
            'name' => fake()->randomElement($options),
            'price' => fake()->randomFloat(2, 100, 3000),
        ];
    }
}
