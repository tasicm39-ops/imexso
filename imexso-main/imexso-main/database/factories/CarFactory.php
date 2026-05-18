<?php

namespace Database\Factories;

use App\Models\Car;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Car>
 */
class CarFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $makes = ['CITROEN', 'PEUGEOT', 'RENAULT', 'BMW', 'AUDI', 'PORSCHE', 'MERCEDES'];
        $fuelTypes = ['ESSENCE', 'DIESEL', 'HEV/ESSENCE', 'MHEV/ESSENCE', 'ESSENCE/LPG', 'PHEV/ESSENCE'];
        $gearboxes = ['EAT8', 'bva', 'PDK', '6 v', 'BVA', '5 v', 'EDC', 'DCT7'];
        $categories = ['Berline', 'Coupe', 'SUV 4x2', 'Citadine', 'Cabriolet', 'SUV 4X4', 'Monospace'];
        $euroStandards = ['EURO6', 'EURO5', 'EURO6.d'];

        return [
            'id' => fake()->unique()->numberBetween(100_000, 999_999),
            'id_produit' => strtoupper(fake()->bothify('?######')),
            'vin' => fake()->optional(0.8)->regexify('[A-Z0-9]{17}'),
            'make' => fake()->randomElement($makes),
            'model' => fake()->word(),
            'trim_level' => fake()->randomElement(['PLUS', 'FEEL', 'SHINE', 'GT', 'SPORT']),
            'fuel_type' => fake()->randomElement($fuelTypes),
            'engine_displacement' => fake()->numberBetween(900, 5200),
            'horsepower' => fake()->numberBetween(63, 525),
            'engine_code' => fake()->randomElement(['ESS', 'DW10', 'EB2', 'THP', 'HDI']),
            'weight' => fake()->optional(0.9)->numberBetween(999, 2210),
            'manufacturing_year' => fake()->optional(0.6)->numberBetween(2015, 2025),
            'gearbox' => fake()->randomElement($gearboxes),
            'gearbox_code' => (string) fake()->numberBetween(1, 5),
            'color' => fake()->colorName(),
            'color_code' => fake()->optional(0.8)->numberBetween(1, 20),
            'mileage' => fake()->numberBetween(10, 185000),
            'co2' => fake()->optional(0.1)->numberBetween(0, 332),
            'co2_wltp' => fake()->optional(0.9)->numberBetween(0, 231),
            'wltp_electric_range' => fake()->optional(0.05)->numberBetween(50, 80),
            'euro_standard' => fake()->optional(0.9)->randomElement($euroStandards),
            'professional_price' => fake()->randomFloat(2, 12250, 63500),
            'vat_type' => fake()->randomElement(['HTVA', 'TTC']),
            'status' => [
                'fr' => 'En stock',
                'de' => 'Auf Lager',
                'en' => 'On Stock',
                'nl' => 'Stock',
            ],
            'registration_date' => fake()->optional(0.8)->date(),
            'retention_date' => null,
            'warranty_start_date' => null,
            'doors' => fake()->optional(0.85)->randomElement([2, 3, 4, 5]),
            'category' => fake()->optional(0.85)->randomElement($categories),
            'catalogue_base_price_excl_vat' => fake()->optional(0.9)->randomFloat(2, 16000, 150000),
            'catalogue_total_price_excl_vat' => fake()->optional(0.9)->randomFloat(2, 16000, 160000),
            'catalogue_price_incl_vat' => fake()->optional(0.9)->randomFloat(2, 20000, 190000),
            'used_car_fees' => fake()->randomFloat(2, 0, 1000),
            'used_car_fees_detail' => null,
            'condition_type' => fake()->randomElement(['VN', 'VO']),
            'france_discount' => fake()->randomFloat(2, 0, 60),
            'catalogue_model_name' => fake()->optional(0.9)->sentence(4),
            'is_clearance' => fake()->boolean(30),
            'argus_price' => null,
            'previous_price' => null,
            'user_type' => fake()->randomElement(['BE', 'INT']),
            'is_new' => fake()->boolean(10),
            'catalogue_remark' => null,
            'creation_date' => fake()->date(),
            'private_price_incl_vat' => fake()->randomFloat(2, 0, 84900),
            'publication_codes' => fake()->randomElement(['SSL//02', 'N/A//N/A']),
            'tags' => fake()->sentence(5),
            'main_equipment_codes' => [fake()->numberBetween(1, 15)],
            'carlab' => fake()->boolean(50),
            'carpass_url' => null,
            'ecological_penalty' => null,
            'vehicle_condition' => null,
            'auction_data' => null,
            'stock_level' => fake()->optional(0.7)->randomElement([0, 1, 2]),
            'okcars' => false,
            'ecarlux' => false,
            'publish_platforms' => fake()->optional(0.5)->randomElements(
                ['aramis', 'carconnex', 'starterre'],
                fake()->numberBetween(1, 3),
            ),
            'excluded_countries' => null,
            'supplementary_equipment' => [
                'fr' => [fake()->sentence(3), fake()->sentence(3)],
                'en' => [fake()->sentence(3), fake()->sentence(3)],
            ],
            'standard_equipment' => [
                'fr' => [fake()->sentence(3), fake()->sentence(3)],
                'en' => [fake()->sentence(3), fake()->sentence(3)],
            ],
            'sync_status' => 'active',
            'stock_status' => 'AVAILABLE',
            'last_synced_at' => now(),
        ];
    }

    public function sold(): static
    {
        return $this->state(fn (array $attributes) => [
            'sync_status' => 'sold',
            'stock_status' => 'SOLD',
        ]);
    }

    public function newCar(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition_type' => 'VN',
            'mileage' => fake()->numberBetween(10, 100),
        ]);
    }

    public function usedCar(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition_type' => 'VO',
            'mileage' => fake()->numberBetween(5000, 185000),
            'used_car_fees' => fake()->randomFloat(2, 100, 1000),
        ]);
    }

    public function withExcludedCountries(): static
    {
        return $this->state(fn (array $attributes) => [
            'excluded_countries' => fake()->randomElements(
                ['Belgium', 'Luxembourg', 'France', 'Italy', 'Spain'],
                fake()->numberBetween(1, 3),
            ),
        ]);
    }
}
