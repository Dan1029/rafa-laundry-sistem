<?php

namespace Database\Factories;

use App\Models\Layanan;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Pesanan>
 */
class PesananFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_code' => 'LND-'.now()->format('Ymd').'-'.strtoupper(Str::random(4)),
            'user_id' => User::factory(),
            'service_id' => Layanan::factory(),
            'slot_id' => null,
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->numerify('08##########'),
            'pickup_address' => null,
            'pickup_date' => today()->addDays(2),
            'fulfillment_type' => 'drop_off',
            'quantity' => 2,
            'total_price' => 10000,
            'status' => 'pending',
            'notes' => null,
        ];
    }
}
