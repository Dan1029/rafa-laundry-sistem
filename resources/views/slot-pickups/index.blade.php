@extends('layouts.dashboard')

@section('title', 'Jadwal pickup')
@section('heading', 'Jadwal pickup')
@section('description', 'Atur ketersediaan jadwal dan kuota pickup.')

@section('content')
    <section class="panel">
        <div class="section-head">
            <h2>Daftar jadwal</h2>
            <a class="btn" href="{{ route('slot-pickups.create') }}">Tambah jadwal</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Tanggal</th><th>Waktu</th><th>Booking</th><th>Sisa kuota</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($slots as $slot)
                        <tr>
                            <td>{{ $slot->transaction_date->format('d M Y') }}</td>
                            <td>{{ substr($slot->start_time, 0, 5) }}–{{ substr($slot->end_time, 0, 5) }}</td>
                            <td>{{ number_format($slot->booked_count) }} / {{ number_format($slot->max_bookings) }}</td>
                            <td>{{ number_format(max(0, $slot->max_bookings - $slot->booked_count)) }}</td>
                            <td>
                                <div class="actions">
                                    <a class="btn secondary small" href="{{ route('slot-pickups.edit', $slot) }}">Edit</a>
                                    @if ($slot->active_pesanans_count === 0)
                                        <form method="POST" action="{{ route('slot-pickups.destroy', $slot) }}" onsubmit="return confirm('Hapus jadwal ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn danger small" type="submit">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="5">Belum ada jadwal pickup.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $slots->links() }}</div>
    </section>
@endsection
