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

            <div class="auth-forms">
                <form class="auth-form" method="POST" action="#">
                    @csrf
                    <h3>Sign in</h3>
                    <label for="login-email">Email</label>
                    <input class="field" id="login-email" name="email" type="email" autocomplete="email" required>
                    <label for="login-password">Password</label>
                    <input class="field" id="login-password" name="password" type="password" autocomplete="current-password" required>
                    <label class="auth-check">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                    <button class="btn auth-submit" type="submit">Sign in</button>
                    <a class="auth-link" href="#forgot-password">Forgot your password?</a>
                </form>

                <div class="auth-register-prompt">
                    <p class="register-prompt-default">Don't have an account?</p>
                    <p class="register-prompt-open">Already have an account?</p>
                    <details>
                        <summary aria-label="Switch between sign in and registration">Create an account</summary>
                        <form class="auth-form" method="POST" action="#">
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

            <form class="two-factor-form" method="POST" action="#" aria-label="Two-factor verification">
                @csrf
                <div>
                    <h3>Two-factor verification</h3>
                    <p class="subtext">Enter the verification code sent to your registered device when required.</p>
                </div>
                <div class="two-factor-controls">
                    <input class="field" name="two_factor_code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="6-digit code" aria-label="Two-factor verification code">
                    <button class="btn small" type="submit">Verify</button>
                </div>
            </form>
        </section>
    </main>
@endsection
