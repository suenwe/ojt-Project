
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Vibely | Schedule Post</title>

    <!-- Shared Vibely Design -->
    <link rel="stylesheet"
          href="{{ asset('assets/post-prototype.css') }}">

    <style>
        /* Page layout */
        .schedule-main {
            display: grid;
            gap: 18px;
            min-width: 0;
        }

        .schedule-card,
        .scheduled-post {
            padding: 22px;
        }

        .schedule-heading {
            margin-bottom: 22px;
        }

        .schedule-heading h2 {
            font-size: 20px;
            margin-bottom: 5px;
        }

        /* Form fields */
        .schedule-group {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 7px;
            min-width: 0;
            margin-bottom: 18px;
        }

        .schedule-group label {
            display: block;
            margin: 0;
            font-size: 13px;
            font-weight: 650;
        }

        .schedule-group .field {
            display: block;
            width: 100%;
            min-width: 0;
            min-height: 44px;
            padding: 11px 12px;
            margin: 0;
            font-size: 14px;
        }

        .schedule-group textarea {
            min-height: 130px;
            resize: vertical;
        }

        /* Two-column fields */
        .schedule-row {
            display: grid;
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
            align-items: start;
            gap: 16px;
        }

        .schedule-row .schedule-group {
            margin-bottom: 18px;
        }

        .schedule-row select,
        .schedule-row input {
            height: 46px;
        }

        .required-star {
            color: var(--red);
        }

        .form-hint,
        .character-count {
            margin: 0;
            color: var(--muted);
            font-size: 11px;
            line-height: 1.6;
        }

        .character-count {
            text-align: right;
        }

        .form-error {
            margin: 0;
            color: var(--red);
            font-size: 12px;
        }

        .schedule-info {
            background: var(--brand-soft);
            color: var(--brand);
            padding: 12px;
            border-radius: 7px;
            font-size: 12px;
            margin-bottom: 18px;
        }

        /* Uploaded media preview */
        .media-preview {
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 7px;
            background: var(--page);
        }

        .media-preview img,
        .media-preview video,
        .scheduled-media img,
        .scheduled-media video {
            display: block;
            width: 100%;
            height: min(70vh, 620px);
            object-fit: contain;
            border-radius: 6px;
        }

        /* Buttons */
        .schedule-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 22px;
        }

        .schedule-actions .btn {
            min-width: 125px;
        }

        /* Scheduled posts */
        .scheduled-list {
            display: grid;
            gap: 14px;
        }

        .scheduled-post {
            display: grid;
            gap: 12px;
        }

        .scheduled-post-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .scheduled-date {
            font-size: 12px;
            color: var(--brand);
            font-weight: 650;
        }

        .scheduled-category {
            display: inline-block;
            width: fit-content;
            background: var(--brand-soft);
            color: var(--brand);
            padding: 5px 9px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 650;
        }

        .scheduled-content {
            margin: 0;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .scheduled-tags {
            color: var(--brand);
            font-size: 13px;
            overflow-wrap: anywhere;
        }

        .preview-message {
            padding: 12px;
            background: var(--green-soft);
            color: var(--green);
            border-radius: 6px;
            font-size: 12px;
        }

        .nav-placeholder {
            display: block;
            padding: 9px 11px;
            color: var(--muted);
            font-weight: 600;
        }

        [hidden] {
            display: none !important;
        }

        /* Mobile */
        @media (max-width: 700px) {
            .schedule-row {
                grid-template-columns: 1fr;
            }

            .schedule-card,
            .scheduled-post {
                padding: 16px;
            }

            .schedule-actions {
                flex-wrap: wrap;
            }

            .schedule-actions .btn {
                flex: 1;
            }
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<header class="topbar">

    <a class="brand" href="#schedule">
        <span class="brand-mark">V</span>
        Vibely
    </a>

    <div class="top-actions">

        <a class="top-link"
           href="/ui-preview/create-post">
            Create Post
        </a>

        <span class="avatar">AM</span>

    </div>

</header>

<!-- APP LAYOUT -->
<div class="app-layout">

    <!-- LEFT SIDEBAR -->
    <aside class="sidebar">

        <div class="panel side-panel">

            <p class="side-heading">
                Community
            </p>

            <nav class="side-nav">

                <a href="/ui-preview/create-post">
                    Home Feed
                </a>

                <span class="nav-placeholder">
                    Explore Topics
                </span>

                <a href="/ui-preview/edit-profile">
                    My Profile
                </a>

                <a class="active" href="#schedule">
                    Schedule Post
                </a>

            </nav>

        </div>

        <div class="panel account-card">

            <span class="avatar">AM</span>

            <div>
                <strong>Alex Morgan</strong>
                <small>Community Member</small>
            </div>

        </div>

    </aside>

    <!-- MAIN CONTENT -->
    <main class="schedule-main" id="schedule">

        <div class="page-heading">

            <div>
                <h1>Schedule a Post</h1>

                <p class="subtext">
                    Plan your community posts in advance.
                </p>
            </div>

        </div>

        <!-- SCHEDULE POST FORM -->
        <section class="panel schedule-card">

            <div class="schedule-heading">

                <h2>Create Scheduled Post</h2>

                <p class="subtext">
                    Choose when your post should be published.
                </p>

            </div>

            <div class="schedule-info">
                Schedule your post for a future
                date and time.
            </div>

            <!-- Frontend Preview Only -->
            <form id="schedule-post-form" method="POST" action="{{ route('schedule-post.store') }}" enctype="multipart/form-data">
                @csrf

                @if ($errors->any())
                    <p class="form-error" role="alert">{{ $errors->first() }}</p>
                @endif

                <!-- DESCRIPTION -->
                <div class="schedule-group">

                    <label for="content">
                        Post Description
                        <span class="required-star">*</span>
                    </label>

                    <textarea
                        class="field"
                        id="content"
                        name="content"
                        placeholder="What would you like to share?"
                        maxlength="1200"
                        required
                    >{{ old('content') }}</textarea>

                    <p
                        class="character-count"
                        id="character-count"
                    >
                        0 / 1200
                    </p>

                </div>

                <!-- CATEGORY + HASHTAGS -->
                <div class="schedule-row">

                    <div class="schedule-group">

                        <label for="category">
                            Category
                            <span class="required-star">*</span>
                        </label>

                        <select
                            class="field"
                            id="category"
                            name="category"
                            required
                        >
                            <option value="">
                                Select category
                            </option>

                            <option value="Technology">Technology</option>
                            <option value="Education">Education</option>
                            <option value="Gaming">Gaming</option>
                            <option value="Entertainment">Entertainment</option>
                            <option value="Health &amp; Fitness">Health &amp; Fitness</option>
                            <option value="Business &amp; Career">Business &amp; Career</option>
                            <option value="Travel">Travel</option>
                            <option value="Art &amp; Design">Art &amp; Design</option>
                            <option value="Fashion &amp; Lifestyle">Fashion &amp; Lifestyle</option>
                            <option value="Food &amp; Cooking">Food &amp; Cooking</option>
                            <option value="Sports">Sports</option>
                            <option value="General Discussion">General Discussion</option>
                            <option value="Events &amp; Activities">Events &amp; Activities</option>
                            <option value="Books &amp; Literature">Books &amp; Literature</option>
                            <option value="News &amp; Updates">News &amp; Updates</option>
                        </select>

                    </div>

                    <div class="schedule-group">

                        <label for="hashtags">
                            Hashtags (Optional)
                        </label>

                        <input
                            class="field"
                            type="text"
                            id="hashtags"
                            name="hashtags"
                            placeholder="#learning #vibely"
                            maxlength="150"
                            value="{{ old('hashtags') }}"
                        >

                    </div>

                </div>

                <!-- PHOTO OR VIDEO -->
                <div class="schedule-group">

                    <label for="media">
                        Add Photo or Video (Optional)
                    </label>

                    <input
                        class="field"
                        type="file"
                        id="media"
                        name="media"
                        accept="image/jpeg,image/png,image/webp,video/mp4,video/webm"
                    >

                    <p class="form-hint">
                        Images: JPG, PNG, WebP (max 5 MB).
                        Videos: MP4, WebM (max 25 MB).
                        One file per post.
                    </p>

                    <p
                        class="form-error"
                        id="media-error"
                        role="alert"
                        hidden
                    ></p>

                    <div
                        class="media-preview"
                        id="media-preview"
                        hidden
                    ></div>

                </div>

                <!-- DATE + TIME -->
                <div class="schedule-row">

                    <div class="schedule-group">

                        <label for="schedule-date">
                            Schedule Date
                            <span class="required-star">*</span>
                        </label>

                        <input
                            class="field"
                            type="date"
                            id="schedule-date"
                            name="schedule_date"
                            value="{{ old('schedule_date') }}"
                            required
                        >

                    </div>

                    <div class="schedule-group">

                        <label for="schedule-time">
                            Schedule Time
                            <span class="required-star">*</span>
                        </label>

                        <input
                            class="field"
                            type="time"
                            id="schedule-time"
                            name="schedule_time"
                            value="{{ old('schedule_time') }}"
                            required
                        >

                    </div>

                </div>

                <p
                    class="form-error"
                    id="schedule-error"
                    role="alert"
                    hidden
                ></p>

                <!-- BUTTONS -->
                <div class="schedule-actions">

                    <button
                        class="btn secondary"
                        type="reset"
                    >
                        Clear
                    </button>

                    <button
                        class="btn"
                        type="submit"
                    >
                        Schedule Post
                    </button>

                </div>

            </form>

        </section>

        <!-- SCHEDULED POSTS PREVIEW -->
        <section>

            <h2>Scheduled Posts</h2>

                <p class="subtext">
                    Your scheduled posts appear here.
            </p>

        </section>

        <p
            class="preview-message"
            id="preview-message"
            role="status"
            @if (!session('status')) hidden @endif
        >{{ session('status') }}</p>

        <div
            class="scheduled-list"
            id="scheduled-list"
            aria-live="polite"
        >
            @forelse ($scheduledPosts as $scheduledPost)
                <article class="panel scheduled-post">
                    <div class="scheduled-post-header">
                        <strong>{{ $scheduledPost->user?->username ?? 'Community Member' }}</strong>
                        <span class="scheduled-date">Scheduled for {{ $scheduledPost->date_time->format('M j, Y g:i A') }}</span>
                    </div>
                    <span class="scheduled-category">{{ $scheduledPost->category?->category_type ?? 'General Discussion' }}</span>
                    <p class="scheduled-content">{{ $scheduledPost->descriptions }}</p>
                    @if ($scheduledPost->hashtag)
                        <p class="scheduled-tags">{{ $scheduledPost->hashtag }}</p>
                    @endif
                    @foreach ($scheduledPost->media as $postMedia)
                        <div class="scheduled-media">
                            @if (str_starts_with($postMedia->mime_type, 'image/'))
                                <img src="{{ route('post-media.show', $postMedia) }}" alt="Scheduled post image">
                            @else
                                <video src="{{ route('post-media.show', $postMedia) }}" controls></video>
                            @endif
                        </div>
                    @endforeach
                    <form method="POST" action="{{ route('schedule-post.destroy', $scheduledPost->post_id) }}" onsubmit="return confirm('Cancel this scheduled post?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn secondary" type="submit">Cancel Schedule</button>
                    </form>
                </article>
            @empty
                <p class="subtext">No scheduled posts yet.</p>
            @endforelse
        </div>

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

<!-- JAVASCRIPT -->
<script>
    const form = document.getElementById(
        'schedule-post-form'
    );

    const content = document.getElementById(
        'content'
    );

    const category = document.getElementById(
        'category'
    );

    const hashtags = document.getElementById(
        'hashtags'
    );

    const media = document.getElementById(
        'media'
    );

    const dateInput = document.getElementById(
        'schedule-date'
    );

    const timeInput = document.getElementById(
        'schedule-time'
    );

    const counter = document.getElementById(
        'character-count'
    );

    const mediaError = document.getElementById(
        'media-error'
    );

    const scheduleError = document.getElementById(
        'schedule-error'
    );

    const mediaPreview = document.getElementById(
        'media-preview'
    );

    const message = document.getElementById(
        'preview-message'
    );

    let mediaPreviewUrl = null;

    // Get today's date using browser local time
    function localDateString() {
        const now = new Date();

        const year = now.getFullYear();

        const month = String(
            now.getMonth() + 1
        ).padStart(2, '0');

        const day = String(
            now.getDate()
        ).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }

    dateInput.min = localDateString();

    // Character counter
    content.addEventListener('input', function() {
        counter.textContent =
            content.value.length + ' / 1200';

        content.setCustomValidity('');
        message.hidden = true;
    });

    // Validate photo or video
    function validateMedia(file) {
        media.setCustomValidity('');
        mediaError.hidden = true;

        if (!file) return true;

        const imageTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        const videoTypes = [
            'video/mp4',
            'video/webm'
        ];

        let maxSize = 0;

        if (imageTypes.includes(file.type)) {
            maxSize = 5 * 1024 * 1024;

        } else if (videoTypes.includes(file.type)) {
            maxSize = 25 * 1024 * 1024;

        } else {
            const error =
                'Please choose a JPG, PNG, WebP, MP4 or WebM file.';

            mediaError.textContent = error;
            mediaError.hidden = false;
            media.setCustomValidity(error);

            return false;
        }

        if (file.size > maxSize) {
            const error =
                'Selected media file is too large.';

            mediaError.textContent = error;
            mediaError.hidden = false;
            media.setCustomValidity(error);

            return false;
        }

        return true;
    }

    // Media preview
    media.addEventListener('change', function() {
        if (mediaPreviewUrl) {
            URL.revokeObjectURL(mediaPreviewUrl);
            mediaPreviewUrl = null;
        }

        mediaPreview.replaceChildren();
        mediaPreview.hidden = true;

        const file = media.files[0];

        if (!file || !validateMedia(file)) {
            return;
        }

        mediaPreviewUrl = URL.createObjectURL(file);

        let element;

        if (file.type.startsWith('image/')) {
            element = document.createElement('img');
            element.alt = 'Selected image preview';
        } else {
            element = document.createElement('video');
            element.controls = true;
        }

        element.src = mediaPreviewUrl;
        mediaPreview.appendChild(element);
        mediaPreview.hidden = false;
    });

    // Clear date/time errors when changed
    [dateInput, timeInput].forEach(function(input) {
        input.addEventListener('input', function() {
            dateInput.setCustomValidity('');
            timeInput.setCustomValidity('');
            scheduleError.hidden = true;
            message.hidden = true;
        });
    });

    // Validate schedule date and time
    function validateSchedule() {
        dateInput.setCustomValidity('');
        timeInput.setCustomValidity('');
        scheduleError.hidden = true;

        if (!dateInput.value || !timeInput.value) {
            return false;
        }

        const scheduledAt = new Date(
            dateInput.value + 'T' + timeInput.value
        );

        if (
            !Number.isFinite(scheduledAt.getTime()) ||
            scheduledAt <= new Date()
        ) {
            const error =
                'Please select a future date and time.';

            scheduleError.textContent = error;
            scheduleError.hidden = false;

            timeInput.setCustomValidity(error);

            return false;
        }

        return true;
    }

    // Reset selected form values
    form.addEventListener('reset', function() {
        if (mediaPreviewUrl) {
            URL.revokeObjectURL(mediaPreviewUrl);
            mediaPreviewUrl = null;
        }

        mediaPreview.replaceChildren();
        mediaPreview.hidden = true;

        media.setCustomValidity('');
        dateInput.setCustomValidity('');
        timeInput.setCustomValidity('');
        content.setCustomValidity('');

        mediaError.hidden = true;
        scheduleError.hidden = true;

        counter.textContent = '0 / 1200';
        message.hidden = true;

        dateInput.min = localDateString();
    });

    // Submit validated form data for server-side persistence.
    form.addEventListener('submit', function(event) {
        event.preventDefault();

        content.setCustomValidity('');

        const postText = content.value.trim();

        if (!postText) {
            content.setCustomValidity(
                'Post description is required.'
            );

            content.reportValidity();
            return;
        }

        const file = media.files[0];

        if (!validateMedia(file)) {
            media.reportValidity();
            return;
        }

        if (!validateSchedule()) {
            if (!dateInput.value || !timeInput.value) {
                form.reportValidity();
            } else {
                timeInput.reportValidity();
            }
            return;
        }

        if (!form.reportValidity()) {
            return;
        }

        form.submit();
    });
</script>

</body>
</html>
