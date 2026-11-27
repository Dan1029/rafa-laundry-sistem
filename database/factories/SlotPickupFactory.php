<?php

namespace Database\Factories;

use App\Models\SlotPickup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SlotPickup>
 */
class SlotPickupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaction_date' => today()->addDays(fake()->numberBetween(1, 30)),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'max_bookings' => 10,
        ];
    }
}
