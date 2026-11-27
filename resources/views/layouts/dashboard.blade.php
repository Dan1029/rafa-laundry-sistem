@php($user = auth()->user())
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Laundry') }}</title>
    <style>
        :root { color-scheme: light; font-family: ui-sans-serif, system-ui, sans-serif; color: #1b1b18; background: #f5f5f2; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; }
        a { color: inherit; }
        .shell { display: flex; min-height: 100vh; }
        .sidebar { display: flex; flex: 0 0 230px; flex-direction: column; gap: 24px; padding: 24px 16px; color: #EDEDEC; background: #161615; }
        .brand { margin: 0; font-size: 19px; font-weight: 700; }
        .role { color: #a7a69f; font-size: 12px; text-transform: uppercase; letter-spacing: .08em; }
        .nav { display: grid; gap: 6px; }
        .nav a { padding: 10px 12px; color: #d7d6d0; text-decoration: none; border-radius: 6px; }
        .nav a:hover, .nav a.active { color: #fff; background: #353530; }
        .sidebar-bottom { margin-top: auto; display: grid; gap: 12px; }
        .sidebar-bottom p { margin: 0; overflow-wrap: anywhere; color: #d7d6d0; font-size: 13px; }
        .main { flex: 1; min-width: 0; padding: 28px clamp(16px, 4vw, 48px); }
        .topbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 24px; }
        h1 { margin: 0; font-size: clamp(24px, 3vw, 32px); }
        h2 { margin: 0; font-size: 18px; }
        .muted { color: #74736d; }
        .panel, .stat { border: 1px solid #e6e5df; border-radius: 10px; background: #fff; }
        .panel { padding: 20px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 14px; margin-bottom: 20px; }
        .stat { padding: 18px; }
        .stat span { display: block; color: #74736d; font-size: 13px; }
        .stat strong { display: block; margin-top: 8px; font-size: 26px; }
        .section-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
        .btn, button.btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 13px; border: 1px solid #1b1b18; border-radius: 6px; color: #fff; background: #1b1b18; font: inherit; font-size: 14px; text-decoration: none; cursor: pointer; }
        .btn.secondary { color: #1b1b18; background: #fff; }
        .btn.danger { border-color: #b42318; background: #b42318; }
        .btn.small { padding: 6px 9px; font-size: 12px; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
        th, td { padding: 12px 10px; border-bottom: 1px solid #eeede8; vertical-align: middle; }
        th { color: #74736d; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
        tbody tr:last-child td { border-bottom: 0; }
        .actions { display: flex; flex-wrap: wrap; align-items: center; gap: 7px; }
        .actions form { margin: 0; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .field { display: grid; align-content: start; gap: 6px; }
        .field.full { grid-column: 1 / -1; }
        label { font-size: 14px; font-weight: 600; }
        input, select, textarea { width: 100%; padding: 10px 11px; border: 1px solid #d5d4ce; border-radius: 6px; color: #1b1b18; background: #fff; font: inherit; }
        textarea { min-height: 92px; resize: vertical; }
        input[type="checkbox"] { width: auto; }
        .check { display: flex; align-items: center; gap: 8px; }
        .error { color: #b42318; font-size: 13px; }
        .alert { margin-bottom: 16px; padding: 12px 14px; border-radius: 7px; }
        .alert.success { color: #155724; background: #d9f2df; }
        .alert.error { color: #842029; background: #f8d7da; }
        .empty { padding: 24px 12px; color: #74736d; text-align: center; }
        .pagination { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-top: 16px; font-size: 14px; }
        @media (max-width: 760px) {
            .shell { display: block; }
            .sidebar { gap: 14px; padding: 16px; }
            .nav { grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); }
            .sidebar-bottom { grid-template-columns: 1fr auto; align-items: center; }
            .main { padding-top: 20px; }
            .form-grid { grid-template-columns: 1fr; }
            .field.full { grid-column: auto; }
            .section-head { align-items: flex-start; }
        }
        @media (prefers-color-scheme: dark) {
            :root { color-scheme: dark; color: #EDEDEC; background: #161615; }
            .panel, .stat { border-color: #3e3e3a; background: #1f1f1e; }
            .muted, .stat span, th { color: #a7a69f; }
            th, td { border-color: #353530; }
            input, select, textarea { color: #EDEDEC; border-color: #3e3e3a; background: #161615; }
            .btn.secondary { color: #EDEDEC; border-color: #62605b; background: #1f1f1e; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <div>
                <p class="brand">{{ config('app.name', 'Laundry') }}</p>
                <span class="role">{{ $user->role }}</span>
            </div>
            <nav class="nav" aria-label="Navigasi dashboard">
                <a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard')])>Dashboard</a>
                @if (in_array($user->role, ['admin', 'kasir'], true))
                    <a href="{{ route('pesanans.index') }}" @class(['active' => request()->routeIs('pesanans.*')])>Transaksi</a>
                @endif
                @if ($user->role === 'admin')
                    <a href="{{ route('layanan.index') }}" @class(['active' => request()->routeIs('layanan.*')])>Layanan</a>
                    <a href="{{ route('slot-pickups.index') }}" @class(['active' => request()->routeIs('slot-pickups.*')])>Jadwal pickup</a>
                    <a href="{{ route('pengguna.index') }}" @class(['active' => request()->routeIs('pengguna.*')])>Akun staff</a>
                @endif
            </nav>
            <div class="sidebar-bottom">
                <p>{{ $user->name }}<br>{{ $user->email }}</p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn secondary small" type="submit">Keluar</button>
                </form>
            </div>
        </aside>
        <main class="main">
            <header class="topbar">
                <div>
                    <h1>@yield('heading', 'Dashboard')</h1>
                    <span class="muted">@yield('description')</span>
                </div>
            </header>

            @if (session('success'))
                <div class="alert success" role="status">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert error" role="alert">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert error" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
