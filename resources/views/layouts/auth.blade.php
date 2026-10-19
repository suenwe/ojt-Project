@extends('layouts.app')

@section('content')
    <main class="auth-shell">
        <section class="auth-intro">
            <a class="brand auth-brand" href="/" aria-label="Vibely home">
                <span class="brand-mark">V</span>
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
