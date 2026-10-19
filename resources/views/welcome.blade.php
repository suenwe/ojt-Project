@extends('layouts.app')

@section('content')
<style>
    .post-report-menu-wrap { position: relative; align-self: flex-start; margin-top: -4px; margin-left: auto; flex-shrink: 0; }
    .post-report-menu-button { display: grid; place-items: center; width: 36px; height: 36px; padding: 0; color: var(--ink); background: transparent; border: 0; border-radius: 7px; font-size: var(--text-2xl); line-height: 1; }
    .post-report-menu-button:hover, .post-report-menu-button[aria-expanded="true"] { background: var(--brand-soft); }
    .post-report-menu { position: absolute; top: 40px; right: 0; min-width: 160px; z-index: 12; padding: 5px; background: var(--surface); border: 1px solid var(--line); border-radius: 8px; box-shadow: 0 8px 24px rgb(0 0 0 / 12%); }
    .post-report-menu button { display: block; width: 100%; padding: 10px 12px; color: var(--ink); background: transparent; border: 0; border-radius: 5px; text-align: left; font-size: var(--text-md); }
    .post-report-menu button:hover { background: var(--brand-soft); }
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
        <a class="brand" href="{{ url('/') }}" aria-label="Knowvia home">
            <span class="brand-mark">V</span>
            <span>Knowvia</span>
        </a>

        <div class="top-actions">
            <input class="theme-toggle" id="top-theme-toggle" type="checkbox">
            <label class="icon-button theme-toggle-label" for="top-theme-toggle" title="Toggle theme" aria-label="Toggle light and dark theme">◐</label>
            <details class="top-menu">
                <summary class="icon-button" aria-label="Open main menu">☰</summary>
                <nav aria-label="Main menu">
                    <a href="#posts">Posts</a>
                    <a href="#schedules">Schedules</a>
                    <a href="#bookmarks">Saved posts</a>
                    <a href="#help">Help and support</a>
                </nav>
            </details>
        </div>
    </header>

    <div class="app-layout">
        <aside class="sidebar">
            <section class="panel account-card">
                @if ($photoDataUri = auth()->user()->photoDataUri())
                    <img class="avatar avatar-large avatar-photo" src="{{ $photoDataUri }}" alt="{{ auth()->user()->username }} profile photo">
                @else
                    <span class="avatar avatar-large" aria-label="Member profile">{{ auth()->user()->initials() }}</span>
                @endif
                <div class="account-copy">
                    <strong>{{ auth()->user()->username }}</strong>
                </div>
                <div class="account-tools">
                    <span class="account-icon bell-icon" title="Notifications" aria-label="Notifications"></span>
                    <details class="account-settings">
                        <summary class="account-icon" title="Settings" aria-label="Open settings">⚙</summary>
                        <nav aria-label="Account settings">
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
            </section>
        </aside>

        <main class="main-column" id="home">
            @unless ($showProfileSettings ?? false)
            <div class="feed-toolbar">
                <form class="search-box" action="#" method="GET" role="search">
                    <span aria-hidden="true">⌕</span>
                    <input type="search" name="search" placeholder="Search posts and hashtags" aria-label="Search posts and hashtags">
                    <button class="search-submit" type="submit" aria-label="Submit search">↵</button>
                </form>
            </div>

            <div class="feed-actions">
                <div class="feed-action-buttons">
                    <a class="btn" href="{{ route('posts.create') }}">Create post</a>
                    <a class="btn secondary" href="{{ route('posts.schedule') }}">Schedule post</a>
                </div>
                <label class="sort-control" for="post-sort">
                    Sort by:
                    <select class="field filter-select" id="post-sort" name="sort" aria-label="Sort posts">
                        <option value="category">Category</option>
                        <option value="newest">Newest</option>
                        <option value="reacted">Most reacted</option>
                    </select>
                    <select class="field category-filter" name="category" aria-label="Filter by topic">
                        <option>All topics</option>
                        <option>Technology</option>
                        <option>Community</option>
                        <option>Education</option>
                    </select>
                </label>
            </div>
            @endunless

            @if ($showProfileSettings ?? false)
                @include('profile_forms.settings', ['user' => auth()->user()])
            @elseif ($hidePosts ?? false)
                @include('post_forms.form', ['isScheduling' => $isScheduling ?? false])
            @else
                @if (session('status'))
                    <p class="form-status" role="status">{{ session('status') }}</p>
                @endif

                <section class="feed-list" aria-label="Latest posts">
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
                <h3>Explore</h3>
                <p class="subtext">Browse posts by hashtag.</p>
                <div class="category-list">
                    <a class="category-link" href="#laravel"><span>#laravel</span><strong>12</strong></a>
                    <a class="category-link" href="#postgresql"><span>#postgresql</span><strong>8</strong></a>
                    <a class="category-link" href="#webdevelopment"><span>#webdevelopment</span><strong>5</strong></a>
                </div>
            </section>
        </aside>
    </div>

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
