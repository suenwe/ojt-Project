@extends('layouts.app')

@section('content')
    <header class="topbar admin-topbar">
        <a class="brand" href="#admin-dashboard" aria-label="Vibely admin dashboard">
            <span class="brand-mark">V</span>
            <span>Vibely Admin</span>
        </a>
        <div class="top-actions">
            <span class="admin-label">Administrator</span>
            <a class="btn secondary small" href="#logout">Sign out</a>
        </div>
    </header>

    <main class="admin-shell" id="admin-dashboard">
        <div class="page-heading">
            <div>
                <p class="auth-eyebrow">Administration</p>
                <h1>Dashboard</h1>
                <p class="subtext">Manage the community from one place.</p>
            </div>
        </div>

        <section class="admin-stat-grid" aria-label="Dashboard summary">
            <article class="panel admin-stat">
                <span class="admin-stat-label">Users</span>
                <strong>—</strong>
                <small>Connect to user data</small>
            </article>
            <article class="panel admin-stat">
                <span class="admin-stat-label">Posts</span>
                <strong>—</strong>
                <small>Connect to post data</small>
            </article>
            <article class="panel admin-stat">
                <span class="admin-stat-label">Reports</span>
                <strong>—</strong>
                <small>Connect to report data</small>
            </article>
        </section>

        <section class="panel admin-placeholder">
            <h2>Administration tools</h2>
            <p class="subtext">User management, moderation, reports, and scheduled posts can be added here.</p>
            <div class="admin-tool-grid">
                <a class="admin-tool" href="#users"><strong>Manage users</strong><span>Review accounts and roles.</span></a>
                <a class="admin-tool" href="#posts"><strong>Manage posts</strong><span>Review community content.</span></a>
                <a class="admin-tool" href="#reports"><strong>Review reports</strong><span>Handle reported content.</span></a>
            </div>
        </section>
    </main>
@endsection
