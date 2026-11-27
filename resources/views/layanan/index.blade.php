@extends('layouts.dashboard')

@section('title', 'Layanan')
@section('heading', 'Layanan')
@section('description', 'Kelola jenis dan tarif layanan laundry.')

@section('content')
    <section class="panel">
        <div class="section-head">
            <h2>Daftar layanan</h2>
            <a class="btn" href="{{ route('layanan.create') }}">Tambah layanan</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Unit</th>
                        <th>Tarif / unit</th>
                        <th>Estimasi</th>
                        <th>Status</th>
                        <th>Transaksi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($layanans as $layanan)
                        <tr>
                            <td>{{ $layanan->nama }}</td>
                            <td>{{ $layanan->unit }}</td>
                            <td>Rp{{ number_format((float) $layanan->price_per_unit, 0, ',', '.') }}</td>
                            <td>{{ $layanan->turnaround_hours }} jam</td>
                            <td>{{ $layanan->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                            <td>{{ number_format($layanan->pesanans_count) }}</td>
                            <td>
                                <div class="actions">
                                    <a class="btn secondary small" href="{{ route('layanan.edit', $layanan) }}">Edit</a>
                                    @if ($layanan->pesanans_count === 0)
                                        <form method="POST" action="{{ route('layanan.destroy', $layanan) }}" onsubmit="return confirm('Hapus layanan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn danger small" type="submit">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="7">Belum ada layanan yang dibuat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $layanans->links() }}</div>
    </section>
@endsection
