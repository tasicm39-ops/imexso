<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Offer>
 */
class OfferFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $marginType = fake()->randomElement(['percentage', 'fixed', null]);
        $basePrice = fake()->randomFloat(2, 5000, 50000);
        $marginAmount = $marginType === 'percentage'
            ? fake()->randomFloat(2, 1, 25)
            : ($marginType === 'fixed' ? fake()->randomFloat(2, 100, 5000) : null);

        $priceExclVat = $basePrice;
        if ($marginType === 'percentage' && $marginAmount) {
            $priceExclVat += ($basePrice * $marginAmount / 100);
        } elseif ($marginType === 'fixed' && $marginAmount) {
            $priceExclVat += $marginAmount;
        }

        $vatRate = fake()->randomElement([0, 19, 20, 21, 22]);
        $priceInclVat = $vatRate > 0
            ? $priceExclVat + ($priceExclVat * $vatRate / 100)
            : $priceExclVat;

        return [
            'user_id' => User::factory(),
            'car_id' => Car::factory(),
            'margin_type' => $marginType,
            'margin_amount' => $marginAmount,
            'vat_rate' => $vatRate,
            'validity_days' => fake()->optional()->numberBetween(7, 90),
            'price_excl_vat' => round($priceExclVat, 2),
            'price_incl_vat' => round($priceInclVat, 2),
            'client_name' => fake()->name(),
            'client_email' => fake()->safeEmail(),
            'message' => fake()->optional()->paragraph(),
            'setup_fees' => fake()->randomFloat(2, 0, 500),
            'registration_fees' => fake()->randomFloat(2, 0, 300),
            'admin_fees' => fake()->randomFloat(2, 0, 200),
            'bonus_malus' => fake()->randomFloat(2, 0, 1000),
            'ww_fees' => fake()->randomFloat(2, 0, 100),
            'delivery_type' => fake()->randomElement(['pdf', 'email']),
            'pdf_path' => null,
        ];
    }
}
