<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_admin_and_cashier_accounts_idempotently(): void
    {
        config([
            'seeders.admin.password' => 'secure-admin-password',
            'seeders.kasir.password' => 'secure-kasir-password',
        ]);

        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseHas('users', [
            'name' => 'Admin Laundry',
            'email' => 'admin@laundry.test',
            'role' => 'admin',
        ]);
        $this->assertDatabaseHas('users', [
            'name' => 'Kasir Laundry',
            'email' => 'kasir@laundry.test',
            'role' => 'kasir',
        ]);

        $this->assertTrue(Hash::check('secure-admin-password', User::where('email', 'admin@laundry.test')->value('password')));
        $this->assertTrue(Hash::check('secure-kasir-password', User::where('email', 'kasir@laundry.test')->value('password')));
    }
}
