@extends('layouts.auth')

@section('title', 'Choose a new password - Knowvia')
@section('auth-heading', 'Choose a new password')
@section('auth-description', 'Your email is verified. Choose a new password to secure your account.')

@section('auth-form')
    <form class="auth-form" method="POST" action="{{ route('password.update') }}">
        @csrf
        <label for="password">New password</label>
        <input class="field" id="password" name="password" type="password" autocomplete="new-password" minlength="8" required autofocus>

        <label for="password_confirmation">Confirm new password</label>
        <input class="field" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>

        <button class="btn auth-submit" type="submit">Reset password</button>
    </form>
@endsection
