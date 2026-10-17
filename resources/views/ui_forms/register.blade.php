
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Vibely | Create Account</title>

    <!-- Shared Vibely CSS -->
    <link rel="stylesheet"
          href="{{ asset('assets/prototype.css') }}">

    <style>
        .register-container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px 16px;
        }

        .register-card {
            width: 100%;
            max-width: 480px;
            padding: 32px;
        }

        .register-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 18px;
        }

        .register-brand h1 {
            margin: 0;
            color: var(--brand);
            font-size: 28px;
        }

        .register-title {
            text-align: center;
            margin-bottom: 5px;
            font-size: 20px;
        }

        .register-card .subtext {
            text-align: center;
            margin-bottom: 22px;
        }

        .required-note {
            color: var(--muted);
            font-size: 12px;
            margin-bottom: 18px;
        }

        .required-star {
            color: var(--red);
        }

        .form-group {
            margin-bottom: 17px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-size: 13px;
            font-weight: 650;
        }

        .form-group .field {
            width: 100%;
            padding: 11px 12px;
        }

        .form-group .field:focus {
            border-color: var(--brand);
            outline: 2px solid var(--brand-soft);
        }

        .form-hint {
            display: block;
            margin-top: 5px;
            color: var(--muted);
            font-size: 11px;
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .field {
            padding-right: 65px;
        }

        .show-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: var(--brand);
            font-size: 12px;
            font-weight: 650;
            cursor: pointer;
        }

        .form-error {
            margin-top: 6px;
            color: var(--red);
            font-size: 12px;
        }

        .register-btn {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
        }

        .login-text {
            margin-top: 23px;
            text-align: center;
            font-size: 13px;
            color: var(--muted);
        }

        .login-text a {
            color: var(--brand);
            font-weight: 700;
        }

        .preview-message {
            padding: 12px;
            margin-top: 16px;
            background: var(--green-soft);
            color: var(--green);
            border-radius: 5px;
            font-size: 12px;
        }

        .preview-message[hidden] {
            display: none;
        }

        @media (max-width: 480px) {
            .register-card {
                padding: 22px;
            }

            .register-container {
                padding: 16px;
            }
        }
    </style>
</head>

<body>

<main class="register-container">

    <section class="panel register-card">

        <!-- Brand -->
        <div class="register-brand">
            <span class="brand-mark">V</span>
            <h1>Vibely</h1>
        </div>

        <h2 class="register-title">
            Create Your Account
        </h2>

        <p class="subtext">
            Join Vibely and connect with your community.
        </p>

        <p class="required-note">
            Fields marked
            <span class="required-star">*</span>
            are required.
        </p>

        <!-- Frontend Preview Form -->
        <form id="register-form">

            <!-- Username -->
            <div class="form-group">

                <label for="name">
                    Username
                    <span class="required-star">*</span>
                </label>

                <input
                    class="field"
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Enter your username"
                    minlength="3"
                    maxlength="30"
                    autocomplete="username"
                    required
                >

                <small class="form-hint">
                    3 to 30 characters.
                </small>

            </div>

           
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
        name="email"
        placeholder="Enter your email"
        autocomplete="email"
        required
    >
</div>

<!-- Phone Number (Optional) -->
<div class="form-group">
    <label for="phone">
        Phone Number (Optional)
    </label>

    <input
        class="field"
        type="tel"
        id="phone"
        name="phone"
        placeholder="e.g. 09xxxxxxxxx"
        autocomplete="tel"
        maxlength="20"
    >

    <small class="form-hint">
        You can add your phone number later.
    </small>
</div>

            <!-- Birthday -->
            <div class="form-group">

                <label for="birthday">
                    Birthday
                    <span class="required-star">*</span>
                </label>

                <input
                    class="field"
                    type="date"
                    id="birthday"
                    name="birthday"
                    max="{{ date('Y-m-d') }}"
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
                        name="password"
                        placeholder="Create your password"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >

                    <button
                        class="show-password"
                        type="button"
                        data-target="password"
                        aria-label="Show password"
                        aria-pressed="false"
                    >
                        Show
                    </button>

                </div>

                <small class="form-hint">
                    Must be at least 8 characters.
                </small>

            </div>

            <!-- Confirm Password -->
            <div class="form-group">

                <label for="password_confirmation">
                    Confirm Password
                    <span class="required-star">*</span>
                </label>

                <div class="password-wrapper">

                    <input
                        class="field"
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="Confirm your password"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >

                    <button
                        class="show-password"
                        type="button"
                        data-target="password_confirmation"
                        aria-label="Show confirm password"
                        aria-pressed="false"
                    >
                        Show
                    </button>

                </div>

                <p
                    id="password-error"
                    class="form-error"
                    role="alert"
                    hidden
                ></p>

            </div>

            <!-- Create Account Button -->
            <button
                class="btn register-btn"
                type="submit"
            >
                Create Account
            </button>

            <!-- Preview Message -->
            <p
                id="preview-message"
                class="preview-message"
                role="status"
                hidden
            ></p>

        </form>

        <!-- Login Link -->
        <p class="login-text">
            Already have an account?

            <a href="/ui-preview/login">
                Sign In
            </a>
        </p>

    </section>

</main>

<script>
    // Form elements
    const form = document.getElementById(
        'register-form'
    );

    const password = document.getElementById(
        'password'
    );

    const confirmPassword = document.getElementById(
        'password_confirmation'
    );

    const passwordError = document.getElementById(
        'password-error'
    );

    const previewMessage = document.getElementById(
        'preview-message'
    );

    // Check password confirmation
    function validatePasswords() {

        const mismatch =
            confirmPassword.value !== '' &&
            password.value !== confirmPassword.value;

        if (mismatch) {
            confirmPassword.setCustomValidity(
                'Passwords do not match.'
            );

            passwordError.textContent =
                'Passwords do not match.';

            passwordError.hidden = false;

        } else {
            confirmPassword.setCustomValidity('');
            passwordError.textContent = '';
            passwordError.hidden = true;
        }
    }

    // Check while typing
    password.addEventListener(
        'input',
        validatePasswords
    );

    confirmPassword.addEventListener(
        'input',
        validatePasswords
    );

    // Show or hide passwords
    document.querySelectorAll(
        '.show-password'
    ).forEach(function(button) {

        button.addEventListener('click', function() {

            const input = document.getElementById(
                button.dataset.target
            );

            const isHidden =
                input.type === 'password';

            input.type = isHidden
                ? 'text'
                : 'password';

            button.textContent = isHidden
                ? 'Hide'
                : 'Show';

            button.setAttribute(
                'aria-pressed',
                String(isHidden)
            );

            button.setAttribute(
                'aria-label',
                (isHidden ? 'Hide ' : 'Show ') +
                (input.id === 'password'
                    ? 'password'
                    : 'confirm password')
            );
        });
    });

    // Frontend preview submit
    form.addEventListener('submit', function(event) {

        // Prevent sending data to a server
        event.preventDefault();

        previewMessage.hidden = true;

        validatePasswords();

        if (!form.reportValidity()) {
            return;
        }

        previewMessage.textContent =
            'Validation passed! ' +
            'This is a frontend preview. ' +
            'No account was created.';

        previewMessage.hidden = false;
    });
</script>

</body>
</html>
