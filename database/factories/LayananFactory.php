<?php

namespace Database\Factories;

use App\Models\Layanan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Layanan>
 */
class LayananFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->unique()->words(2, true),
            'unit' => fake()->randomElement(['kg', 'liter', 'pcs']),
            'price_per_unit' => fake()->randomFloat(2, 1000, 100000),
            'turnaround_hours' => fake()->randomElement([24, 48, 72]),
            'is_active' => true,
        ];
    }
}
