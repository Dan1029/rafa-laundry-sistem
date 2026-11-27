<?php

namespace Tests\Feature;

use App\Models\Layanan;
use App\Models\Pesanan;
use App\Models\SlotPickup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_management_and_cashier_is_limited_to_transactions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cashier = User::factory()->create(['role' => 'kasir']);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard');

        $this->get(route('layanan.index'))->assertOk();
        $this->get(route('layanan.create'))->assertOk();
        $this->get(route('slot-pickups.index'))->assertOk();
        $this->get(route('slot-pickups.create'))->assertOk();
        $this->get(route('pengguna.index'))->assertOk();
        $this->get(route('pengguna.create'))->assertOk();

        $this->actingAs($cashier)
            ->get(route('pesanans.index'))
            ->assertOk();
        $this->get(route('pesanans.create'))->assertOk();

        $this->get(route('layanan.index'))->assertForbidden();
        $this->get(route('slot-pickups.index'))->assertForbidden();
        $this->get(route('pengguna.index'))->assertForbidden();
    }

    public function test_admin_can_create_update_and_delete_a_layanan(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->post(route('layanan.store'), [
            'nama' => 'Cuci Express',
            'unit' => 'kg',
            'price_per_unit' => 12000,
            'turnaround_hours' => 24,
            'is_active' => 1,
        ])->assertRedirect(route('layanan.index'));

        $layanan = Layanan::where('nama', 'Cuci Express')->firstOrFail();
        $this->get(route('layanan.edit', $layanan))->assertOk();
        $this->assertDatabaseHas('layanans', [
            'id' => $layanan->id,
            'price_per_unit' => 12000,
            'is_active' => true,
        ]);

        $this->put(route('layanan.update', $layanan), [
            'nama' => 'Cuci Express Plus',
            'unit' => 'kg',
            'price_per_unit' => 15000,
            'turnaround_hours' => 12,
            'is_active' => 1,
        ])->assertRedirect(route('layanan.index'));

        $this->assertDatabaseHas('layanans', [
            'id' => $layanan->id,
            'nama' => 'Cuci Express Plus',
            'price_per_unit' => 15000,
        ]);

        $this->delete(route('layanan.destroy', $layanan))
            ->assertRedirect(route('layanan.index'));

        $this->assertDatabaseMissing('layanans', ['id' => $layanan->id]);
    }

    public function test_admin_can_create_update_and_delete_a_pickup_slot(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $date = today()->addDays(2)->toDateString();

        $this->post(route('slot-pickups.store'), [
            'transaction_date' => $date,
            'start_time' => '09:00',
            'end_time' => '11:00',
            'max_bookings' => 8,
        ])->assertRedirect(route('slot-pickups.index'));

        $slot = SlotPickup::whereDate('transaction_date', $date)->firstOrFail();
        $this->get(route('slot-pickups.edit', $slot))->assertOk();
        $this->assertDatabaseHas('transaksis', [
            'id' => $slot->id,
            'max_bookings' => 8,
        ]);

        $this->put(route('slot-pickups.update', $slot), [
            'transaction_date' => $date,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'max_bookings' => 10,
        ])->assertRedirect(route('slot-pickups.index'));

        $this->assertDatabaseHas('transaksis', [
            'id' => $slot->id,
            'max_bookings' => 10,
        ]);
        $this->assertSame('10:00', substr($slot->fresh()->start_time, 0, 5));

        $this->delete(route('slot-pickups.destroy', $slot))
            ->assertRedirect(route('slot-pickups.index'));

        $this->assertDatabaseMissing('transaksis', ['id' => $slot->id]);
    }

    public function test_cashier_can_create_update_cancel_and_delete_a_transaction(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $otherUser = User::factory()->create();
        $layanan = Layanan::factory()->create(['price_per_unit' => 5000]);
        $date = today()->addDays(2)->toDateString();
        $slot = SlotPickup::factory()->create([
            'transaction_date' => $date,
            'max_bookings' => 1,
        ]);

        $this->actingAs($cashier)
            ->post(route('pesanans.store'), $this->orderInput($layanan, $slot, $date) + [
                'status' => 'completed',
                'user_id' => $otherUser->id,
                'total_price' => 1,
                'booking_code' => 'FORGED',
            ])
            ->assertRedirect(route('pesanans.index'));

        $pesanan = Pesanan::firstOrFail();
        $this->assertDatabaseHas('pesanans', [
            'id' => $pesanan->id,
            'user_id' => $cashier->id,
            'status' => 'pending',
            'total_price' => 17500,
        ]);
        $this->assertSame(1, $slot->fresh()->booked_count);
        $this->assertNotSame('FORGED', $pesanan->booking_code);
        $this->get(route('pesanans.index'))->assertOk();
        $this->get(route('pesanans.edit', $pesanan))->assertOk();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->delete(route('layanan.destroy', $layanan))
            ->assertSessionHas('error');
        $this->delete(route('slot-pickups.destroy', $slot))
            ->assertSessionHasErrors('slot');
        $this->actingAs($cashier);

        $this->put(route('pesanans.update', $pesanan), $this->orderInput($layanan, $slot, $date) + [
            'status' => 'processing',
        ])->assertRedirect(route('pesanans.index'));

        $this->assertDatabaseHas('pesanans', [
            'id' => $pesanan->id,
            'booking_code' => $pesanan->booking_code,
            'status' => 'processing',
        ]);
        $this->assertSame(1, $slot->fresh()->booked_count);

        $this->put(route('pesanans.update', $pesanan), $this->orderInput($layanan, $slot, $date) + [
            'status' => 'cancelled',
        ])->assertRedirect(route('pesanans.index'));

        $this->assertDatabaseHas('pesanans', [
            'id' => $pesanan->id,
            'status' => 'cancelled',
        ]);
        $this->assertSame(0, $slot->fresh()->booked_count);

        $this->delete(route('pesanans.destroy', $pesanan))
            ->assertRedirect(route('pesanans.index'));

        $this->assertDatabaseMissing('pesanans', ['id' => $pesanan->id]);
    }

    public function test_cashier_cannot_overbook_a_pickup_slot(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $layanan = Layanan::factory()->create();
        $date = today()->addDays(2)->toDateString();
        $slot = SlotPickup::factory()->create([
            'transaction_date' => $date,
            'max_bookings' => 1,
        ]);
        $this->actingAs($cashier);
        $this->get(route('pesanans.create'))->assertOk();

        $this->post(route('pesanans.store'), $this->orderInput($layanan, $slot, $date))
            ->assertRedirect(route('pesanans.index'));

        $this->from(route('pesanans.create'))
            ->post(route('pesanans.store'), $this->orderInput($layanan, $slot, $date))
            ->assertRedirect(route('pesanans.create'))
            ->assertSessionHasErrors('slot_id');

        $this->assertDatabaseCount('pesanans', 1);
        $this->assertSame(1, $slot->fresh()->booked_count);
    }

    public function test_admin_can_manage_staff_but_cannot_remove_the_last_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $this->post(route('pengguna.store'), [
            'name' => 'Kasir Utama',
            'email' => 'kasir-utama@example.com',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
            'role' => 'kasir',
        ])->assertRedirect(route('pengguna.index'));

        $cashier = User::where('email', 'kasir-utama@example.com')->firstOrFail();
        $this->get(route('pengguna.edit', $cashier))->assertOk();
        $this->assertDatabaseHas('users', [
            'id' => $cashier->id,
            'role' => 'kasir',
        ]);

        $this->put(route('pengguna.update', $cashier), [
            'name' => 'Kasir Siang',
            'email' => 'kasir-siang@example.com',
            'role' => 'kasir',
        ])->assertRedirect(route('pengguna.index'));

        $this->assertDatabaseHas('users', [
            'id' => $cashier->id,
            'name' => 'Kasir Siang',
            'email' => 'kasir-siang@example.com',
        ]);

        $this->put(route('pengguna.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'kasir',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => 'admin',
        ]);

        $this->delete(route('pengguna.destroy', $admin))
            ->assertSessionHas('error');

        $this->delete(route('pengguna.destroy', $cashier))
            ->assertRedirect(route('pengguna.index'));

        $this->assertDatabaseMissing('users', ['id' => $cashier->id]);
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => 'admin',
        ]);
    }

    public function test_customer_dashboard_only_shows_their_own_transactions(): void
    {
        $customer = User::factory()->create(['role' => 'pelanggan']);
        $otherCustomer = User::factory()->create(['role' => 'pelanggan']);
        $layanan = Layanan::factory()->create();

        Pesanan::factory()->for($customer)->for($layanan, 'layanan')->create([
            'customer_name' => 'Pesanan Saya',
        ]);
        Pesanan::factory()->for($otherCustomer)->for($layanan, 'layanan')->create([
            'customer_name' => 'Pesanan Orang Lain',
        ]);

        $this->actingAs($customer)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pesanan Saya')
            ->assertDontSee('Pesanan Orang Lain');

        $this->get(route('pesanans.index'))->assertForbidden();
    }

    /**
     * @return array<string, mixed>
     */
    private function orderInput(Layanan $layanan, SlotPickup $slot, string $date): array
    {
        return [
            'service_id' => $layanan->id,
            'slot_id' => $slot->id,
            'customer_name' => 'Pelanggan Laundry',
            'customer_phone' => '081234567890',
            'fulfillment_type' => 'pickup_delivery',
            'pickup_address' => 'Jalan Melati 10',
            'pickup_date' => $date,
            'quantity' => 3.5,
            'notes' => '',
        ];
    }
}
