<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\CarPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CarPhoto>
 */
class CarPhotoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'car_id' => Car::factory(),
            'url' => 'https://photos.imexso.com/photos_vehicules/'.fake()->unique()->numerify('N######-######').'.jpg',
            'position' => fake()->numberBetween(0, 30),
        ];
    }
}
