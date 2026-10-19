
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Knowvia | Reset Password</title>

    <!-- Shared Knowvia CSS -->
    <link rel="stylesheet"
          href="{{ asset('assets/prototype.css') }}">

    <style>
        .reset-container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px 16px;
        }

        .reset-card {
            width: 100%;
            max-width: 440px;
            padding: 32px;
        }

        .reset-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .reset-brand h1 {
            margin: 0;
            font-size: var(--text-3xl);
            color: var(--brand);
        }

        .reset-title {
            text-align: center;
            font-size: var(--text-xl);
            margin-bottom: 8px;
        }

        .reset-card .subtext {
            text-align: center;
            margin-bottom: 25px;
            line-height: 1.6;
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

        .password-hint {
            margin-top: 6px;
            color: var(--muted);
            font-size: var(--text-sm);
        }

        .reset-btn {
            width: 100%;
            padding: 12px;
        }

        .back-link {
            margin-top: 24px;
            text-align: center;
        }

        .back-link a {
            color: var(--brand);
            font-size: var(--text-md);
            font-weight: 700;
        }

        .form-errors {
            margin-bottom: 16px;
            color: var(--red);
            font-size: var(--text-md);
        }

        .form-errors p {
            margin: 0 0 4px;
        }

        @media (max-width: 480px) {
            .reset-card {
                padding: 22px;
            }
        }
    </style>
</head>

<body>

<main class="reset-container">

    <section class="panel reset-card">

        <!-- Knowvia Branding -->
        <div class="reset-brand">
            <span class="brand-mark">K</span>
            <h1>Knowvia</h1>
        </div>

        <h2 class="reset-title">
            Choose a New Password
        </h2>

        <p class="subtext">
            Your email has been verified. Choose a new password for your account.
        </p>

        <p class="required-note">
            Fields marked
            <span class="required-star">*</span>
            are required.
        </p>

        <form id="reset-form" method="POST" action="{{ route('password.update') }}">
            @csrf

            @if ($errors->any())
                <div class="form-errors" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- New Password -->
            <div class="form-group">

                <label for="password">
                    New Password
                    <span class="required-star">*</span>
                </label>

                <div class="password-wrapper">

                    <input
                        class="field"
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter a new password"
                        autocomplete="new-password"
                        minlength="8"
                        required
                    >

                    <button
                        class="show-password"
                        type="button"
                        data-toggle-for="password"
                        aria-label="Show password"
                        aria-pressed="false"
                    >
                        Show
                    </button>

                </div>

                <p class="password-hint">
                    At least 8 characters, including a number and a symbol.
                </p>

            </div>

            <!-- Confirm Password -->
            <div class="form-group">

                <label for="password_confirmation">
                    Confirm New Password
                    <span class="required-star">*</span>
                </label>

                <div class="password-wrapper">

                    <input
                        class="field"
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="Re-enter the new password"
                        autocomplete="new-password"
                        minlength="8"
                        required
                    >

                    <button
                        class="show-password"
                        type="button"
                        data-toggle-for="password_confirmation"
                        aria-label="Show password"
                        aria-pressed="false"
                    >
                        Show
                    </button>

                </div>

            </div>

            <button
                class="btn reset-btn"
                type="submit"
            >
                Save New Password
            </button>

        </form>

        <p class="back-link">
            <a href="{{ route('login') }}">
                ← Back to Sign In
            </a>
        </p>

    </section>

</main>

<script>
    // Show / Hide Password
    document.querySelectorAll('[data-toggle-for]').forEach(function(toggle) {

        const input = document.getElementById(
            toggle.dataset.toggleFor
        );

        toggle.addEventListener('click', function() {

            const isHidden =
                input.type === 'password';

            input.type = isHidden
                ? 'text'
                : 'password';

            toggle.textContent =
                isHidden ? 'Hide' : 'Show';

            toggle.setAttribute(
                'aria-pressed',
                String(isHidden)
            );

            toggle.setAttribute(
                'aria-label',
                isHidden
                    ? 'Hide password'
                    : 'Show password'
            );
        });

    });
</script>

</body>
</html>
