
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Vibely | Change Password</title>

    <!-- Shared Vibely CSS -->
    <link rel="stylesheet"
          href="{{ asset('assets/prototype.css') }}">

    <style>
        .password-container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px 16px;
        }

        .password-card {
            width: 100%;
            max-width: 460px;
            padding: 32px;
        }

        .password-brand {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .password-brand h1 {
            margin: 0;
            color: var(--brand);
            font-size: 28px;
        }

        .password-title {
            text-align: center;
            font-size: 21px;
            margin-bottom: 7px;
        }

        .password-card .subtext {
            text-align: center;
            margin-bottom: 25px;
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
            margin-bottom: 19px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-size: 13px;
            font-weight: 650;
        }

        .form-group .field {
            width: 100%;
            padding: 12px;
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .field {
            padding-right: 72px;
        }

        .show-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: var(--brand);
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .form-hint {
            display: block;
            margin-top: 6px;
            color: var(--muted);
            font-size: 11px;
        }

        .form-error {
            display: block;
            margin-top: 6px;
            color: var(--red);
            font-size: 12px;
        }

        .form-error[hidden] {
            display: none;
        }

        .update-btn {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
        }

        .back-link {
            margin-top: 24px;
            text-align: center;
        }

        .back-link a {
            color: var(--brand);
            font-size: 13px;
            font-weight: 700;
        }

        .preview-message {
            margin-top: 16px;
            padding: 12px;
            background: var(--green-soft);
            color: var(--green);
            border-radius: 5px;
            font-size: 12px;
        }

        .preview-message[hidden] {
            display: none;
        }

        @media (max-width: 480px) {
            .password-card {
                padding: 22px;
            }
        }
    </style>
</head>

<body>

<main class="password-container">

    <section class="panel password-card">

        <!-- Vibely Logo -->
        <div class="password-brand">
            <span class="brand-mark">V</span>
            <h1>Vibely</h1>
        </div>

        <h2 class="password-title">
            Change Password
        </h2>

        <p class="subtext">
            Keep your Vibely account secure
            by updating your password.
        </p>

        <p class="required-note">
            Fields marked
            <span class="required-star">*</span>
            are required.
        </p>

        <!-- Frontend Preview Only -->
        <form id="change-password-form">

            <!-- Current Password -->
            <div class="form-group">

                <label for="current_password">
                    Current Password
                    <span class="required-star">*</span>
                </label>

                <div class="password-wrapper">

                    <input
                        class="field"
                        type="password"
                        id="current_password"
                        name="current_password"
                        placeholder="Enter current password"
                        autocomplete="current-password"
                        required
                    >

                    <button
                        class="show-password"
                        type="button"
                        data-target="current_password"
                        aria-label="Show current password"
                        aria-pressed="false"
                    >
                        Show
                    </button>

                </div>

            </div>

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
                        placeholder="Enter new password"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >

                    <button
                        class="show-password"
                        type="button"
                        data-target="password"
                        aria-label="Show new password"
                        aria-pressed="false"
                    >
                        Show
                    </button>

                </div>

                <small class="form-hint">
                    Password must contain at least
                    8 characters.
                </small>

                <p
                    class="form-error"
                    id="new-password-error"
                    role="alert"
                    hidden
                ></p>

            </div>

            <!-- Confirm New Password -->
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
                        placeholder="Confirm new password"
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
                    class="form-error"
                    id="confirm-password-error"
                    role="alert"
                    hidden
                ></p>

            </div>

            <!-- Submit -->
            <button
                class="btn update-btn"
                type="submit"
            >
                Update Password
            </button>

            <!-- Preview Message -->
            <p
                class="preview-message"
                id="preview-message"
                role="status"
                hidden
            ></p>

        </form>

        <p class="back-link">
            <a href="/ui-preview/edit-profile">
                ← Back to My Profile
            </a>
        </p>

    </section>

</main>

<script>
    const form = document.getElementById(
        'change-password-form'
    );

    const currentPassword = document.getElementById(
        'current_password'
    );

    const newPassword = document.getElementById(
        'password'
    );

    const confirmPassword = document.getElementById(
        'password_confirmation'
    );

    const newPasswordError = document.getElementById(
        'new-password-error'
    );

    const confirmError = document.getElementById(
        'confirm-password-error'
    );

    const previewMessage = document.getElementById(
        'preview-message'
    );

    // Show / Hide each password field
    document.querySelectorAll(
        '.show-password'
    ).forEach(function(button) {

        button.addEventListener('click', function() {

            const input = document.getElementById(
                button.dataset.target
            );

            const isHidden =
                input.type === 'password';

            input.type = isHidden ? 'text' : 'password';

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
                button.dataset.target.replaceAll('_', ' ')
            );

        });

    });

    // Validate new password
    function validatePasswords() {

        newPassword.setCustomValidity('');
        confirmPassword.setCustomValidity('');

        newPasswordError.hidden = true;
        confirmError.hidden = true;

        // New password must be different
        if (
            currentPassword.value &&
            newPassword.value &&
            currentPassword.value === newPassword.value
        ) {
            newPassword.setCustomValidity(
                'New password must be different from current password.'
            );

            newPasswordError.textContent =
                'New password must be different from current password.';

            newPasswordError.hidden = false;
        }

        // Passwords must match
        if (
            confirmPassword.value &&
            newPassword.value !== confirmPassword.value
        ) {
            confirmPassword.setCustomValidity(
                'New passwords do not match.'
            );

            confirmError.textContent =
                'New passwords do not match.';

            confirmError.hidden = false;
        }
    }

    // Revalidate when typing
    [
        currentPassword,
        newPassword,
        confirmPassword
    ].forEach(function(input) {

        input.addEventListener('input', function() {
            previewMessage.hidden = true;
            validatePasswords();
        });

    });

    // Preview submit only
    form.addEventListener('submit', function(event) {

        event.preventDefault();

        previewMessage.hidden = true;

        validatePasswords();

        if (!form.reportValidity()) {
            return;
        }

        previewMessage.textContent =
            'Validation passed! ' +
            'This is a frontend preview. ' +
            'Your password was not changed.';

        previewMessage.hidden = false;

        // Clear sensitive values
        form.reset();

        [currentPassword, newPassword, confirmPassword]
            .forEach(function(input) {
                input.type = 'password';
                input.setCustomValidity('');
            });

        document.querySelectorAll(
            '.show-password'
        ).forEach(function(button) {
            button.textContent = 'Show';
            button.setAttribute('aria-pressed', 'false');
            button.setAttribute(
                'aria-label',
                'Show ' + button.dataset.target.replaceAll('_', ' ')
            );
        });

    });
</script>

</body>
</html>
