@extends('layouts.dashboard')

@php($isEditing = $pengguna->exists)

@section('title', $isEditing ? 'Edit akun staff' : 'Tambah akun staff')
@section('heading', $isEditing ? 'Edit akun staff' : 'Tambah akun staff')
@section('description', 'Atur identitas dan akses akun staff.')

@section('content')
    <section class="panel">
        <form method="POST" action="{{ $isEditing ? route('pengguna.update', $pengguna) : route('pengguna.store') }}">
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            <div class="form-grid">
                <div class="field">
                    <label for="name">Nama</label>
                    <input id="name" name="name" value="{{ old('name', $pengguna->name) }}" required maxlength="255">
                    @error('name')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $pengguna->email) }}" required maxlength="255">
                    @error('email')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        @foreach (['admin', 'kasir'] as $role)
                            <option value="{{ $role }}" @selected(old('role', $pengguna->role ?: 'kasir') === $role)>{{ ucfirst($role) }}</option>
                        @endforeach
                    </select>
                    @error('role')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="password">{{ $isEditing ? 'Password baru (opsional)' : 'Password' }}</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" @required(! $isEditing)>
                    @error('password')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="password_confirmation">Konfirmasi password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" @required(! $isEditing)>
                </div>
            </div>

            <div class="actions" style="margin-top: 20px">
                <button class="btn" type="submit">{{ $isEditing ? 'Simpan perubahan' : 'Buat akun' }}</button>
                <a class="btn secondary" href="{{ route('pengguna.index') }}">Batal</a>
            </div>
        </form>
    </section>
@endsection
