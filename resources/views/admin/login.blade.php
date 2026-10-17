<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administrator sign in · {{ config('app.name', 'Vibely') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-login-body">
    <main class="login-card">
        <a class="login-brand" href="{{ url('/') }}"><span class="brand-mark">V</span><span><strong>Vibely</strong><small>ADMIN CONSOLE</small></span></a>
        <p class="eyebrow">ADMINISTRATOR ACCESS</p>
        <h1>Welcome back</h1>
        <p class="login-subtitle">Sign in with an administrator account to continue.</p>
        @if ($errors->any())
            <div class="alert alert-error" role="alert"><span>!</span><div>{{ $errors->first() }}</div></div>
        @endif
        <form method="POST" action="{{ route('admin.login') }}" class="stacked-form login-form">
            @csrf
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" autocomplete="username" value="{{ old('email') }}" placeholder="admin@example.com" required autofocus>
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" placeholder="Enter your password" required>
            <label class="remember-field"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
            <button class="button button-primary" type="submit">Sign in to admin</button>
        </form>
        <p class="login-footer">Administrator access is restricted and monitored.</p>
    </main>
</body>
</html>
