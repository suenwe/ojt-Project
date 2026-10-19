@extends('layouts.app', ['title' => 'No access - Knowvia'])

@section('content')
<style>
    .error-shell { display: grid; place-items: center; min-height: 100vh; padding: 24px 16px; }
    .error-card { width: min(440px, 100%); padding: 32px; text-align: center; }
    .error-card .brand { justify-content: center; min-width: 0; margin-bottom: 22px; }
    .error-code { margin-bottom: 6px; color: var(--muted); font-size: var(--text-sm); font-weight: 700; }
    .error-card h1 { margin-bottom: 8px; font-size: var(--text-xl); }
    .error-card p { color: var(--muted); }
    .error-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; margin-top: 22px; }
</style>
@php
    $user = auth()->user();
    $reason = $exception->getMessage() ?: "You don't have permission to see this page.";
@endphp
    <main class="error-shell">
        <section class="panel error-card">
            <a class="brand" href="{{ url('/') }}" aria-label="Knowvia home">
                <span class="brand-mark">K</span>
                <span>Knowvia</span>
            </a>

            <p class="error-code">Error 403</p>
            <h1>You can't open this page</h1>
            <p>{{ $reason }}</p>

            <div class="error-actions">
                @if (! $user)
                    <a class="btn" href="{{ route('login') }}">Sign in</a>
                @elseif ($user->isBanned())
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn" type="submit">Sign out</button>
                    </form>
                @elseif ($user->role === 'admin')
                    <a class="btn" href="{{ route('admin.dashboard') }}">Go to admin dashboard</a>
                @else
                    <a class="btn" href="{{ route('home') }}">Back to home</a>
                @endif
            </div>
        </section>
    </main>
@endsection
