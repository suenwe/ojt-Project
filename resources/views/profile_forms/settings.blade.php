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

        <label class="settings-form-wide" for="profile-current-password">
            Current password (only needed if you change your email)
            <span class="password-field">
                <input
                    class="field"
                    id="profile-current-password"
                    name="current_password"
                    type="password"
                    autocomplete="current-password"
                >
                <button class="password-toggle" type="button" data-toggle-for="profile-current-password" aria-label="Show password" aria-pressed="false">Show</button>
            </span>
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
            <img class="avatar avatar-large avatar-photo profile-photo-preview" id="profile-photo-preview" alt="Preview of your new profile picture" hidden>
        </label>

        @if ($user->photoDataUri())
            <label class="settings-form-wide settings-checkbox" for="profile-remove-photo">
                <input id="profile-remove-photo" name="remove_photo" type="checkbox" value="1" @checked(old('remove_photo'))>
                Remove my current profile picture
            </label>
        @endif

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
            <span class="password-field">
                <input
                    class="field"
                    id="current-password"
                    name="current_password"
                    type="password"
                    autocomplete="current-password"
                    required
                >
                <button class="password-toggle" type="button" data-toggle-for="current-password" aria-label="Show password" aria-pressed="false">Show</button>
            </span>
        </label>

        <label for="new-password">
            New password
            <span class="password-field">
                <input
                    class="field"
                    id="new-password"
                    name="password"
                    type="password"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >
                <button class="password-toggle" type="button" data-toggle-for="new-password" aria-label="Show password" aria-pressed="false">Show</button>
            </span>
        </label>

        <label for="new-password-confirmation">
            Confirm new password
            <span class="password-field">
                <input
                    class="field"
                    id="new-password-confirmation"
                    name="password_confirmation"
                    type="password"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >
                <button class="password-toggle" type="button" data-toggle-for="new-password-confirmation" aria-label="Show password" aria-pressed="false">Show</button>
            </span>
        </label>

        <div class="settings-form-wide settings-form-actions">
            <button class="btn" type="submit">Change Password</button>
        </div>
    </form>
</section>

@can('deleteOwnAccount', $user)
    <section class="panel composer danger-zone" id="delete-account">
        <h2 class="composer-title">Delete Account</h2>
        <p class="subtext">This permanently deletes your account, your posts, comments and reactions. It cannot be undone.</p>

        @if ($errors->deleteAccount->any())
            <div class="form-errors" role="alert">
                @foreach ($errors->deleteAccount->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form
            class="settings-form"
            action="{{ route('profile.destroy') }}"
            method="POST"
            data-confirm="Your account and everything you posted will be permanently deleted."
            data-confirm-title="Delete your account?"
            data-confirm-action="Delete account"
        >
            @csrf
            @method('DELETE')

            <label class="settings-form-wide" for="delete-account-password">
                Enter your password to confirm
                <span class="password-field">
                    <input
                        class="field"
                        id="delete-account-password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                    >
                    <button class="password-toggle" type="button" data-toggle-for="delete-account-password" aria-label="Show password" aria-pressed="false">Show</button>
                </span>
            </label>

            <div class="settings-form-wide settings-form-actions">
                <button class="btn danger" type="submit">Delete my account</button>
            </div>
        </form>
    </section>
@endcan

<script>
    (() => {
        'use strict';

        document.querySelectorAll('[data-toggle-for]').forEach((toggle) => {
            const input = document.getElementById(toggle.dataset.toggleFor);

            toggle.addEventListener('click', () => {
                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                toggle.textContent = isHidden ? 'Hide' : 'Show';
                toggle.setAttribute('aria-pressed', String(isHidden));
                toggle.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
            });
        });

        const photoInput = document.getElementById('profile-photo');
        const photoPreview = document.getElementById('profile-photo-preview');
        const removePhoto = document.getElementById('profile-remove-photo');

        photoInput.addEventListener('change', () => {
            const file = photoInput.files[0];

            if (!file || !file.type.startsWith('image/')) {
                photoPreview.hidden = true;
                photoPreview.removeAttribute('src');
                return;
            }

            photoPreview.src = URL.createObjectURL(file);
            photoPreview.hidden = false;

            if (removePhoto) removePhoto.checked = false;
        });
    })();
</script>
