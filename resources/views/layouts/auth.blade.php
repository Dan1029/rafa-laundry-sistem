<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - {{ config('app.name', 'Laundry') }}</title>
    <style>
        :root {
            color-scheme: light dark;
            font-family: ui-sans-serif, system-ui, sans-serif;
            color: #1b1b18;
            background: #f9f8f6;
        }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; }
        main { width: min(100%, 400px); padding: 32px; background: #fff; border: 1px solid #e8e7e2; border-radius: 12px; }
        h1 { margin: 0 0 24px; font-size: 24px; }
        label { display: block; margin: 16px 0 6px; font-size: 14px; }
        input:not([type="checkbox"]) { width: 100%; padding: 10px 12px; border: 1px solid #d5d4ce; border-radius: 6px; font: inherit; }
        input:focus { outline: 2px solid #777; outline-offset: 2px; }
        button { width: 100%; margin-top: 20px; padding: 10px 14px; border: 1px solid #1b1b18; border-radius: 6px; color: #fff; background: #1b1b18; font: inherit; cursor: pointer; }
        .remember { display: flex; align-items: center; gap: 8px; margin-top: 16px; font-size: 14px; }
        .error { margin: 6px 0 0; color: #b42318; font-size: 14px; }
        .footer { margin: 20px 0 0; font-size: 14px; }
        a { color: inherit; }
        @media (prefers-color-scheme: dark) {
            :root { color: #EDEDEC; background: #161615; }
            main { background: #161615; border-color: #3e3e3a; }
            input:not([type="checkbox"]) { color: #EDEDEC; background: #1f1f1e; border-color: #3e3e3a; }
            button { color: #161615; background: #EDEDEC; border-color: #EDEDEC; }
        }
    </style>
</head>
<body>
    <main>
        @yield('content')
    </main>
</body>
</html>
