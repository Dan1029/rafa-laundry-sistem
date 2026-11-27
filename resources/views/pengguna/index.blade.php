@extends('layouts.dashboard')

@section('title', 'Akun staff')
@section('heading', 'Akun staff')
@section('description', 'Kelola akun admin dan kasir.')

@section('content')
    <section class="panel">
        <div class="section-head">
            <h2>Daftar staff</h2>
            <a class="btn" href="{{ route('pengguna.create') }}">Tambah akun</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Nama</th><th>Email</th><th>Role</th><th>Transaksi dicatat</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($penggunas as $pengguna)
                        <tr>
                            <td>{{ $pengguna->name }} @if (auth()->user()->is($pengguna)) <span class="muted">(Anda)</span> @endif</td>
                            <td>{{ $pengguna->email }}</td>
                            <td>{{ ucfirst($pengguna->role) }}</td>
                            <td>{{ number_format($pengguna->pesanans_count) }}</td>
                            <td>
                                <div class="actions">
                                    <a class="btn secondary small" href="{{ route('pengguna.edit', $pengguna) }}">Edit</a>
                                    @unless (auth()->user()->is($pengguna))
                                        <form method="POST" action="{{ route('pengguna.destroy', $pengguna) }}" onsubmit="return confirm('Hapus akun {{ $pengguna->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn danger small" type="submit">Hapus</button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="5">Belum ada akun staff.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $penggunas->links() }}</div>
    </section>
@endsection
