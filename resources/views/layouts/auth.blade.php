@extends('layouts.app')

@section('content')
<style>
    .auth-shell { display: grid; grid-template-columns: minmax(0, 1fr) minmax(360px, 560px); gap: 36px; width: min(1120px, 100%); min-height: 100vh; margin: 0 auto; padding: 40px 24px; align-items: center; }
    .auth-intro { display: grid; gap: 36px; }
    .auth-panel { padding: 28px; background: var(--surface); border: 1px solid var(--line); border-radius: 10px; box-shadow: var(--shadow); }
    .auth-panel-heading { margin-bottom: 20px; }
    .auth-form { display: grid; gap: 10px; }
    .auth-status, .auth-errors { margin-bottom: 16px; padding: 10px 12px; border-radius: 5px; }
    .auth-status { color: var(--green); background: var(--green-soft); }
    .auth-errors { color: var(--red); background: #fff0f0; }
    .auth-errors p:last-child { margin-bottom: 0; }
    .auth-resend { margin-top: 16px; }
    .auth-submit { width: 100%; }
    .auth-link { display: block; margin-top: 14px; color: var(--brand); text-align: center; }
    @media (max-width: 760px) {
        .auth-shell { grid-template-columns: 1fr; gap: 24px; padding: 24px 16px; }
        .auth-panel { padding: 20px; }
    }
</style>
    <main class="auth-shell">
        <section class="auth-intro">
            <a class="brand auth-brand" href="/" aria-label="Knowvia home">
                <span class="brand-mark">K</span>
                <span>Knowvia</span>
            </a>
            <div>
                <p class="auth-eyebrow">Welcome to Knowvia</p>
                <h1>Learn, share, and grow together.</h1>
                <p class="auth-description">Connect with your community, share what you are working on, and keep up with the conversations that matter to you.</p>
            </div>
        </section>

        <section class="auth-panel" aria-label="Authentication">
            <div class="auth-panel-heading">
                <h2>@yield('auth-heading')</h2>
                <p class="subtext">@yield('auth-description')</p>
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

            @yield('auth-form')
        </section>
    </main>
@endsection
