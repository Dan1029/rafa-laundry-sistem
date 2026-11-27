<?php

namespace App\Http\Controllers;

use App\Models\SlotPickup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SlotPickupController extends Controller
{
    public function index(): View
    {
        return view('slot-pickups.index', [
            'slots' => SlotPickup::withCount([
                'pesanans as active_pesanans_count' => fn ($query) => $query->where('status', '!=', 'cancelled'),
            ])
                ->orderByDesc('transaction_date')
                ->orderBy('start_time')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('slot-pickups.form', [
            'slot' => new SlotPickup,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        SlotPickup::create($this->validatedData($request));

        return redirect()->route('slot-pickups.index')->with('success', 'Jadwal pickup berhasil ditambahkan.');
    }

    public function edit(SlotPickup $slot_pickup): View
    {
        return view('slot-pickups.form', [
            'slot' => $slot_pickup,
        ]);
    }

    public function update(Request $request, SlotPickup $slot_pickup): RedirectResponse
    {
        $validated = $this->validatedData($request);

        DB::transaction(function () use ($validated, $slot_pickup): void {
            $slot = SlotPickup::query()->lockForUpdate()->findOrFail($slot_pickup->id);

            if ($validated['max_bookings'] < $slot->booked_count) {
                throw ValidationException::withMessages([
                    'max_bookings' => 'Kuota tidak boleh kurang dari jumlah booking yang sudah masuk.',
                ]);
            }

            if (
                $slot->pesanans()->where('status', '!=', 'cancelled')->exists()
                && (
                    $slot->transaction_date->toDateString() !== $validated['transaction_date']
                    || substr($slot->start_time, 0, 5) !== $validated['start_time']
                    || substr($slot->end_time, 0, 5) !== $validated['end_time']
                )
            ) {
                throw ValidationException::withMessages([
                    'transaction_date' => 'Tanggal atau jam tidak dapat diubah karena jadwal sudah memiliki transaksi aktif.',
                ]);
            }

            $slot->update($validated);
        });

        return redirect()->route('slot-pickups.index')->with('success', 'Jadwal pickup berhasil diperbarui.');
    }

    public function destroy(SlotPickup $slot_pickup): RedirectResponse
    {
        DB::transaction(function () use ($slot_pickup): void {
            $slot = SlotPickup::query()->lockForUpdate()->findOrFail($slot_pickup->id);

            if ($slot->pesanans()->where('status', '!=', 'cancelled')->exists()) {
                throw ValidationException::withMessages([
                    'slot' => 'Jadwal yang memiliki transaksi aktif tidak dapat dihapus.',
                ]);
            }

            $slot->delete();
        });

        return redirect()->route('slot-pickups.index')->with('success', 'Jadwal pickup berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'transaction_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'max_bookings' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);
    }
}
