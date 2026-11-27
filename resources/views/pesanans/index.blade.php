@extends('layouts.dashboard')

@section('title', 'Transaksi')
@section('heading', 'Transaksi')
@section('description', 'Catat dan pantau proses laundry pelanggan.')

@section('content')
    <section class="panel">
        <div class="section-head">
            <h2>Daftar transaksi</h2>
            <a class="btn" href="{{ route('pesanans.create') }}">Buat transaksi</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Kode</th><th>Pelanggan</th><th>Layanan</th><th>Pickup</th><th>Berat / qty</th><th>Status</th><th>Total</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($pesanans as $pesanan)
                        <tr>
                            <td>{{ $pesanan->booking_code }}</td>
                            <td>{{ $pesanan->customer_name }}<br><span class="muted">{{ $pesanan->customer_phone }}</span></td>
                            <td>{{ $pesanan->layanan->nama }}</td>
                            <td>
                                {{ $pesanan->pickup_date->format('d M Y') }}
                                @if ($pesanan->slotPickup)
                                    <br><span class="muted">{{ substr($pesanan->slotPickup->start_time, 0, 5) }}–{{ substr($pesanan->slotPickup->end_time, 0, 5) }}</span>
                                @endif
                            </td>
                            <td>{{ number_format((float) $pesanan->quantity, 2) }} {{ $pesanan->layanan->unit }}</td>
                            <td>{{ ucfirst($pesanan->status) }}</td>
                            <td>Rp{{ number_format((float) $pesanan->total_price, 0, ',', '.') }}</td>
                            <td>
                                <div class="actions">
                                    <a class="btn secondary small" href="{{ route('pesanans.edit', $pesanan) }}">Detail / edit</a>
                                    <form method="POST" action="{{ route('pesanans.destroy', $pesanan) }}" onsubmit="return confirm('Hapus transaksi {{ $pesanan->booking_code }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn danger small" type="submit">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="8">Belum ada transaksi laundry.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $pesanans->links() }}</div>
    </section>
@endsection
