@extends('layouts.auth')

@section('title', 'Log in')

@section('content')
    <h1>Log in</h1>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
        @error('email')
            <p class="error">{{ $message }}</p>
        @enderror

        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        @error('password')
            <p class="error">{{ $message }}</p>
        @enderror

        <label class="remember">
            <input name="remember" type="checkbox" value="1">
            Remember me
        </label>

        <button type="submit">Log in</button>
    </form>

    <p class="footer">Don't have an account? <a href="{{ route('register') }}">Register</a></p>
@endsection
