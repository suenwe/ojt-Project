@extends('layouts.auth')

@section('title', 'Verify your email - Knowvia')
@section('auth-heading', 'Check your email')
@section('auth-description')
    Enter the six-digit code sent to {{ $email }}. Codes expire after ten minutes and can only be used once.
@endsection

@section('auth-form')
    <form class="auth-form" method="POST" action="{{ route('verification-code.verify') }}">
        @csrf
        <label for="code">Six-digit verification code</label>
        <input class="field" id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required autofocus>
        <button class="btn auth-submit" type="submit">Verify code</button>
    </form>

    <form class="auth-form auth-resend" method="POST" action="{{ route('verification-code.resend') }}">
        @csrf
        <button class="btn secondary auth-submit" type="submit">Resend code</button>
    </form>

    <a class="auth-link" href="{{ route('login') }}">Cancel and return to sign in</a>
@endsection
