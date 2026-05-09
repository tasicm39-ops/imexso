<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'car_id' => Car::factory(),
            'client_id' => 'C'.fake()->numerify('####'),
            'car_reference' => fake()->bothify('??-####'),
            'status' => 'pending',
            'notes' => null,
            'confirmed_at' => null,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (): array => [
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }
}
