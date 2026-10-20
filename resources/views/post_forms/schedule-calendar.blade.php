@php
    $calendarFormHasInput = old('from_calendar') !== null;
    $oldScheduledAt = $calendarFormHasInput ? (string) old('scheduled_at') : '';
@endphp

<section class="panel schedule-calendar" id="schedule-calendar" aria-label="Schedule calendar" data-schedule-calendar @unless (session('showScheduleCalendar')) hidden @endunless>
    <div class="calendar-head">
        <strong data-calendar-title></strong>
        <div class="calendar-nav">
            <button type="button" data-calendar-prev aria-label="Previous month">&lsaquo;</button>
            <button type="button" data-calendar-next aria-label="Next month">&rsaquo;</button>
        </div>
    </div>

    @if (session('showScheduleCalendar') && session('status'))
        <p class="calendar-status" role="status">{{ session('status') }}</p>
    @endif

    <div class="calendar-weekdays" aria-hidden="true">
        <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
    </div>
    <div class="calendar-grid" data-calendar-grid></div>
    <p class="calendar-hint">Pick a day to schedule a post.</p>
</section>

<dialog class="rp-dialog schedule-dialog" id="schedule-day-dialog" aria-labelledby="schedule-day-title">
    <div class="rp-dialog-heading">
        <h2 id="schedule-day-title">Scheduled posts for <span data-day-label></span></h2>
        <button type="button" class="rp-close" data-dialog-close aria-label="Close">&times;</button>
    </div>

    <div class="schedule-day-list" data-day-list></div>

    <div class="rp-actions">
        <button type="button" class="btn" data-day-add>+ Add new post</button>
        <button type="button" class="btn secondary" data-dialog-close>Close</button>
    </div>
</dialog>

<template id="schedule-day-item">
    <div class="schedule-day-item">
        <div class="schedule-day-copy">
            <small data-item-time></small>
            <p data-item-text></p>
            <span class="schedule-day-hashtag" data-item-hashtag></span>
        </div>
        <div class="schedule-day-actions">
            <button type="button" class="icon-button" data-item-edit aria-label="Edit scheduled post" title="Edit">
                <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false">
                    <path d="M4 20h4L19 9l-4-4L4 16z M13.5 6.5l4 4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                </svg>
            </button>
            <form method="POST" data-item-delete data-confirm="Cancel this scheduled post?">
                @csrf
                @method('DELETE')
                <input type="hidden" name="from_calendar" value="1">
                <button type="submit" class="icon-button schedule-delete" aria-label="Delete scheduled post" title="Delete">
                    <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false">
                        <path d="M4.5 7h15M9.5 7V4.5h5V7M6.5 7l1 13h9l1-13M10 11v5.5M14 11v5.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</template>

<dialog class="rp-dialog schedule-dialog" id="schedule-form-dialog" aria-labelledby="schedule-form-title" data-open-on-load="{{ $calendarFormHasInput && $errors->any() ? 'true' : 'false' }}">
    <div class="rp-dialog-heading">
        <div>
            <h2 id="schedule-form-title" data-form-title>Schedule a post</h2>
            <p class="subtext">Choose when your post should be published on <strong data-form-date></strong>.</p>
        </div>
        <button type="button" class="rp-close" data-dialog-close aria-label="Close">&times;</button>
    </div>

    @if ($calendarFormHasInput && $errors->any())
        <div class="form-errors schedule-form-errors" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('schedule-post.store') }}" enctype="multipart/form-data" data-schedule-form data-store-url="{{ route('schedule-post.store') }}">
        @csrf
        <input type="hidden" name="_method" value="PUT" data-method-field disabled>
        <input type="hidden" name="from_calendar" value="1">
        <input type="hidden" name="editing_post_id" value="{{ $calendarFormHasInput ? old('editing_post_id') : '' }}" data-editing-id>
        <input type="hidden" name="scheduled_at" value="{{ $oldScheduledAt }}" data-scheduled-at>

        <div class="rp-field-group">
            <label for="schedule-content">Post description <span class="rp-required">*</span></label>
            <textarea class="field" id="schedule-content" name="content" maxlength="1200" placeholder="Share your thoughts, questions or experiences..." required>{{ $calendarFormHasInput ? old('content') : '' }}</textarea>
            <span class="rp-count" data-content-count>0 / 1200</span>
        </div>

        <div class="schedule-form-row">
            <div class="rp-field-group">
                <label for="schedule-category">Category <span class="rp-required">*</span></label>
                <select class="field" id="schedule-category" name="category_id" required>
                    <option value="">Select category</option>
                    @foreach ($scheduleCategories ?? [] as $category)
                        <option value="{{ $category->category_id }}" @selected($calendarFormHasInput && old('category_id') == $category->category_id)>{{ $category->category_type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="rp-field-group">
                <label for="schedule-post-type">Post type <span class="rp-required">*</span></label>
                <select class="field" id="schedule-post-type" name="post_type" required>
                    @foreach (\App\Services\PostPublisher::POST_TYPES as $postType)
                        <option value="{{ $postType }}" @selected(($calendarFormHasInput ? old('post_type') : 'Discussion') === $postType)>{{ $postType }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="schedule-form-row">
            <div class="rp-field-group">
                <label for="schedule-time">Schedule time <span class="rp-required">*</span></label>
                <input class="field" id="schedule-time" type="time" value="{{ $oldScheduledAt !== '' ? substr($oldScheduledAt, 11, 5) : '' }}" required data-schedule-time>
            </div>
            <div class="rp-field-group">
                <label for="schedule-hashtags">Hashtags (optional)</label>
                <input class="field" id="schedule-hashtags" name="hashtag" type="text" maxlength="150" value="{{ $calendarFormHasInput ? old('hashtag') : '' }}" placeholder="#learning #knowvia">
            </div>
        </div>

        <div class="rp-field-group">
            <label for="schedule-media" data-media-label>Add photo or video (optional)</label>
            <input class="field" id="schedule-media" data-media-preview="schedule-media-preview" name="media[]" type="file" multiple accept="image/jpeg,image/png,image/webp,video/mp4,video/webm">
            <p class="subtext" data-media-hint>JPG, PNG or WEBP images up to 5 MB; MP4 or WEBM videos up to 25 MB; up to 10 files.</p>
            <div class="media-preview" id="schedule-media-preview" data-current-media="[]" hidden></div>
        </div>

        <div class="rp-actions">
            <button type="button" class="btn secondary" data-dialog-close>Cancel</button>
            <button type="submit" class="btn" data-form-submit>Schedule post</button>
        </div>
    </form>
</dialog>

<script type="application/json" id="schedule-calendar-data">@json($scheduleCalendar ?? ['today' => now()->format('Y-m-d'), 'posts' => []])</script>
