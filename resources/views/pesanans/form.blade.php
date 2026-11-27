@extends('layouts.dashboard')

@php($isEditing = $pesanan->exists)

@section('title', $isEditing ? 'Edit transaksi' : 'Buat transaksi')
@section('heading', $isEditing ? 'Edit transaksi' : 'Buat transaksi')
@section('description', $isEditing ? 'Kode transaksi: '.$pesanan->booking_code : 'Masukkan data pelanggan dan layanan laundry.')

@section('content')
    <section class="panel">
        <form method="POST" action="{{ $isEditing ? route('pesanans.update', $pesanan) : route('pesanans.store') }}">
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            <div class="form-grid">
                <div class="field">
                    <label for="customer_name">Nama pelanggan</label>
                    <input id="customer_name" name="customer_name" value="{{ old('customer_name', $pesanan->customer_name) }}" required maxlength="120">
                    @error('customer_name')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="customer_phone">Nomor telepon</label>
                    <input id="customer_phone" name="customer_phone" type="tel" value="{{ old('customer_phone', $pesanan->customer_phone) }}" required maxlength="25">
                    @error('customer_phone')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="service_id">Layanan</label>
                    <select id="service_id" name="service_id" required>
                        <option value="">Pilih layanan</option>
                        @foreach ($layanans as $layanan)
                            <option value="{{ $layanan->id }}" @selected((string) old('service_id', $pesanan->service_id) === (string) $layanan->id)>
                                {{ $layanan->nama }} — Rp{{ number_format((float) $layanan->price_per_unit, 0, ',', '.') }}/{{ $layanan->unit }}
                            </option>
                        @endforeach
                    </select>
                    @error('service_id')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="quantity">Jumlah ({{ $pesanan->layanan?->unit ?? 'unit' }})</label>
                    <input id="quantity" name="quantity" type="number" min="0.1" max="500" step="0.01" value="{{ old('quantity', $pesanan->quantity) }}" required>
                    @error('quantity')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="pickup_date">Tanggal pickup / penerimaan</label>
                    <input id="pickup_date" name="pickup_date" type="date" @unless($isEditing) min="{{ today()->toDateString() }}" @endunless value="{{ old('pickup_date', $pesanan->pickup_date?->toDateString()) }}" required>
                    @error('pickup_date')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="slot_id">Jadwal pickup (opsional)</label>
                    <select id="slot_id" name="slot_id">
                        <option value="">Tanpa jadwal khusus</option>
                        @foreach ($slots as $slot)
                            <option value="{{ $slot->id }}" @selected((string) old('slot_id', $pesanan->slot_id) === (string) $slot->id)>
                                {{ $slot->transaction_date->format('d M Y') }} · {{ substr($slot->start_time, 0, 5) }}–{{ substr($slot->end_time, 0, 5) }} · {{ $slot->booked_count }}/{{ $slot->max_bookings }}
                            </option>
                        @endforeach
                    </select>
                    @error('slot_id')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="fulfillment_type">Jenis layanan</label>
                    <select id="fulfillment_type" name="fulfillment_type" required>
                        <option value="drop_off" @selected(old('fulfillment_type', $pesanan->fulfillment_type) === 'drop_off')>Antar ke outlet</option>
                        <option value="pickup_delivery" @selected(old('fulfillment_type', $pesanan->fulfillment_type) === 'pickup_delivery')>Pickup dan antar</option>
                    </select>
                    @error('fulfillment_type')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field full">
                    <label for="pickup_address">Alamat pickup</label>
                    <input id="pickup_address" name="pickup_address" value="{{ old('pickup_address', $pesanan->pickup_address) }}" maxlength="500">
                    @error('pickup_address')<span class="error">{{ $message }}</span>@enderror
                </div>
                @if ($isEditing)
                    <div class="field">
                        <label for="status">Status transaksi</label>
                        <select id="status" name="status" required>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected(old('status', $pesanan->status) === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        @error('status')<span class="error">{{ $message }}</span>@enderror
                    </div>
                @endif
                <div class="field full">
                    <label for="notes">Catatan</label>
                    <textarea id="notes" name="notes" maxlength="1000">{{ old('notes', $pesanan->notes) }}</textarea>
                    @error('notes')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>

            @if ($isEditing)
                <p class="muted">Total saat ini: Rp{{ number_format((float) $pesanan->total_price, 0, ',', '.') }}</p>
            @endif
            <div class="actions" style="margin-top: 20px">
                <button class="btn" type="submit">{{ $isEditing ? 'Simpan perubahan' : 'Simpan transaksi' }}</button>
                <a class="btn secondary" href="{{ route('pesanans.index') }}">Batal</a>
            </div>
        </form>
    </section>
@endsection
