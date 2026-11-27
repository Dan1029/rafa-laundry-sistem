@extends('layouts.dashboard')

@php($isEditing = $layanan->exists)

@section('title', $isEditing ? 'Edit layanan' : 'Tambah layanan')
@section('heading', $isEditing ? 'Edit layanan' : 'Tambah layanan')
@section('description', 'Isi detail layanan laundry.')

@section('content')
    <section class="panel">
        <form method="POST" action="{{ $isEditing ? route('layanan.update', $layanan) : route('layanan.store') }}">
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            <div class="form-grid">
                <div class="field">
                    <label for="nama">Nama layanan</label>
                    <input id="nama" name="nama" value="{{ old('nama', $layanan->nama) }}" required maxlength="255">
                    @error('nama')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="unit">Unit perhitungan</label>
                    <select id="unit" name="unit" required>
                        @foreach (['kg', 'liter', 'pcs'] as $unit)
                            <option value="{{ $unit }}" @selected(old('unit', $layanan->unit) === $unit)>{{ strtoupper($unit) }}</option>
                        @endforeach
                    </select>
                    @error('unit')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="price_per_unit">Tarif per unit (Rp)</label>
                    <input id="price_per_unit" name="price_per_unit" type="number" min="0" step="0.01" value="{{ old('price_per_unit', $layanan->price_per_unit) }}" required>
                    @error('price_per_unit')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="turnaround_hours">Estimasi pengerjaan (jam)</label>
                    <input id="turnaround_hours" name="turnaround_hours" type="number" min="1" max="8760" value="{{ old('turnaround_hours', $layanan->turnaround_hours) }}" required>
                    @error('turnaround_hours')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field full">
                    <label class="check" for="is_active">
                        <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $isEditing ? $layanan->is_active : true))>
                        Layanan aktif dan dapat dipilih untuk transaksi
                    </label>
                    @error('is_active')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="actions" style="margin-top: 20px">
                <button class="btn" type="submit">{{ $isEditing ? 'Simpan perubahan' : 'Simpan layanan' }}</button>
                <a class="btn secondary" href="{{ route('layanan.index') }}">Batal</a>
            </div>
        </form>
    </section>
@endsection
