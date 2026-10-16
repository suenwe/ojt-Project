@extends('layouts.app')

@section('content')
    <header class="topbar">
        <a class="brand" href="#home" aria-label="Vibely home">
            <span class="brand-mark">V</span>
            <span>Vibely</span>
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
                            <a href="#profile-settings">Profile settings</a>
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
                    <a class="btn" href="#create-post">Create post</a>
                    <a class="btn secondary" href="#schedule-post">Schedule post</a>
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

            <section class="feed-list" aria-label="Latest posts">
                <article class="panel post-card">
                    <div class="post-header">
                        <span class="avatar">JC</span>
                        <div class="post-author">
                            <strong>James Carter</strong>
                            <small>Today at 10:30 AM</small>
                        </div>
                        <span class="post-category">Technology</span>
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
@endsection
