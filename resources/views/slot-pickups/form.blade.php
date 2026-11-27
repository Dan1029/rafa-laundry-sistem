@extends('layouts.dashboard')

@php($isEditing = $slot->exists)

@section('title', $isEditing ? 'Edit jadwal pickup' : 'Tambah jadwal pickup')
@section('heading', $isEditing ? 'Edit jadwal pickup' : 'Tambah jadwal pickup')
@section('description', 'Tentukan tanggal, jam, dan kuota booking.')

@section('content')
    <section class="panel">
        <form method="POST" action="{{ $isEditing ? route('slot-pickups.update', $slot) : route('slot-pickups.store') }}">
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            <div class="form-grid">
                <div class="field">
                    <label for="transaction_date">Tanggal pickup</label>
                    <input id="transaction_date" name="transaction_date" type="date" min="{{ today()->toDateString() }}" value="{{ old('transaction_date', $slot->transaction_date?->toDateString()) }}" required>
                    @error('transaction_date')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="max_bookings">Kuota booking</label>
                    <input id="max_bookings" name="max_bookings" type="number" min="1" max="10000" value="{{ old('max_bookings', $slot->max_bookings ?: 10) }}" required>
                    @error('max_bookings')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="start_time">Jam mulai</label>
                    <input id="start_time" name="start_time" type="time" value="{{ old('start_time', substr((string) $slot->start_time, 0, 5)) }}" required>
                    @error('start_time')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="end_time">Jam selesai</label>
                    <input id="end_time" name="end_time" type="time" value="{{ old('end_time', substr((string) $slot->end_time, 0, 5)) }}" required>
                    @error('end_time')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="actions" style="margin-top: 20px">
                <button class="btn" type="submit">{{ $isEditing ? 'Simpan perubahan' : 'Simpan jadwal' }}</button>
                <a class="btn secondary" href="{{ route('slot-pickups.index') }}">Batal</a>
            </div>
        </form>
    </section>
@endsection
