@extends('layouts.app')

@section('content')
    <main class="auth-shell">
        <section class="auth-intro">
            <a class="brand auth-brand" href="/" aria-label="Vibely home">
                <span class="brand-mark">V</span>
                <span>Vibely</span>
            </a>
            <div>
                <p class="auth-eyebrow">Welcome to Vibely</p>
                <h1>Learn, share, and grow together.</h1>
                <p class="auth-description">Connect with your community, share what you are working on, and keep up with the conversations that matter to you.</p>
            </div>
        </section>

        <section class="auth-panel" aria-label="Authentication">
            <div class="auth-panel-heading">
                <h2>Access your account</h2>
                <p class="subtext">Sign in or create an account to continue.</p>
            </div>

            @if (session('status'))
                <p class="auth-status" role="status">{{ session('status') }}</p>
            @endif

            @if ($errors->any())
                <div class="auth-errors" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="auth-forms">
                <form class="auth-form" method="POST" action="{{ route('login.store') }}">
                    @csrf
                    <h3>Sign in</h3>
                    <label for="login-email">Email</label>
                    <input class="field" id="login-email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                    <label for="login-password">Password</label>
                    <input class="field" id="login-password" name="password" type="password" autocomplete="current-password" required>
                    <label class="auth-check">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                    <button class="btn auth-submit" type="submit">Sign in</button>
                    <a class="auth-link" href="{{ route('forgot-password') }}">Forgot your password?</a>
                </form>

                <div class="auth-register-prompt">
                    <p class="register-prompt-default">Don't have an account?</p>
                    <p class="register-prompt-open">Already have an account?</p>
                    <details>
                        <summary aria-label="Switch between sign in and registration">Create an account</summary>
                        <form class="auth-form" method="POST" action="{{ route('register.store') }}">
                            @csrf
                            <label for="register-username">Username</label>
                            <input class="field" id="register-username" name="username" type="text" autocomplete="username" required>
                            <label for="register-email">Email</label>
                            <input class="field" id="register-email" name="email" type="email" autocomplete="email" required>
                            <label for="register-password">Password</label>
                            <input class="field" id="register-password" name="password" type="password" autocomplete="new-password" required>
                            <label for="register-password-confirmation">Confirm password</label>
                            <input class="field" id="register-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                            <button class="btn secondary auth-submit" type="submit">Register</button>
                        </form>
                    </details>
                </div>
            </div>

            <p class="subtext">A six-digit code is emailed for every sign-in and must be entered within one minute.</p>
        </section>
    </main>
@endsection
