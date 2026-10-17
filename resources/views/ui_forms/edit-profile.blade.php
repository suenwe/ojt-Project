
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Knowvia | My Profile</title>

    <!-- Shared Knowvia CSS -->
    <link rel="stylesheet"
          href="{{ asset('assets/prototype.css') }}">

    <style>
        /* General layout */
        .profile-page {
            display: grid;
            gap: 16px;
            min-width: 0;
        }

        .profile-card {
            overflow: hidden;
        }

        .profile-cover {
            height: 150px;
        }

        .profile-summary {
            flex-wrap: wrap;
        }

        .profile-avatar {
            width: 72px;
            height: 72px;
        }

        .profile-bio {
            margin: 18px 0;
        }

        /* Edit profile form */
        .profile-form {
            border-top: 1px solid var(--line);
            padding-top: 20px;
        }

        .profile-form label {
            font-size: 12px;
            align-content: start;
            grid-auto-rows: max-content;
            gap: 6px;
        }

        .profile-form .field {
            font-size: 14px;
        }

        .profile-form textarea {
            min-height: 95px;
            resize: vertical;
        }

        .form-hint {
            display: block;
            color: var(--muted);
            font-size: 11px;
            margin-top: 4px;
        }

        .form-error {
            color: var(--red);
            font-size: 12px;
            margin-top: 6px;
        }

        .preview-message {
            padding: 12px;
            margin-top: 15px;
            border-radius: 5px;
            color: var(--green);
            background: var(--green-soft);
            font-size: 12px;
        }

        /* Header avatar */
        .header-avatar {
            width: 36px;
            height: 36px;
        }

        /* Non-functional prototype navigation */
        .nav-placeholder {
            display: block;
            padding: 9px 11px;
            color: var(--muted);
            font-weight: 600;
        }

        /* Respect hidden attribute */
        [hidden] {
            display: none !important;
        }

        /* Mobile responsive */
        @media (max-width: 700px) {
            .profile-form {
                grid-template-columns: 1fr;
            }

            .profile-summary > .btn {
                margin: 10px 0 0;
            }

            .profile-form-actions {
                flex-wrap: wrap;
            }
        }
    </style>
</head>

<body>

<!-- =========================
     NAVBAR
========================= -->

<header class="topbar">

    <a class="brand" href="#profile">
        <span class="brand-mark">V</span>
        Knowvia
    </a>

    <div class="top-actions">

        <span class="subtext">
            Community Member
        </span>

        <span
            class="avatar header-avatar"
            id="header-avatar"
            aria-label="Profile picture"
        >
            AM
        </span>

    </div>

</header>

<!-- =========================
     MAIN LAYOUT
========================= -->

