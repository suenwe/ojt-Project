@extends('layouts.app')

@section('content')
<style>
    .post-report-menu-wrap { position: relative; align-self: flex-start; margin-top: -4px; margin-left: auto; flex-shrink: 0; }
    .post-report-menu-button { display: grid; place-items: center; width: 36px; height: 36px; padding: 0; color: var(--ink); background: transparent; border: 0; border-radius: 7px; font-size: var(--text-2xl); line-height: 1; }
    .post-report-menu-button:hover, .post-report-menu-button[aria-expanded="true"] { background: var(--brand-soft); }
    .post-report-menu { position: absolute; top: 40px; right: 0; min-width: 160px; z-index: 12; padding: 5px; background: var(--surface); border: 1px solid var(--line); border-radius: 8px; box-shadow: 0 8px 24px rgb(0 0 0 / 12%); }
    .post-report-menu button, .post-report-menu a { display: block; width: 100%; padding: 10px 12px; color: var(--ink); background: transparent; border: 0; border-radius: 5px; text-align: left; font-size: var(--text-md); }
    .post-report-menu button:hover, .post-report-menu a:hover { background: var(--brand-soft); }
    .post-report-menu .post-menu-danger { color: var(--red); }
    .rp-dialog { position: fixed; inset: 50% auto auto 50%; width: min(510px, calc(100% - 28px)); max-height: 90vh; margin: 0; overflow-y: auto; padding: 24px; color: var(--ink); background: var(--surface); border: 1px solid var(--line); border-radius: 12px; box-shadow: 0 16px 45px rgb(0 0 0 / 20%); transform: translate(-50%, -50%); }
    .rp-dialog::backdrop { background: rgb(10 16 40 / 60%); }
    .rp-dialog-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; margin-bottom: 18px; }
    .rp-dialog-heading h2 { margin-bottom: 3px; font-size: var(--text-xl); }
    .rp-close { border: 0; background: transparent; color: var(--muted); font-size: var(--text-2xl); line-height: 1; padding: 2px 6px; }
    .rp-field-group { display: flex; flex-direction: column; gap: 7px; margin-bottom: 16px; }
    .rp-field-group label { font-size: var(--text-md); font-weight: 700; }
    .rp-field-group .field { display: block; width: 100%; min-height: 44px; margin: 0; }
    .rp-field-group textarea.field { min-height: 110px; resize: vertical; }
    .rp-required { color: var(--red); }
    .rp-count { text-align: right; font-size: var(--text-xs); color: var(--muted); }
    .rp-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 9px; margin-top: 16px; }
    .rp-actions .btn { min-width: 105px; }
    .rp-privacy { padding: 10px 12px; color: var(--muted); background: var(--page); border-radius: 7px; font-size: var(--text-sm); }
    .rp-result { padding: 13px 15px; color: var(--green); background: var(--green-soft); border-radius: 7px; }
    [hidden] { display: none !important; }
    @media (max-width: 700px) {
        .rp-dialog { padding: 18px; }
        .rp-actions .btn { flex: 1; }
    }
