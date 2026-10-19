
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Knowvia | Forgot Password</title>

    <!-- Shared Knowvia CSS -->
    <link rel="stylesheet"
          href="{{ asset('assets/prototype.css') }}">

    <style>
        .forgot-container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px 16px;
        }

        .forgot-card {
            width: 100%;
            max-width: 440px;
            padding: 32px;
        }

        .forgot-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .forgot-brand h1 {
            margin: 0;
            font-size: var(--text-3xl);
            color: var(--brand);
        }

        .forgot-title {
            text-align: center;
            font-size: var(--text-xl);
            margin-bottom: 8px;
        }

        .forgot-card .subtext {
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
            margin-bottom: 22px;
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

        .preview-message {
            margin-top: 16px;
            padding: 12px;
            border-radius: 5px;
            background: var(--green-soft);
            color: var(--green);
            font-size: var(--text-sm);
        }

        .preview-message[hidden] {
            display: none;
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
            .forgot-card {
                padding: 22px;
            }
        }
    </style>
</head>

<body>

<main class="forgot-container">

    <section class="panel forgot-card">

        <!-- Knowvia Branding -->
        <div class="forgot-brand">
            <span class="brand-mark">K</span>
            <h1>Knowvia</h1>
        </div>

        <h2 class="forgot-title">
            Forgot Password?
        </h2>

        <p class="subtext">
            Enter your email address below. We'll send a verification code before allowing a password reset.
        </p>

        <p class="required-note">
            Fields marked
            <span class="required-star">*</span>
            are required.
        </p>

        <form id="forgot-form" method="POST" action="{{ route('password.email') }}">
            @csrf

            @if ($errors->any())
                <div class="form-errors" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

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
                    value="{{ old('email') }}"
                    placeholder="Enter your registered email"
                    autocomplete="email"
                    required
                >

            </div>

            <button
                class="btn reset-btn"
                type="submit"
            >
                Send Verification Code
            </button>

            <!-- Reset Link Sent Message -->
            <p
                id="preview-message"
                class="preview-message"
                role="status"
                @unless (session('status')) hidden @endunless
            >{{ session('status') }}</p>

        </form>

        <p class="back-link">
            <a href="{{ route('login') }}">
                ← Back to Sign In
            </a>
        </p>

    </section>

</main>

<script>
    const emailInput = document.getElementById(
        'email'
    );

    const previewMessage = document.getElementById(
        'preview-message'
    );

    // Hide message when email is edited
    emailInput.addEventListener('input', function() {
        previewMessage.hidden = true;
    });
</script>

</body>
</html>
