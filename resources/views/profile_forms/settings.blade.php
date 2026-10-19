<section class="panel composer" id="profile-settings">
    <h2 class="composer-title">Profile Settings</h2>
    <p class="subtext">Update how other members see you.</p>

    @if (session('status') === 'profile-information-updated')
        <p class="form-status" role="status">Your profile has been saved.</p>
    @endif

    @if ($errors->updateProfileInformation->any())
        <div class="form-errors" role="alert">
            @foreach ($errors->updateProfileInformation->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="profile-summary profile-settings-summary">
        @if ($photoDataUri = $user->photoDataUri())
            <img class="avatar avatar-large avatar-photo" src="{{ $photoDataUri }}" alt="{{ $user->username }} profile photo">
        @else
            <span class="avatar avatar-large" aria-hidden="true">{{ $user->initials() }}</span>
        @endif
        <div>
            <strong>{{ $user->username }}</strong>
            <p class="subtext">{{ $user->email }}</p>
        </div>
    </div>

    <form class="settings-form" action="{{ route('user-profile-information.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <label for="profile-username">
            Username
            <input
                class="field"
                id="profile-username"
                name="username"
                type="text"
                value="{{ old('username', $user->username) }}"
                minlength="3"
                maxlength="30"
                autocomplete="username"
                required
            >
        </label>

        <label for="profile-email">
            Email address
            <input
                class="field"
                id="profile-email"
                name="email"
                type="email"
                value="{{ old('email', $user->email) }}"
                autocomplete="email"
                required
            >
        </label>

        <label for="profile-birthday">
            Birthday
            <input
                class="field"
                id="profile-birthday"
                name="birthday"
                type="date"
                value="{{ old('birthday', $user->birthday?->format('Y-m-d')) }}"
                max="{{ now()->format('Y-m-d') }}"
                required
            >
        </label>

        <label for="profile-phone">
            Phone number (optional)
            <input
                class="field"
                id="profile-phone"
                name="phone_number"
                type="tel"
                value="{{ old('phone_number', $user->phone_number) }}"
                maxlength="30"
                autocomplete="tel"
            >
        </label>

        <label class="settings-form-wide" for="profile-website">
            Website (optional)
            <input
                class="field"
                id="profile-website"
                name="website"
                type="url"
                value="{{ old('website', $user->website) }}"
                maxlength="255"
                placeholder="https://example.com"
                autocomplete="url"
            >
        </label>

        <label class="settings-form-wide" for="profile-photo">
            Profile picture (optional)
            <input
                class="field"
                id="profile-photo"
                name="photo"
                type="file"
                accept="image/jpeg,image/png"
            >
            <small class="subtext">JPG or PNG, up to 2 MB. Leave empty to keep your current picture.</small>
        </label>

        <div class="settings-form-wide settings-form-actions">
            <a class="btn secondary" href="{{ url('/') }}">Cancel</a>
            <button class="btn" type="submit">Save Profile</button>
        </div>
    </form>
</section>

<section class="panel composer" id="password-settings">
    <h2 class="composer-title">Change Password</h2>
    <p class="subtext">Use at least 8 characters, including a number and a symbol.</p>

    @if (session('status') === 'password-updated')
        <p class="form-status" role="status">Your password has been changed.</p>
    @endif

    @if ($errors->updatePassword->any())
        <div class="form-errors" role="alert">
            @foreach ($errors->updatePassword->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form class="settings-form" action="{{ route('user-password.update') }}" method="POST">
        @csrf
        @method('PUT')

        <label class="settings-form-wide" for="current-password">
            Current password
            <input
                class="field"
                id="current-password"
                name="current_password"
                type="password"
                autocomplete="current-password"
                required
            >
        </label>

        <label for="new-password">
            New password
            <input
                class="field"
                id="new-password"
                name="password"
                type="password"
                minlength="8"
                autocomplete="new-password"
                required
            >
        </label>

        <label for="new-password-confirmation">
            Confirm new password
            <input
                class="field"
                id="new-password-confirmation"
                name="password_confirmation"
                type="password"
                minlength="8"
                autocomplete="new-password"
                required
            >
        </label>

        <div class="settings-form-wide settings-form-actions">
            <button class="btn" type="submit">Change Password</button>
        </div>
    </form>
</section>
