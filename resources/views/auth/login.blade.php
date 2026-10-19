@extends('layouts.auth')

@section('title', 'Sign in - Knowvia')
@section('auth-heading', 'Sign in')
@section('auth-description', 'Sign in to your account. We will email you a six-digit code to verify it is you.')

@section('auth-form')
    <form class="auth-form" method="POST" action="{{ route('login.store') }}">
        @csrf
        <label for="email">Email address</label>
        <input class="field" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" required autofocus>

        <label for="password">Password</label>
        <input class="field" id="password" name="password" type="password" autocomplete="current-password" required>

        <label class="auth-check" for="remember">
            <input id="remember" type="checkbox" name="remember" value="1">
            <span>Remember me</span>
        </label>

        <button class="btn auth-submit" type="submit">Continue</button>
        <a class="auth-link" href="{{ route('forgot-password') }}">Forgot your password?</a>
        <a class="auth-link" href="{{ route('register') }}">Create a temporary account</a>
    </form>
@endsection
