<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LayananController extends Controller
{
    public function index(): View
    {
        return view('layanan.index', [
            'layanans' => Layanan::withCount('pesanans')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('layanan.form', [
            'layanan' => new Layanan,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Layanan::create($this->validatedData($request));

        return redirect()->route('layanan.index')->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function edit(Layanan $layanan): View
    {
        return view('layanan.form', compact('layanan'));
    }

    public function update(Request $request, Layanan $layanan): RedirectResponse
    {
        $layanan->update($this->validatedData($request));

        return redirect()->route('layanan.index')->with('success', 'Layanan berhasil diperbarui.');
    }

    public function destroy(Layanan $layanan): RedirectResponse
    {
        $deleted = DB::transaction(function () use ($layanan): bool {
            $lockedLayanan = Layanan::query()->lockForUpdate()->findOrFail($layanan->id);

            if ($lockedLayanan->pesanans()->exists()) {
                return false;
            }

            $lockedLayanan->delete();

            return true;
        });

        if (! $deleted) {
            return back()->with('error', 'Layanan yang sudah dipakai pada transaksi tidak dapat dihapus. Nonaktifkan layanan tersebut.');
        }

        return redirect()->route('layanan.index')->with('success', 'Layanan berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'in:kg,liter,pcs'],
            'price_per_unit' => ['required', 'numeric', 'min:0', 'max:19999999.99'],
            'turnaround_hours' => ['required', 'integer', 'min:1', 'max:8760'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
