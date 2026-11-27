@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('description', 'Ringkasan aktivitas laundry Anda.')

@section('content')
    <section class="stats" aria-label="Ringkasan transaksi">
        <article class="stat">
            <span>Total transaksi</span>
            <strong>{{ number_format($totalOrders) }}</strong>
        </article>
        <article class="stat">
            <span>Transaksi hari ini</span>
            <strong>{{ number_format($todayOrders) }}</strong>
        </article>
        <article class="stat">
            <span>Perlu diproses</span>
            <strong>{{ number_format($pendingOrders) }}</strong>
        </article>
        @if ($isStaff)
            <article class="stat">
                <span>Pendapatan hari ini</span>
                <strong>Rp{{ number_format((float) $todayRevenue, 0, ',', '.') }}</strong>
            </article>
            @if (auth()->user()->role === 'admin')
                <article class="stat">
                    <span>Jenis layanan</span>
                    <strong>{{ number_format($serviceCount) }}</strong>
                </article>
                <article class="stat">
                    <span>Jadwal pickup mendatang</span>
                    <strong>{{ number_format($slotCount) }}</strong>
                </article>
            @endif
        @endif
    </section>

    <section class="panel">
        <div class="section-head">
            <div>
                <h2>Transaksi terbaru</h2>
                <span class="muted">Aktivitas laundry terbaru.</span>
            </div>
            @if ($isStaff)
                <a class="btn" href="{{ route('pesanans.create') }}">Buat transaksi</a>
            @endif
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Pelanggan</th>
                        <th>Layanan</th>
                        <th>Status</th>
                        <th>Total</th>
                        @if ($isStaff)
                            <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($latestOrders as $pesanan)
                        <tr>
                            <td>{{ $pesanan->booking_code }}</td>
                            <td>{{ $pesanan->customer_name }}</td>
                            <td>{{ $pesanan->layanan->nama }}</td>
                            <td>{{ ucfirst($pesanan->status) }}</td>
                            <td>Rp{{ number_format((float) $pesanan->total_price, 0, ',', '.') }}</td>
                            @if ($isStaff)
                                <td><a class="btn secondary small" href="{{ route('pesanans.edit', $pesanan) }}">Detail / edit</a></td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td class="empty" colspan="{{ $isStaff ? 6 : 5 }}">Belum ada transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
