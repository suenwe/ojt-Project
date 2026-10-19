@extends('layouts.auth')

@section('title', 'Reset your password - Knowvia')
@section('auth-heading', 'Forgot your password?')
@section('auth-description', 'Enter your account email. We will send a six-digit code before allowing a password reset.')

@section('auth-form')
    <form class="auth-form" method="POST" action="{{ route('password.email') }}">
        @csrf
        <label for="email">Email address</label>
        <input class="field" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" required autofocus>

        <button class="btn auth-submit" type="submit">Send verification code</button>
        <a class="auth-link" href="{{ route('login') }}">Back to sign in</a>
    </form>
@endsection
