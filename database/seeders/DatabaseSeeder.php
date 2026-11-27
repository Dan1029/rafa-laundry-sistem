<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminPassword = config('seeders.admin.password');
        $kasirPassword = config('seeders.kasir.password');

        if (! is_string($adminPassword) || mb_strlen($adminPassword) < 8) {
            throw new RuntimeException('Set SEED_ADMIN_PASSWORD to at least 8 characters before running the seeder.');
        }

        if (! is_string($kasirPassword) || mb_strlen($kasirPassword) < 8) {
            throw new RuntimeException('Set SEED_KASIR_PASSWORD to at least 8 characters before running the seeder.');
        }

        User::updateOrCreate(
            ['email' => config('seeders.admin.email')],
            [
                'name' => 'Admin Laundry',
                'password' => $adminPassword,
                'role' => 'admin',
            ],
        );

        User::updateOrCreate(
            ['email' => config('seeders.kasir.email')],
            [
                'name' => 'Kasir Laundry',
                'password' => $kasirPassword,
                'role' => 'kasir',
            ],
        );
    }
}
