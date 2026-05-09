<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\CarCartItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CarCartItem>
 */
class CarCartItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'car_id' => Car::factory(),
        ];
    }
}