</style>

    <header class="topbar">
        <div class="topbar-inner">
            <a class="brand" href="{{ url('/') }}" aria-label="Knowvia home">
                <span class="brand-mark">K</span>
                <span>Knowvia</span>
            </a>

            <form class="search-box top-search" action="{{ route('home') }}" method="GET" role="search" data-feed-search-form>
                <span aria-hidden="true">⌕</span>
                <input type="search" name="search" placeholder="Search posts, people and hashtags" aria-label="Search posts, people and hashtags" data-feed-search>
            </form>

            <div class="top-actions">
                <div class="notification-bell" data-notification-bell data-list-url="{{ route('notifications.index') }}" data-count-url="{{ route('notifications.unread-count') }}" data-read-url="{{ route('notifications.read') }}">
                    <button class="icon-button notification-toggle" type="button" title="Notifications" aria-label="Notifications" aria-expanded="false" aria-controls="notification-panel" data-notification-toggle>
                        <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false">
                            <path d="M6 16.5V11a6 6 0 0 1 12 0v5.5l1.5 2h-15z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M10 20.5a2 2 0 0 0 4 0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        <span class="notification-badge" data-notification-badge @unless ($unreadNotificationCount ?? 0) hidden @endunless>{{ ($unreadNotificationCount ?? 0) > 99 ? '99+' : ($unreadNotificationCount ?? 0) }}</span>
                    </button>
                    <section class="panel notification-panel" id="notification-panel" aria-label="Notifications" data-notification-panel hidden>
                        <h2 class="notification-panel-title">Notifications</h2>
                        <div data-notification-list></div>
                    </section>
                </div>
                <input class="theme-toggle" id="top-theme-toggle" type="checkbox">
                <label class="icon-button theme-toggle-label" for="top-theme-toggle" title="Toggle theme" aria-label="Toggle light and dark theme">◐</label>
                <details class="account-settings">
                    <summary class="icon-button" title="Account" aria-label="Open account menu">
                        <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false">
                            <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                        </svg>
                    </summary>
                    <nav aria-label="Account menu">
                        <a href="{{ route('profile.edit') }}">Profile settings</a>
                        @if (auth()->user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}">Admin dashboard</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="account-menu-button" type="submit">Sign out</button>
                        </form>
                    </nav>
                </details>
            </div>
        </div>
    </header>

    <div class="pull-refresh" data-pull-refresh aria-hidden="true">
        @include('partials.refresh-icon')
    </div>

    <div class="app-layout">
        <aside class="sidebar">
            <a class="panel account-card" href="{{ route('users.show', auth()->user()) }}">
                @if ($photoDataUri = auth()->user()->photoDataUri())
                    <img class="avatar avatar-large avatar-photo" src="{{ $photoDataUri }}" alt="" aria-hidden="true">
                @else
                    <span class="avatar avatar-large" aria-hidden="true">{{ auth()->user()->initials() }}</span>
                @endif
                <div class="account-copy">
                    <strong>{{ auth()->user()->username }}</strong>
                    <span class="subtext">View your profile</span>
                </div>
            </a>

            <nav class="panel main-nav" aria-label="Main menu">
                @foreach ([
                    ['Home', route('home'), request()->routeIs('home')],
                    ['My profile', route('users.show', auth()->user()), request()->routeIs('users.show') && ($isOwnProfile ?? false)],
                ] as [$navLabel, $navUrl, $isCurrentPage])
                    <a class="{{ $isCurrentPage ? 'is-active' : '' }}" href="{{ $navUrl }}" @if ($isCurrentPage) aria-current="page" @endif>{{ $navLabel }}</a>
                @endforeach
            </nav>
        </aside>

        <main class="main-column" id="home">
            @unless (request()->routeIs('home'))
                @include('partials.page-back', ['fallback' => route('home')])
            @endunless

            @if (request()->routeIs('home'))
            @php($composerStartsOpen = old('content') !== null && old('from_calendar') === null)
            <section class="panel composer-prompt" aria-label="Create a post" data-composer-prompt @if ($composerStartsOpen) hidden @endif>
                @if ($photoDataUri)
                    <img class="avatar avatar-photo" src="{{ $photoDataUri }}" alt="" aria-hidden="true">
                @else
                    <span class="avatar" aria-hidden="true">{{ auth()->user()->initials() }}</span>
                @endif
                <button class="composer-prompt-input" type="button" data-composer-open>What's on your mind?</button>
                <label class="icon-button" for="post-media" title="Add photos or videos" aria-label="Add photos or videos">
                    <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false">
                        <rect x="3.5" y="4.5" width="17" height="15" rx="2.5" fill="none" stroke="currentColor" stroke-width="1.8"/>
                        <circle cx="9" cy="9.5" r="1.6" fill="none" stroke="currentColor" stroke-width="1.6"/>
                        <path d="M4 17l5-5 4 4 2.5-2.5L20 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    </svg>
                </label>
            </section>

            <div class="composer-inline" data-composer-form @unless ($composerStartsOpen) hidden @endunless>
                @include('post_forms.form', ['isScheduling' => false])
            </div>

            <div class="feed-sort">
                <label class="sort-control" for="post-sort">
                    <select class="sort-select" id="post-sort" name="sort" aria-label="Sort posts">
                        <option value="newest">Newest</option>
                        <option value="category">By topic</option>
                    </select>
                    <select class="sort-select category-filter" name="category" aria-label="Filter by topic" data-feed-category>
                        <option value="">All topics</option>
                        @foreach ($categories ?? [] as $category)
                            <option value="{{ $category->category_type }}">{{ $category->category_type }}</option>
                        @endforeach
                    </select>
                </label>
                @include('partials.feed-refresh-button')
            </div>
            @endif

            @if ($showProfileSettings ?? false)
                @include('profile_forms.settings', ['user' => auth()->user()])
            @elseif (isset($member))
                @include('profile_forms.show')
            @elseif ($hidePosts ?? false)
                @include('post_forms.form', ['isScheduling' => $isScheduling ?? false])
            @else
                @if (session('status') && ! session('showScheduleCalendar'))
                    <p class="form-status" role="status">{{ session('status') }}</p>
                @endif

                <section class="feed-list" aria-label="Latest posts" data-live-feed>
                    @forelse ($posts ?? [] as $post)
                        @include('post_forms.card', ['post' => $post])
                    @empty
                        <div class="panel empty-feed">
                            <h2>No posts yet</h2>
                            <p class="subtext">Be the first to share something with the community.</p>
                            <a class="btn" href="{{ route('posts.create') }}">Create post</a>
                        </div>
                    @endforelse
                </section>
            @endif

        </main>

        <aside class="rightbar">
            <section class="panel right-panel">
                <h3>Popular hashtags</h3>
                <p class="subtext">From posts in the last 30 days.</p>
                <div class="category-list">
                    @forelse ($popularHashtags as $hashtag => $postCount)
                        <a class="category-link" href="#" data-search-hashtag="{{ $hashtag }}"><span>{{ $hashtag }}</span><strong>{{ $postCount }}</strong></a>
                    @empty
                        <p class="subtext">Hashtags from new posts will show up here.</p>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>

    <a
        class="schedule-fab {{ request()->routeIs('posts.schedule') ? 'is-active' : '' }}"
        href="{{ route('posts.schedule') }}"
        title="Schedule a post"
        aria-label="Schedule a post"
        aria-controls="schedule-calendar"
        aria-expanded="{{ session('showScheduleCalendar') ? 'true' : 'false' }}"
        data-schedule-toggle
    >
        <svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">
            <rect x="3.5" y="5" width="17" height="15.5" rx="2.5" fill="none" stroke="currentColor" stroke-width="1.8"/>
            <path d="M3.5 9.5h17M8 3v4M16 3v4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M12 12.5v3l2 1.2" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </a>

    @include('post_forms.schedule-calendar')

    <dialog class="rp-dialog people-dialog" id="people-dialog" aria-labelledby="people-dialog-title">
        <div class="rp-dialog-heading">
            <h2 id="people-dialog-title" data-people-title></h2>
            <button type="button" class="rp-close" data-people-close aria-label="Close">&times;</button>
        </div>
        <div data-people-body></div>
    </dialog>

    <dialog class="rp-dialog" id="post-report-dialog" aria-labelledby="post-report-title">
        <div class="rp-dialog-heading">
            <div>
                <h2 id="post-report-title">Report Post</h2>
                <p class="subtext">Tell us why you're reporting this post.</p>
            </div>
            <button type="button" class="rp-close" id="close-post-report" aria-label="Close report form">&times;</button>
        </div>

        <form id="post-report-form" method="POST" action="{{ route('report.store') }}">
            @csrf
            <input type="hidden" id="post-report-id" name="post_id" value="{{ old('post_id') }}">
            <div class="rp-field-group">
                <label for="post-report-reason">Report Reason <span class="rp-required">*</span></label>
                <select class="field" id="post-report-reason" name="report_type" required>
                    <option value="">Select a reason</option>
                    <option value="Problem involving someone under 18" @selected(old('report_type') === 'Problem involving someone under 18')>Problem involving someone under 18</option>
                    <option value="Bullying, harassment or abuse" @selected(old('report_type') === 'Bullying, harassment or abuse')>Bullying, harassment or abuse</option>
                    <option value="Suicide or self-harm" @selected(old('report_type') === 'Suicide or self-harm')>Suicide or self-harm</option>
                    <option value="Violent, hateful or disturbing content" @selected(old('report_type') === 'Violent, hateful or disturbing content')>Violent, hateful or disturbing content</option>
                    <option value="Selling or promoting restricted items" @selected(old('report_type') === 'Selling or promoting restricted items')>Selling or promoting restricted items</option>
                    <option value="Adult content" @selected(old('report_type') === 'Adult content')>Adult content</option>
                    <option value="Scam, fraud or false information" @selected(old('report_type') === 'Scam, fraud or false information')>Scam, fraud or false information</option>
                    <option value="Intellectual property" @selected(old('report_type') === 'Intellectual property')>Intellectual property</option>
                    <option value="I don&#x27;t want to see this" @selected(old('report_type') === "I don't want to see this")>I don't want to see this</option>
                </select>
            </div>

            @error('report_type')<p class="form-error" role="alert">{{ $message }}</p>@enderror
            @error('post_id')<p class="form-error" role="alert">{{ $message }}</p>@enderror
            <div class="rp-field-group">
                <label for="post-report-details">Additional Details (Optional)</label>
                <textarea class="field" id="post-report-details" name="reason" maxlength="500" placeholder="Describe your concern (optional)...">{{ old('reason') }}</textarea>
                <span class="rp-count" id="post-report-count">0 / 500</span>
            </div>
            @error('reason')<p class="form-error" role="alert">{{ $message }}</p>@enderror

            <p class="rp-privacy">Reports are sent to the admin team for review.</p>

            <div class="rp-actions">
                <button type="button" class="btn secondary" id="cancel-post-report">Cancel</button>
                <button type="submit" class="btn">Submit Report</button>
            </div>
        </form>
    </dialog>
    <p class="rp-result" id="post-report-result" role="status" hidden></p>

    @include('partials.confirmation-dialog')

    <script>
        (() => {
            'use strict';

            const dialog = document.getElementById('post-report-dialog');
            const closeReport = document.getElementById('close-post-report');
            const cancelReport = document.getElementById('cancel-post-report');
            const form = document.getElementById('post-report-form');
            const reason = document.getElementById('post-report-reason');
            const details = document.getElementById('post-report-details');
            const count = document.getElementById('post-report-count');
            const result = document.getElementById('post-report-result');
            const reportPostId = document.getElementById('post-report-id');

            @if ($errors->has('report_type') || $errors->has('post_id') || $errors->has('reason'))
                dialog.showModal();
            @endif

            function closeAllMenus(exceptWrap) {
                document.querySelectorAll('.post-report-menu-wrap').forEach((wrap) => {
                    if (wrap === exceptWrap) return;
                    wrap.querySelector('.post-report-menu').hidden = true;
                    wrap.querySelector('.post-report-menu-button').setAttribute('aria-expanded', 'false');
                });
            }

            function hideReport() {
                if (dialog.open) dialog.close();
            }

            document.addEventListener('click', (event) => {
                const toggle = event.target.closest('.post-report-menu-button');
                const openReport = event.target.closest('.open-post-report');

                if (toggle) {
                    const wrap = toggle.closest('.post-report-menu-wrap');
                    const menu = wrap.querySelector('.post-report-menu');
                    closeAllMenus(wrap);
                    menu.hidden = !menu.hidden;
                    toggle.setAttribute('aria-expanded', String(!menu.hidden));
                    return;
                }

                if (openReport) {
                    closeAllMenus(null);
                    form.reset();
                    reportPostId.value = openReport.dataset.postId;
                    count.textContent = '0 / 500';
                    dialog.showModal();
                    reason.focus();
                    return;
                }

                if (!event.target.closest('.post-report-menu-wrap')) closeAllMenus(null);
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') closeAllMenus(null);
            });

            closeReport.addEventListener('click', hideReport);
            cancelReport.addEventListener('click', hideReport);

            dialog.addEventListener('click', (event) => {
                if (event.target === dialog) hideReport();
            });

            details.addEventListener('input', () => {
                count.textContent = details.value.length + ' / 500';
            });

        })();
    </script>
@endsection
