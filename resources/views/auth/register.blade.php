@extends('layouts.auth')

@section('title', 'Create account')

@section('content')
    <h1>Create account</h1>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <label for="name">Name</label>
        <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required autofocus>
        @error('name')
            <p class="error">{{ $message }}</p>
        @enderror

        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
        @error('email')
            <p class="error">{{ $message }}</p>
        @enderror

        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="new-password" required>
        @error('password')
            <p class="error">{{ $message }}</p>
        @enderror

        <label for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>

        <button type="submit">Create account</button>
    </form>

    <p class="footer">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
@endsection
