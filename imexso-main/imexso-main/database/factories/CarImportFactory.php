<?php

namespace Database\Factories;

use App\Models\CarImport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CarImport>
 */
class CarImportFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'filename' => 'cars.xml',
            'total_items_in_xml' => fake()->numberBetween(100, 500),
            'new_count' => 0,
            'updated_count' => 0,
            'sold_count' => 0,
            'unchanged_count' => 0,
            'status' => 'pending',
            'error_message' => null,
            'started_at' => null,
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        $total = fake()->numberBetween(100, 500);
        $newCount = fake()->numberBetween(0, 50);
        $soldCount = fake()->numberBetween(0, 20);
        $updatedCount = fake()->numberBetween(0, $total - $newCount);

        return $this->state(fn (array $attributes) => [
            'total_items_in_xml' => $total,
            'new_count' => $newCount,
            'updated_count' => $updatedCount,
            'sold_count' => $soldCount,
            'unchanged_count' => $total - $newCount - $updatedCount,
            'status' => 'completed',
            'started_at' => now()->subMinutes(2),
            'completed_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'error_message' => 'Import failed: invalid XML structure',
            'started_at' => now()->subMinutes(1),
            'completed_at' => now(),
        ]);
    }
}
