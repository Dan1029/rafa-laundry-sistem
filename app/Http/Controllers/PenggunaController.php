<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PenggunaController extends Controller
{
    public function index(): View
    {
        return view('pengguna.index', [
            'penggunas' => User::whereIn('role', ['admin', 'kasir'])
                ->withCount('pesanans')
                ->orderBy('role')
                ->orderBy('name')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('pengguna.form', [
            'pengguna' => new User,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'kasir'])],
        ]);

        User::create($validated);

        return redirect()->route('pengguna.index')->with('success', 'Akun staff berhasil dibuat.');
    }

    public function edit(User $pengguna): View
    {
        abort_unless(in_array($pengguna->role, ['admin', 'kasir'], true), 404);

        return view('pengguna.form', compact('pengguna'));
    }

    public function update(Request $request, User $pengguna): RedirectResponse
    {
        abort_unless(in_array($pengguna->role, ['admin', 'kasir'], true), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($pengguna->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'kasir'])],
        ]);

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        DB::transaction(function () use ($validated, $pengguna): void {
            $admins = User::query()->where('role', 'admin')->orderBy('id')->lockForUpdate()->get();
            $user = User::query()->lockForUpdate()->findOrFail($pengguna->id);

            if ($user->role === 'admin' && $validated['role'] !== 'admin' && $admins->count() <= 1) {
                throw ValidationException::withMessages([
                    'role' => 'Aplikasi harus memiliki setidaknya satu akun admin.',
                ]);
            }

            $user->update($validated);
        });

        return redirect()->route('pengguna.index')->with('success', 'Akun staff berhasil diperbarui.');
    }

    public function destroy(Request $request, User $pengguna): RedirectResponse
    {
        abort_unless(in_array($pengguna->role, ['admin', 'kasir'], true), 404);

        if ($request->user()->is($pengguna)) {
            return back()->with('error', 'Akun yang sedang digunakan tidak dapat dihapus.');
        }

        DB::transaction(function () use ($pengguna): void {
            $admins = User::query()->where('role', 'admin')->orderBy('id')->lockForUpdate()->get();
            $user = User::query()->lockForUpdate()->findOrFail($pengguna->id);

            if ($user->role === 'admin' && $admins->count() <= 1) {
                throw ValidationException::withMessages([
                    'pengguna' => 'Admin terakhir tidak dapat dihapus.',
                ]);
            }

            $user->delete();
        });

        return redirect()->route('pengguna.index')->with('success', 'Akun staff berhasil dihapus.');
    }
}
