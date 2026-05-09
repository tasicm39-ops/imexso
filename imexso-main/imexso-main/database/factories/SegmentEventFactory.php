<?php

namespace Database\Factories;

use App\Models\SegmentEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SegmentEvent>
 */
class SegmentEventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'event_type' => fake()->randomElement(['search', 'view_car', 'filter', 'page_view', 'login', 'register']),
            'payload' => fake()->optional(0.7)->passthrough(fn () => ['query' => fake()->word()]),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
