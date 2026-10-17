@extends('layouts.app')

@section('content')
<style>
    .post-report-menu-wrap { position: relative; align-self: flex-start; margin-top: -4px; margin-left: auto; flex-shrink: 0; }
    .post-report-menu-button { display: grid; place-items: center; width: 36px; height: 36px; padding: 0; color: var(--ink); background: transparent; border: 0; border-radius: 7px; font-size: 24px; line-height: 1; }
    .post-report-menu-button:hover, .post-report-menu-button[aria-expanded="true"] { background: var(--brand-soft); }
    .post-report-menu { position: absolute; top: 40px; right: 0; min-width: 160px; z-index: 12; padding: 5px; background: var(--surface); border: 1px solid var(--line); border-radius: 8px; box-shadow: 0 8px 24px rgb(0 0 0 / 12%); }
    .post-report-menu button { display: block; width: 100%; padding: 10px 12px; color: var(--ink); background: transparent; border: 0; border-radius: 5px; text-align: left; font-size: 13px; }
    .post-report-menu button:hover { background: var(--brand-soft); }
    .rp-dialog { position: fixed; inset: 50% auto auto 50%; width: min(510px, calc(100% - 28px)); max-height: 90vh; margin: 0; overflow-y: auto; padding: 24px; color: var(--ink); background: var(--surface); border: 1px solid var(--line); border-radius: 12px; box-shadow: 0 16px 45px rgb(0 0 0 / 20%); transform: translate(-50%, -50%); }
    .rp-dialog::backdrop { background: rgb(10 16 40 / 60%); }
    .rp-dialog-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; margin-bottom: 18px; }
    .rp-dialog-heading h2 { margin-bottom: 3px; font-size: 19px; }
    .rp-close { border: 0; background: transparent; color: var(--muted); font-size: 25px; line-height: 1; padding: 2px 6px; }
    .rp-field-group { display: flex; flex-direction: column; gap: 7px; margin-bottom: 16px; }
    .rp-field-group label { font-size: 13px; font-weight: 700; }
    .rp-field-group .field { display: block; width: 100%; min-height: 44px; margin: 0; }
    .rp-field-group textarea.field { min-height: 110px; resize: vertical; }
    .rp-required { color: var(--red); }
    .rp-count { text-align: right; font-size: 11px; color: var(--muted); }
    .rp-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 9px; margin-top: 16px; }
    .rp-actions .btn { min-width: 105px; }
    .rp-privacy { padding: 10px 12px; color: var(--muted); background: var(--page); border-radius: 7px; font-size: 12px; }
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
                <span class="avatar avatar-large" aria-label="Member profile">AM</span>
                <div class="account-copy">
                    <strong>Alex Morgan</strong>
                </div>
                <div class="account-tools">
                    <span class="account-icon bell-icon" title="Notifications" aria-label="Notifications"></span>
                    <details class="account-settings">
                        <summary class="account-icon" title="Settings" aria-label="Open settings">⚙</summary>
                        <nav aria-label="Account settings">
                            <a href="/ui-preview/edit-profile">Profile settings</a>
                            <a href="#logout">Sign out</a>
                        </nav>
                    </details>
                </div>
            </section>
        </aside>

        <main class="main-column" id="home">
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

            @if($hidePosts ?? false)
                @include('post_forms.form', ['isScheduling' => $isScheduling ?? false])
            @else
                <section class="feed-list" aria-label="Latest posts">
                    <article class="panel post-card">
                        <div class="post-header">
                            <span class="avatar">JC</span>
                            <div class="post-author">
                                <strong>James Carter</strong>
                                <small>Today at 10:30 AM</small>
                            </div>
                            <span class="post-category">Technology</span>
                            <div class="post-report-menu-wrap" id="post-report-menu-wrap">
                                <button
                                    type="button"
                                    class="post-report-menu-button"
                                    id="post-report-menu-toggle"
                                    aria-label="Post options"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                    aria-controls="post-report-menu"
                                >&hellip;</button>
                                <div id="post-report-menu" class="post-report-menu" hidden>
                                    <button type="button" id="open-post-report">Report Post</button>
                                </div>
                            </div>
                        </div>
                        <p class="post-text">Finished the first pass at the Posts migration today. The category relationship was confusing at first, but it finally started to make sense.</p>
                        <div class="post-visual">Your community updates will appear here.</div>
                        <div class="post-counts"><span>24 reactions</span><span>2 comments · 18 views</span></div>
                        <div class="post-actions">
                            <button class="post-action" type="button">♡ React</button>
                            <button class="post-action" type="button">Comment</button>
                        </div>
                        <div class="comment-area">
                            <div class="comment-row"><strong>Sarah Wilson</strong> Nice! I had the same issue with the foreign key.</div>
                            <form class="comment-form" action="#" method="POST">
                                @csrf
                                <input type="text" name="content" placeholder="Write a comment..." aria-label="Write a comment">
                                <button class="btn small" type="submit">Send</button>
                            </form>
                        </div>
                    </article>
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

        <form id="post-report-form">
            <div class="rp-field-group">
                <label for="post-report-reason">Report Reason <span class="rp-required">*</span></label>
                <select class="field" id="post-report-reason" name="reason" required>
                    <option value="">Select a reason</option>
                    <option value="Problem involving someone under 18">Problem involving someone under 18</option>
                    <option value="Bullying, harassment or abuse">Bullying, harassment or abuse</option>
                    <option value="Suicide or self-harm">Suicide or self-harm</option>
                    <option value="Violent, hateful or disturbing content">Violent, hateful or disturbing content</option>
                    <option value="Selling or promoting restricted items">Selling or promoting restricted items</option>
                    <option value="Adult content">Adult content</option>
                    <option value="Scam, fraud or false information">Scam, fraud or false information</option>
                    <option value="Intellectual property">Intellectual property</option>
                    <option value="I don&#x27;t want to see this">I don't want to see this</option>
                </select>
            </div>

            <div class="rp-field-group">
                <label for="post-report-details">Additional Details (Optional)</label>
                <textarea class="field" id="post-report-details" name="details" maxlength="500" placeholder="Describe your concern (optional)..."></textarea>
                <span class="rp-count" id="post-report-count">0 / 500</span>
            </div>

            <p class="rp-privacy">Reports will be reviewed by the moderation team once the backend is connected. This preview does not send any report.</p>

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

            const toggle = document.getElementById('post-report-menu-toggle');
            const menu = document.getElementById('post-report-menu');
            const menuWrap = document.getElementById('post-report-menu-wrap');
            const openReport = document.getElementById('open-post-report');
            const dialog = document.getElementById('post-report-dialog');
            const closeReport = document.getElementById('close-post-report');
            const cancelReport = document.getElementById('cancel-post-report');
            const form = document.getElementById('post-report-form');
            const reason = document.getElementById('post-report-reason');
            const details = document.getElementById('post-report-details');
            const count = document.getElementById('post-report-count');
            const result = document.getElementById('post-report-result');

            function setMenuOpen(open) {
                menu.hidden = !open;
                toggle.setAttribute('aria-expanded', String(open));
            }

            function hideReport() {
                if (dialog.open) dialog.close();
            }

            toggle.addEventListener('click', (event) => {
                event.stopPropagation();
                setMenuOpen(menu.hidden);
            });

            document.addEventListener('click', (event) => {
                if (!menuWrap.contains(event.target)) setMenuOpen(false);
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') setMenuOpen(false);
            });

            openReport.addEventListener('click', () => {
                setMenuOpen(false);
                form.reset();
                count.textContent = '0 / 500';
                dialog.showModal();
                reason.focus();
            });

            closeReport.addEventListener('click', hideReport);
            cancelReport.addEventListener('click', hideReport);

            dialog.addEventListener('click', (event) => {
                if (event.target === dialog) hideReport();
            });

            details.addEventListener('input', () => {
                count.textContent = details.value.length + ' / 500';
            });

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                if (!form.reportValidity()) return;
                result.textContent = 'Report form validated successfully (preview only). No report was submitted to the server.';
                result.hidden = false;
                hideReport();
            });
        })();
    </script>
@endsection
