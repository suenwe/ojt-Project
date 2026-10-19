
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Knowvia | Sign In</title>

    <!-- Shared Knowvia Design -->
    <link rel="stylesheet"
          href="{{ asset('assets/prototype.css') }}">

    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px 16px;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            padding: 32px;
        }

        .login-brand {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .login-brand h1 {
            margin: 0;
            font-size: var(--text-3xl);
            color: var(--brand);
        }

        .login-title {
            text-align: center;
            font-size: var(--text-xl);
            margin-bottom: 5px;
        }

        .login-card .subtext {
            text-align: center;
            margin-bottom: 24px;
        }

        .required-note {
            color: var(--muted);
            font-size: var(--text-sm);
            margin-bottom: 18px;
        }

        .required-star {
            color: var(--red);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-size: var(--text-md);
            font-weight: 650;
        }

        .form-group .field {
            width: 100%;
            padding: 12px;
        }

        .form-group .field:focus {
            border-color: var(--brand);
            outline: 2px solid var(--brand-soft);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .field {
            padding-right: 70px;
        }

        .show-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: var(--brand);
            font-size: var(--text-sm);
            font-weight: 700;
            cursor: pointer;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 22px;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 7px;
            color: var(--ink);
            font-size: var(--text-sm);
            cursor: pointer;
        }

        .form-options a,
        .signup-text a {
            color: var(--brand);
            font-size: var(--text-sm);
            font-weight: 700;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
        }

        .signup-text {
            margin-top: 24px;
            text-align: center;
            color: var(--muted);
            font-size: var(--text-md);
        }

        .signup-text a {
            font-size: var(--text-md);
        }

        .preview-message {
            padding: 12px;
            margin-top: 16px;
            background: var(--green-soft);
            color: var(--green);
            border-radius: 5px;
            font-size: var(--text-sm);
        }

        .preview-message[hidden] {
            display: none;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 22px;
            }
        }
        .form-errors {
            margin-bottom: 16px;
            color: var(--red);
            font-size: var(--text-md);
        }

        .form-errors p {
            margin: 0 0 4px;
        }
    </style>
    @include('partials.saved-theme')
</head>

<body>

<main class="login-container">

    <section class="panel login-card">

        <!-- Knowvia Logo -->
        <div class="login-brand">

            <span class="brand-mark">K</span>

            <h1>Knowvia</h1>

        </div>

        <h2 class="login-title">
            Sign in
        </h2>

        <p class="subtext">
            Use your Knowvia email and password.
        </p>

        <p class="required-note">
            Fields marked
            <span class="required-star">*</span>
            are required.
        </p>

        <!-- Frontend UI Preview Only -->
        <form id="login-form" method="POST" action="{{ route('login.store') }}">
            @csrf

            @if ($errors->any())
                <div class="form-errors" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- Email -->
            <div class="form-group">

                <label for="email">
                    Email Address
                    <span class="required-star">*</span>
                </label>

                <input
                    class="field"
                    type="email"
                    id="email"
                    data-clearable
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Enter your email"
                    autocomplete="email"
                    required
                >

            </div>

            <!-- Password -->
            <div class="form-group">

                <label for="password">
                    Password
                    <span class="required-star">*</span>
                </label>

                <div class="password-wrapper">

                    <input
                        class="field"
                        type="password"
                        id="password"
                        data-clearable
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >

                    <button
                        class="show-password"
                        id="toggle-password"
                        type="button"
                        aria-label="Show password"
                        aria-pressed="false"
                    >
                        Show
                    </button>

                </div>

            </div>

            <!-- Remember Me and Forgot Password -->
            <div class="form-options">

                <label class="remember-label">

                    <input
                        type="checkbox"
                        id="remember"
                        name="remember"
                        value="1"
                    >

                    Remember me

                </label>

                <a href="{{ route('password.request') }}">
                    Forgot Password?
                </a>

            </div>

            <!-- Sign In -->
            <button
                class="btn login-btn"
                type="submit"
            >
                Sign In
            </button>

            <!-- Status Message (e.g. after a password reset) -->
            <p
                id="preview-message"
                class="preview-message"
                role="status"
                @unless (session('status')) hidden @endunless
            >{{ session('status') }}</p>

        </form>

        <!-- Sign Up -->
        <p class="signup-text">
            Don't have an account?

            <a href="{{ route('register') }}">
                Sign Up
            </a>
        </p>

    </section>

</main>

<script>
    // Get elements
    const loginForm = document.getElementById(
        'login-form'
    );

    const email = document.getElementById(
        'email'
    );

    const password = document.getElementById(
        'password'
    );

    const togglePassword = document.getElementById(
        'toggle-password'
    );

    const previewMessage = document.getElementById(
        'preview-message'
    );

    // Show / Hide Password
    togglePassword.addEventListener(
        'click',
        function() {

            const isHidden =
                password.type === 'password';

            password.type = isHidden
                ? 'text'
                : 'password';

            togglePassword.textContent =
                isHidden ? 'Hide' : 'Show';

            togglePassword.setAttribute(
                'aria-pressed',
                String(isHidden)
            );

            togglePassword.setAttribute(
                'aria-label',
                isHidden
                    ? 'Hide password'
                    : 'Show password'
            );
        }
    );

    // Hide preview message when editing
    [email, password].forEach(function(input) {

        input.addEventListener('input', function() {
            previewMessage.hidden = true;
        });

    });

</script>

<script src="{{ asset('assets/clear-fields.js') }}"></script>

</body>
</html>