<div class="app-layout">

    <!-- LEFT SIDEBAR -->
    <aside class="sidebar">

        <div class="panel side-panel">

            <p class="side-heading">
                Community
            </p>

            <nav class="side-nav">

                <span class="nav-placeholder">
                    Home Feed
                </span>

                <span class="nav-placeholder">
                    Explore Topics
                </span>

                <a
                    class="active"
                    href="#profile"
                >
                    My Profile
                </a>

                <a href="/ui-preview/change-password">
                    Account Security
                </a>

            </nav>

        </div>

        <!-- Sidebar Account Card -->
        <div class="panel account-card">

            <span
                class="avatar"
                id="sidebar-avatar"
                aria-label="Sidebar profile picture"
            >
                AM
            </span>

            <div>

                <strong id="sidebar-name">
                    Alex Morgan
                </strong>

                <small>
                    Community Member
                </small>

            </div>

        </div>

    </aside>

    <!-- =========================
         PROFILE MAIN SECTION
    ========================= -->

    <main class="profile-page" id="profile">

        <div class="page-heading">

            <div>
                <h1>My Profile</h1>

                <p class="subtext">
                    Manage your community profile.
                </p>
            </div>

        </div>

        <section class="panel profile-card">

            <!-- Profile Cover -->
            <div class="profile-cover"></div>

            <div class="profile-body">

                <!-- Profile Summary -->
                <div class="profile-summary">

                    <span
                        class="avatar profile-avatar"
                        id="profile-avatar"
                        aria-label="Profile picture"
                    >
                        AM
                    </span>

                    <div class="profile-copy">

                        <h2 id="profile-name">
                            Alex Morgan
                        </h2>

                        <p
                            class="subtext"
                            id="profile-email"
                        >
                            alex.morgan@example.com
                        </p>

                    </div>

                    <button
                        class="btn secondary small"
                        type="button"
                        id="edit-profile-button"
                    >
                        Edit Profile
                    </button>

                </div>

                <!-- About Me -->
                <p
                    class="profile-bio"
                    id="profile-bio"
                >
                    Learning and sharing ideas
                    with the community.
                </p>

                <!-- =========================
                     EDIT PROFILE FORM
                ========================= -->

                <form
                    id="profile-form"
                    class="profile-form"
                    enctype="multipart/form-data"
                    hidden
                >

                    <!-- Display Name -->
                    <label for="name">

                        Display Name

                        <input
                            class="field"
                            type="text"
                            id="name"
                            name="name"
                            value="Alex Morgan"
                            minlength="3"
                            maxlength="100"
                            autocomplete="name"
                            required
                        >

                        <small class="form-hint">
                            3 to 100 characters.
                        </small>

                    </label>

                    <!-- Email -->
                    <label for="email">

                        Email Address

                        <input
                            class="field"
                            type="email"
                            id="email"
                            name="email"
                            value="alex.morgan@example.com"
                            autocomplete="email"
                            required
                        >

                    </label>

                    <!-- About Me -->
                    <label
                        for="bio"
                        class="profile-form-wide"
                    >

                        About Me (Optional)

                        <textarea
                            class="field"
                            id="bio"
                            name="bio"
                            maxlength="240"
                            placeholder="Tell us about yourself..."
                        >Learning and sharing ideas with the community.</textarea>

                        <small class="form-hint">
                            Maximum 240 characters.
                        </small>

                    </label>

                    <!-- Profile Picture -->
                    <label
                        for="avatar"
                        class="profile-form-wide"
                    >

                        Profile Picture (Optional)

                        <input
                            class="field"
                            type="file"
                            id="avatar"
                            name="avatar"
                            accept="image/jpeg,image/png"
                        >

                        <small class="form-hint">
                            JPG or PNG, maximum 2 MB.
                        </small>

                        <span
                            class="form-error"
                            id="image-error"
                            role="alert"
                            hidden
                        ></span>

                    </label>

                    <!-- Form Buttons -->
                    <div
                        class="profile-form-wide
                               profile-form-actions"
                    >

                        <button
                            class="btn"
                            type="submit"
                        >
                            Save Profile
                        </button>

                        <button
                            class="btn secondary"
                            type="button"
                            id="cancel-edit"
                        >
                            Cancel
                        </button>

                    </div>

                </form>

                <!-- Preview Message -->
                <p
                    id="preview-message"
                    class="preview-message"
                    role="status"
                    hidden
                ></p>

            </div>

        </section>

    </main>

    <!-- RIGHT SIDEBAR -->
    <aside class="rightbar">

        <section class="panel right-panel">

            <h3>Explore Topics</h3>

            <p class="subtext">
                Discover ideas and connect
                with your community.
            </p>

        </section>

    </aside>

</div>

<!-- =========================
     JAVASCRIPT
========================= -->

