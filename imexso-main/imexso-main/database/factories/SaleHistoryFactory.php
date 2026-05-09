<?php

namespace Database\Factories;

use App\Models\SaleHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleHistory>
 */
class SaleHistoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_produit' => strtoupper(fake()->bothify('?######')),
            'vin' => fake()->regexify('[A-Z0-9]{17}'),
            'condition_type' => fake()->randomElement(['VN', 'VO']),
            'make' => fake()->randomElement(['CITROEN', 'PEUGEOT', 'RENAULT', 'BMW', 'MERCEDES']),
            'model' => fake()->word(),
            'trim_level' => fake()->optional(0.8)->randomElement(['ACTIVE', 'SHINE', 'GT', 'SPORT']),
            'radio_code' => fake()->optional(0.3)->numerify('####%%0'),
            'color' => fake()->optional(0.9)->colorName(),
            'location' => fake()->randomElement(['Livre', 'Stock', 'En transit']),
            'price' => (string) fake()->randomFloat(2, 5000, 80000),
            'tax_type' => fake()->randomElement(['HTVA', 'TTC']),
            'client_id' => 'C'.fake()->numberBetween(100, 9999),
            'status' => fake()->randomElement(['LIVRE', 'EN COMMANDE', 'ANNULE']),
            'order_date' => fake()->optional(0.8)->date(),
            'documents' => null,
            'invoices' => null,
            'to_unblock' => fake()->randomElement(['OUI', 'NON']),
            'assignment_count' => fake()->numberBetween(0, 3),
            'assignment_1' => null,
            'assignment_2' => null,
            'days_available' => fake()->numberBetween(0, 30),
            'lot_number' => null,
        ];
    }
}
