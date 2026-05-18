<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\CarMarketing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CarMarketing>
 */
class CarMarketingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'car_id' => Car::factory(),
            'limited_stock_enabled' => false,
            'limited_stock_count' => null,
            'new_price_enabled' => false,
            'new_price_amount' => null,
            'promotion_enabled' => false,
            'promotion_label' => null,
            'badge_text' => null,
            'sold_enabled' => false,
            'sold_visible_days' => 5,
            'sold_marked_at' => null,
            'is_active' => true,
        ];
    }

    public function withLimitedStock(int $count = 3): static
    {
        return $this->state(fn (array $attributes) => [
            'limited_stock_enabled' => true,
            'limited_stock_count' => $count,
        ]);
    }

    public function withNewPrice(float $amount = 15000.00): static
    {
        return $this->state(fn (array $attributes) => [
            'new_price_enabled' => true,
            'new_price_amount' => $amount,
        ]);
    }

    public function withPromotion(string $label = 'Big Promotion!'): static
    {
        return $this->state(fn (array $attributes) => [
            'promotion_enabled' => true,
            'promotion_label' => $label,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
