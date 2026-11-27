<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\Pesanan;
use App\Models\SlotPickup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PesananController extends Controller
{
    public function index(): View
    {
        return view('pesanans.index', [
            'pesanans' => Pesanan::with(['layanan', 'slotPickup', 'user'])
                ->latest()
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('pesanans.form', [
            'pesanan' => new Pesanan,
            'layanans' => Layanan::where('is_active', true)->orderBy('nama')->get(),
            'slots' => $this->availableSlots(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        $pesanan = DB::transaction(function () use ($validated, $request): Pesanan {
            $layanan = Layanan::query()->lockForUpdate()->findOrFail($validated['service_id']);
            $slotId = $validated['slot_id'] ?? null;
            $slot = $slotId === null ? null : $this->lockSlots([$slotId])->get($slotId);

            if (! $layanan->is_active) {
                throw ValidationException::withMessages([
                    'service_id' => 'Layanan yang dipilih sedang tidak aktif.',
                ]);
            }

            $this->validateSlot($slot, $validated['pickup_date'] ?? null, $slotId !== null);

            if ($slot !== null && $slot->booked_count >= $slot->max_bookings) {
                throw ValidationException::withMessages([
                    'slot_id' => 'Kuota pickup pada jadwal yang dipilih sudah penuh.',
                ]);
            }

            $pesanan = Pesanan::create($this->orderAttributes(
                $validated,
                $request->user()->id,
                $layanan,
                'pending',
            ));

            $slot?->increment('booked_count');

            return $pesanan;
        });

        return redirect()->route('pesanans.index')->with('success', "Transaksi {$pesanan->booking_code} berhasil dibuat.");
    }

    public function edit(Pesanan $pesanan): View
    {
        return view('pesanans.form', [
            'pesanan' => $pesanan,
            'layanans' => Layanan::orderBy('nama')->get(),
            'slots' => $this->availableSlots($pesanan),
            'statuses' => $this->statuses(),
        ]);
    }

    public function update(Request $request, Pesanan $pesanan): RedirectResponse
    {
        $validated = $this->validatedData($request, true);

        DB::transaction(function () use ($validated, $pesanan): void {
            $lockedPesanan = Pesanan::query()->lockForUpdate()->findOrFail($pesanan->id);
            $layanan = Layanan::query()->lockForUpdate()->findOrFail($validated['service_id']);
            $oldSlotId = $lockedPesanan->slot_id;
            $newSlotId = $validated['slot_id'] ?? null;
            $slots = $this->lockSlots([$oldSlotId, $newSlotId]);
            $oldSlot = $oldSlotId === null ? null : $slots->get($oldSlotId);
            $newSlot = $newSlotId === null ? null : $slots->get($newSlotId);

            $this->validateSlot($newSlot, $validated['pickup_date'] ?? null, $newSlotId !== null);

            $oldSlotIsCounted = $oldSlot !== null && $lockedPesanan->status !== 'cancelled';
            $newSlotIsCounted = $newSlot !== null && $validated['status'] !== 'cancelled';
            $mustIncrementNewSlot = $newSlotIsCounted
                && (! $oldSlotIsCounted || $oldSlotId !== $newSlotId);

            if (! $layanan->is_active && $layanan->id !== $lockedPesanan->service_id) {
                throw ValidationException::withMessages([
                    'service_id' => 'Layanan yang dipilih sedang tidak aktif.',
                ]);
            }

            if ($mustIncrementNewSlot && $newSlot->booked_count >= $newSlot->max_bookings) {
                throw ValidationException::withMessages([
                    'slot_id' => 'Kuota pickup pada jadwal yang dipilih sudah penuh.',
                ]);
            }

            if ($oldSlotIsCounted && (! $newSlotIsCounted || $oldSlotId !== $newSlotId) && $oldSlot->booked_count > 0) {
                $oldSlot->decrement('booked_count');
            }

            if ($mustIncrementNewSlot) {
                $newSlot->increment('booked_count');
            }

            $lockedPesanan->update($this->orderAttributes(
                $validated,
                $lockedPesanan->user_id,
                $layanan,
                $validated['status'],
                $lockedPesanan->booking_code,
            ));
        });

        return redirect()->route('pesanans.index')->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(Pesanan $pesanan): RedirectResponse
    {
        DB::transaction(function () use ($pesanan): void {
            $lockedPesanan = Pesanan::query()->lockForUpdate()->findOrFail($pesanan->id);
            $slot = $lockedPesanan->slot_id === null
                ? null
                : SlotPickup::query()->lockForUpdate()->findOrFail($lockedPesanan->slot_id);

            if ($slot !== null && $lockedPesanan->status !== 'cancelled' && $slot->booked_count > 0) {
                $slot->decrement('booked_count');
            }

            $lockedPesanan->delete();
        });

        return redirect()->route('pesanans.index')->with('success', 'Transaksi berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, bool $updating = false): array
    {
        $serviceRule = $updating
            ? Rule::exists('layanans', 'id')
            : Rule::exists('layanans', 'id')->where('is_active', true);

        $validated = $request->validate([
            'service_id' => ['required', $serviceRule],
            'slot_id' => ['nullable', 'integer', 'exists:transaksis,id'],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'min:8', 'max:25'],
            'fulfillment_type' => ['required', Rule::in(['pickup_delivery', 'drop_off'])],
            'pickup_address' => [
                Rule::requiredIf($request->input('fulfillment_type') === 'pickup_delivery'),
                'nullable',
                'string',
                'max:500',
            ],
            'pickup_date' => ['required', 'date', ...($updating ? [] : ['after_or_equal:today'])],
            'quantity' => ['required', 'numeric', 'min:0.1', 'max:500'],
            'status' => [$updating ? 'required' : 'sometimes', Rule::in($this->statuses())],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['slot_id'] = empty($validated['slot_id']) ? null : (int) $validated['slot_id'];

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function orderAttributes(
        array $validated,
        ?int $userId,
        Layanan $layanan,
        string $status,
        ?string $bookingCode = null,
    ): array {
        $quantity = (float) $validated['quantity'];

        return [
            'booking_code' => $bookingCode ?? $this->generateBookingCode(),
            'user_id' => $userId,
            'service_id' => $layanan->id,
            'slot_id' => $validated['slot_id'] ?? null,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'pickup_address' => $validated['pickup_address'] ?? null,
            'pickup_date' => $validated['pickup_date'],
            'fulfillment_type' => $validated['fulfillment_type'],
            'quantity' => $quantity,
            'total_price' => round($quantity * (float) $layanan->price_per_unit, 2),
            'status' => $status,
            'notes' => $validated['notes'] ?? null,
        ];
    }

    /**
     * @param  array<int, int|string|null>  $slotIds
     * @return Collection<int, SlotPickup>
     */
    private function lockSlots(array $slotIds): Collection
    {
        $slotIds = array_values(array_unique(array_filter($slotIds)));

        if ($slotIds === []) {
            return collect();
        }

        return SlotPickup::query()
            ->whereIn('id', $slotIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    private function validateSlot(?SlotPickup $slot, ?string $pickupDate, bool $isRequired): void
    {
        if ($isRequired && $slot === null) {
            throw ValidationException::withMessages([
                'slot_id' => 'Jadwal pickup yang dipilih sudah tidak tersedia.',
            ]);
        }

        if ($slot !== null && $slot->transaction_date->toDateString() !== $pickupDate) {
            throw ValidationException::withMessages([
                'slot_id' => 'Tanggal jadwal pickup harus sama dengan tanggal transaksi.',
            ]);
        }
    }

    /**
     * @return array<int, string>
     */
    private function statuses(): array
    {
        return ['pending', 'processing', 'completed', 'cancelled'];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, SlotPickup>
     */
    private function availableSlots(?Pesanan $pesanan = null): \Illuminate\Database\Eloquent\Collection
    {
        return SlotPickup::query()
            ->where(function ($query) use ($pesanan): void {
                $query->whereDate('transaction_date', '>=', today());

                if ($pesanan?->slot_id !== null) {
                    $query->orWhere('id', $pesanan->slot_id);
                }
            })
            ->where(function ($query) use ($pesanan): void {
                $query->whereColumn('booked_count', '<', 'max_bookings');

                if ($pesanan?->slot_id !== null) {
                    $query->orWhere('id', $pesanan->slot_id);
                }
            })
            ->orderBy('transaction_date')
            ->orderBy('start_time')
            ->get();
    }

    private function generateBookingCode(): string
    {
        do {
            $code = 'LND-'.now()->format('Ymd').'-'.strtoupper(Str::random(4));
        } while (Pesanan::where('booking_code', $code)->exists());

        return $code;
    }
}