<script>
    // Form elements
    const editButton =
        document.getElementById('edit-profile-button');

    const form =
        document.getElementById('profile-form');

    const cancelButton =
        document.getElementById('cancel-edit');

    const nameInput =
        document.getElementById('name');

    const emailInput =
        document.getElementById('email');

    const bioInput =
        document.getElementById('bio');

    const avatarInput =
        document.getElementById('avatar');

    const imageError =
        document.getElementById('image-error');

    const message =
        document.getElementById('preview-message');

    // Display elements
    const profileName =
        document.getElementById('profile-name');

    const profileEmail =
        document.getElementById('profile-email');

    const profileBio =
        document.getElementById('profile-bio');

    const sidebarName =
        document.getElementById('sidebar-name');

    const profileAvatar =
        document.getElementById('profile-avatar');

    const sidebarAvatar =
        document.getElementById('sidebar-avatar');

    const headerAvatar =
        document.getElementById('header-avatar');

    // All avatars should show the same image.
    const avatars = [
        profileAvatar,
        sidebarAvatar,
        headerAvatar
    ];

    // Keep reference to current image preview
    let avatarPreviewUrl = null;

    // Create initials from display name
    function getInitials(name) {

        const words = name.trim().split(/\s+/);

        if (words.length >= 2) {
            return (
                words[0][0] +
                words[1][0]
            ).toUpperCase();
        }

        return name.trim().substring(0, 2).toUpperCase();
    }

    // Update all avatar locations
    function updateAvatars(name, imageUrl = null) {

        avatars.forEach(function(avatar) {

            if (imageUrl) {

                const img = document.createElement('img');

                img.src = imageUrl;
                img.alt = 'Profile picture';
                img.className = 'avatar-photo';

                avatar.replaceChildren(img);

            } else if (!avatar.querySelector('img')) {

                avatar.textContent = getInitials(name);

            }

        });

    }

    // Show edit form
    editButton.addEventListener('click', function() {

        form.hidden = false;
        editButton.hidden = true;
        message.hidden = true;

        nameInput.focus();

    });

    // Cancel editing
    cancelButton.addEventListener('click', function() {

        // Restore last saved preview values
        form.reset();

        nameInput.setCustomValidity('');
        imageError.hidden = true;
        message.hidden = true;

        form.hidden = true;
        editButton.hidden = false;

    });

    // Check selected picture
    function validateImage() {

        imageError.hidden = true;
        avatarInput.setCustomValidity('');

        const file = avatarInput.files[0];

        if (!file) {
            return true;
        }

        const allowedTypes = [
            'image/jpeg',
            'image/png'
        ];

        const maxSize = 2 * 1024 * 1024;

        if (!allowedTypes.includes(file.type)) {

            imageError.textContent =
                'Only JPG or PNG images are allowed.';

            imageError.hidden = false;
            avatarInput.setCustomValidity(
                'Please choose a JPG or PNG image.'
            );

            return false;
        }

        if (file.size > maxSize) {

            imageError.textContent =
                'Image size must not exceed 2 MB.';

            imageError.hidden = false;
            avatarInput.setCustomValidity(
                'Image size must not exceed 2 MB.'
            );

            return false;
        }

        return true;
    }

    avatarInput.addEventListener(
        'change',
        validateImage
    );

    // Trimmed name must not be empty
    nameInput.addEventListener('input', function() {
        nameInput.setCustomValidity('');
    });

    // Handle form submission
    form.addEventListener('submit', function(event) {

        // Frontend preview only
        event.preventDefault();

        message.hidden = true;
        nameInput.setCustomValidity('');

        const newName = nameInput.value.trim();
        const newEmail = emailInput.value.trim();
        const newBio = bioInput.value.trim();

        // Required display name validation
        if (newName.length < 3) {

            nameInput.setCustomValidity(
                'Display name must contain at least 3 characters.'
            );

            nameInput.reportValidity();
            return;
        }

        if (!validateImage()) {
            avatarInput.reportValidity();
            return;
        }

        if (!form.reportValidity()) {
            return;
        }

        const selectedFile = avatarInput.files[0];

        // Update preview information
        profileName.textContent = newName;
        sidebarName.textContent = newName;
        profileEmail.textContent = newEmail;
        profileBio.textContent = newBio;

        // Update all avatar pictures
        if (selectedFile) {

            const newUrl =
                URL.createObjectURL(selectedFile);

            updateAvatars(newName, newUrl);

            if (avatarPreviewUrl) {
                URL.revokeObjectURL(avatarPreviewUrl);
            }

            avatarPreviewUrl = newUrl;

        } else {

            // Keep existing picture if one exists.
            updateAvatars(newName);

        }

        // Make current values the form defaults.
        // Cancel will return to these values.
        nameInput.defaultValue = newName;
        emailInput.defaultValue = newEmail;
        bioInput.defaultValue = newBio;

        form.reset();

        // Close form
        form.hidden = true;
        editButton.hidden = false;

        // Preview notification
        message.textContent =
            'Profile updated in preview only. ' +
            'No changes were saved to a database.';

        message.hidden = false;

    });
</script>

</body>
</html>
