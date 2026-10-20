<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin console · {{ config('app.name', 'Knowvia') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-body">
    <div class="admin-shell">
        <aside class="sidebar">
            <a class="brand" href="{{ route('admin.dashboard') }}">
                <span class="brand-mark">K</span>
                <span><strong>Knowvia</strong><small>Admin</small></span>
            </a>

            <div class="nav-label">WORKSPACE</div>
            <nav class="side-nav" aria-label="Admin sections">
                <a class="{{ $section === 'overview' ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <span class="nav-icon">▦</span> Overview
                </a>
                <a class="{{ $section === 'reports' ? 'active' : '' }}" href="{{ route('admin.dashboard', ['section' => 'reports']) }}">
                    <span class="nav-icon">⚑</span> Report queue
                    @if ($stats['pending_reports'] > 0)
                        <span class="nav-count">{{ $stats['pending_reports'] }}</span>
                    @endif
                </a>
                <a class="{{ $section === 'users' ? 'active' : '' }}" href="{{ route('admin.dashboard', ['section' => 'users']) }}">
                    <span class="nav-icon">♙</span> User management
                </a>
                <a class="{{ $section === 'access-logs' ? 'active' : '' }}" href="{{ route('admin.dashboard', ['section' => 'access-logs']) }}">
                    <span class="nav-icon">◷</span> User access log
                </a>
            </nav>

            <div class="nav-label nav-label-spaced">CONTENT &amp; SYSTEM</div>
            <nav class="side-nav" aria-label="Content management">
                <a class="{{ $section === 'categories' ? 'active' : '' }}" href="{{ route('admin.dashboard', ['section' => 'categories']) }}">
                    <span class="nav-icon">▤</span> Categories
                </a>
                <a class="{{ $section === 'restricted' ? 'active' : '' }}" href="{{ route('admin.dashboard', ['section' => 'restricted']) }}">
                    <span class="nav-icon">⊘</span> Keywords
                </a>
            </nav>

            <div class="sidebar-bottom">
                <div class="admin-identity">
                    <span class="identity-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($admin->username, 0, 1)) }}</span>
                    <span class="identity-copy"><strong>{{ $admin->username }}</strong><small>Administrator</small></span>
                    <span class="identity-dot" title="Signed in"></span>
                </div>
            </div>
        </aside>

        <main class="main-panel">
            <header class="topbar">
                <div class="breadcrumbs">
                    @if ($section !== 'overview')
                        @include('partials.page-back', ['fallback' => route('admin.dashboard')])
                    @endif
                    <span>Workspace</span><span class="crumb-divider">/</span><strong>{{ [
                    'overview' => 'Overview',
                    'reports' => 'Report queue',
                    'users' => 'User management',
                    'access-logs' => 'User access log',
                    'categories' => 'Categories',
                    'restricted' => 'Keywords',
                ][$section] }}</strong></div>
                <div class="topbar-right">
                    
                    <span class="topbar-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($admin->username, 0, 1)) }}</span>
                    <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="button button-quiet button-small" type="submit">Sign out</button></form>
                </div>
            </header>

            <div class="page-content">
                @if (session('status'))
                    <div class="alert alert-success" role="status"><span>✓</span>{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-error" role="alert">
                        <span>!</span>
                        <div><strong>Please check the form.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    </div>
                @endif

                @if ($section === 'overview')
                    <div class="page-heading">
                        <div><h1>Overview</h1><p class="page-subtitle">Members, reports and categories at a glance.</p></div>
                        <a class="button button-primary" href="{{ route('admin.dashboard', ['section' => 'reports']) }}"><span>⚑</span> Review reports</a>
                    </div>

                    <section class="stats-grid" aria-label="Community summary">
                        <article class="stat-card">
                            <div class="stat-top"><span class="stat-icon icon-violet">♙</span></div>
                            <p class="stat-label">MEMBERS</p><strong class="stat-value">{{ number_format($stats['users']) }}</strong>
                            <p class="stat-note">Registered non-admin accounts</p>
                        </article>
                        <article class="stat-card">
                            <div class="stat-top"><span class="stat-icon icon-amber">⚑</span>@if ($stats['pending_reports'])<span class="stat-trend trend-warn">Needs review</span>@endif</div>
                            <p class="stat-label">PENDING REPORTS</p><strong class="stat-value">{{ number_format($stats['pending_reports']) }}</strong>
                            <p class="stat-note">Awaiting admin review</p>
                        </article>
                        <article class="stat-card">
                            <div class="stat-top"><span class="stat-icon icon-blue">▤</span></div>
                            <p class="stat-label">CATEGORIES</p><strong class="stat-value">{{ number_format($stats['categories']) }}</strong>
                            <p class="stat-note">Available post categories</p>
                        </article>
                        <article class="stat-card">
                            <div class="stat-top"><span class="stat-icon icon-rose">⊘</span></div>
                            <p class="stat-label">BANNED ACCOUNTS</p><strong class="stat-value">{{ number_format($stats['banned_users']) }}</strong>
                            <p class="stat-note">Accounts currently restricted</p>
                        </article>
                    </section>

                    <section class="panel report-panel">
                        <div class="panel-heading">
                            <div><div class="heading-with-icon"><span class="heading-icon icon-amber">⚑</span><h2>Reports needing review</h2></div><p>Review the reason and post before deciding on an action.</p></div>
                            <a class="text-link" href="{{ route('admin.dashboard', ['section' => 'reports']) }}">View report queue <span>→</span></a>
                        </div>
                        @php($pendingReports = $reports->getCollection()->where('status', 'pending')->take(4))
                        @if ($pendingReports->isEmpty())
                            <div class="empty-state compact-empty"><span class="empty-symbol">✓</span><strong>You’re all caught up</strong><p>There are no reports waiting for review.</p></div>
                        @else
                            <div class="report-list">
                                @foreach ($pendingReports as $report)
                                    <article class="report-row">
                                        <span class="report-marker">!</span>
                                        <div class="report-summary">
                                            <div class="report-title-line"><strong>{{ $report->report_type }}</strong><span class="status-pill status-pending">Pending</span></div>
                                            <p>{{ \Illuminate\Support\Str::limit($report->post?->descriptions ?: 'Post content is unavailable.', 130) }}</p>
                                            <span class="report-meta">Reported by {{ $report->reporter?->username ?? 'Deleted account' }} · Post by {{ $report->post?->user?->username ?? 'Unknown author' }}</span>
                                        </div>
                                        <a class="button button-outline button-small" href="{{ route('admin.dashboard', ['section' => 'reports']) }}">Review</a>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </section>

                    <section class="panel members-preview">
                        <div class="panel-heading">
                            <div><div class="heading-with-icon"><span class="heading-icon icon-violet">♙</span><h2>Recently joined members</h2></div><p>New people in your community.</p></div>
                            <a class="text-link" href="{{ route('admin.dashboard', ['section' => 'users']) }}">View all members <span>→</span></a>
                        </div>
                        @include('admin.partials.users-table', ['compact' => true])
                    </section>
                @elseif ($section === 'reports')
                    <div class="page-heading">
                        <div><h1>Report queue</h1><p class="page-subtitle">Review reported posts, assess the reason, and take the appropriate action.</p></div>
                        <span class="queue-summary"><strong>{{ $stats['pending_reports'] }}</strong> pending review</span>
                    </div>
                    <section class="panel">
                        <div class="panel-heading table-heading"><div><div class="heading-with-icon"><span class="heading-icon icon-amber">⚑</span><h2>Reported posts</h2></div><p>Confirm a report to suspend the post author for three days, or dismiss it if it is not valid.</p></div></div>
                        @if ($reports->isEmpty())
                            <div class="empty-state"><span class="empty-symbol">✓</span><strong>No reports yet</strong><p>When members report a post, it will appear here.</p></div>
                        @else
                            <div class="report-queue">
                                @foreach ($reports as $report)
                                    <article class="queue-card">
                                        <div class="queue-card-top">
                                            <div class="queue-reason"><span class="report-marker">!</span><div><span class="small-label">REPORTED FOR</span><h3>{{ $report->report_type }}</h3></div></div>
                                            <span class="status-pill status-{{ $report->status }}">{{ ucfirst($report->status) }}</span>
                                        </div>
                                        <div class="queue-body">
                                            <div class="post-preview">
                                                <span class="small-label">POST CONTENT</span>
                                                <p>{{ $report->post?->descriptions ?: 'Post content is unavailable.' }}</p>
                                                @if ($report->post?->category)
                                                    <span class="category-tag">{{ $report->post->category->category_type }}</span>
                                                @endif
                                            </div>
                                            <div class="report-details">
                                                <div><span class="small-label">POST AUTHOR</span><strong>{{ $report->post?->user?->username ?? 'Unknown author' }}</strong><span>{{ $report->post?->user?->email ?? 'Account unavailable' }}</span></div>
                                                <div><span class="small-label">REPORTED BY</span><strong>{{ $report->reporter?->username ?? 'Deleted account' }}</strong><span>{{ $report->reporter?->email ?? 'Account unavailable' }}</span></div>
                                                <div><span class="small-label">MEMBER REASON</span><p>{{ $report->reason ?: 'No additional details supplied.' }}</p></div>
                                            </div>
                                        </div>
                                        @if ($report->status === 'pending')
                                            <div class="queue-actions">
                                                <span class="action-hint">Confirming this report suspends the post author for three days.</span>
                                                <div class="button-group">
                                                    <form method="POST" action="{{ route('admin.reports.review', $report) }}" data-confirm="This report will be marked invalid. The post author will not be banned." data-confirm-title="Dismiss this report?" data-confirm-action="Dismiss report">
                                                        @csrf @method('PATCH')<input type="hidden" name="decision" value="invalid">
                                                        <button class="button button-outline button-small" type="submit">Dismiss report</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.reports.review', $report) }}" data-confirm="The report will be marked valid and the post author will be suspended for three days." data-confirm-title="Confirm report and suspend author?" data-confirm-action="Confirm and suspend">
                                                        @csrf @method('PATCH')<input type="hidden" name="decision" value="valid">
                                                        <button class="button button-danger button-small" type="submit">Confirm &amp; suspend for 3 days</button>
                                                    </form>
                                                </div>
                                            </div>
                                        @else
                                            <div class="reviewed-note">Reviewed {{ $report->reviewed_at?->diffForHumans() ?? '' }} · {{ $report->reviewer?->username ?? 'Admin account' }}</div>
                                        @endif
                                    </article>
                                @endforeach
                            </div>
                            <div class="pagination-wrap">{{ $reports->links() }}</div>
                        @endif
                    </section>
                @elseif ($section === 'users')
                    <div class="page-heading">
                        <div><h1>User management</h1><p class="page-subtitle">Search accounts, review profile and activity details, and manage account access.</p></div>
                        <span class="queue-summary"><strong>{{ number_format($stats['users']) }}</strong> registered members</span>
                    </div>
                    <section class="panel">
                        <div class="panel-heading"><div><div class="heading-with-icon"><span class="heading-icon icon-violet">♙</span><h2>Manage user accounts</h2></div><p>Filter by username, email, role, or account status. Admin accounts are protected from bans.</p></div></div>
                        @include('admin.partials.users-table', ['compact' => false])
                    </section>
                    @can('create', \App\Models\User::class)
                        <section class="panel" id="create-admin">
                            <div class="panel-heading"><div><h2>Create admin account</h2><p>The new admin can sign in straight away and change this password later in Profile settings.</p></div></div>
                            @if ($errors->createAdmin->any())
                                <div class="alert alert-error" role="alert"><span>!</span><div><strong>Please check the form.</strong><ul>@foreach ($errors->createAdmin->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
                            @endif
                            <form method="POST" action="{{ route('admin.admins.store') }}" class="create-admin-form">
                                @csrf
                                <div class="field-group"><label for="new_admin_username">Username</label><input id="new_admin_username" name="username" minlength="3" maxlength="30" value="{{ old('username') }}" autocomplete="off" required></div>
                                <div class="field-group"><label for="new_admin_email">Email</label><input id="new_admin_email" name="email" type="email" maxlength="255" value="{{ old('email') }}" autocomplete="off" required></div>
                                <div class="field-group"><label for="new_admin_password">Password</label><input id="new_admin_password" name="password" type="password" minlength="8" autocomplete="new-password" required><span class="field-hint">At least 8 characters, with a number and a symbol.</span></div>
                                <div class="field-group"><label for="new_admin_password_confirmation">Confirm password</label><input id="new_admin_password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required></div>
                                <div class="create-admin-actions"><button class="button button-primary" type="submit">Create admin</button></div>
                            </form>
                        </section>
                    @endcan
                @elseif ($section === 'access-logs')
                    <div class="page-heading">
                        <div><h1>User access log</h1><p class="page-subtitle">Review authenticated web requests with recorded user, timestamp, browser, IP, and page details.</p></div>
                        <span class="queue-summary"><strong>{{ number_format($accessLogs->total()) }}</strong> recorded accesses</span>
                    </div>
                    <section class="panel">
                        <div class="panel-heading"><div><div class="heading-with-icon"><span class="heading-icon icon-blue">◷</span><h2>Recent authenticated activity</h2></div><p>New authenticated web requests are logged automatically; query-string values are not stored.</p></div></div>
                        @if ($accessLogs->isEmpty())
                            <div class="empty-state"><span class="empty-symbol">◷</span><strong>No access activity recorded yet</strong><p>Authenticated user requests will appear here.</p></div>
                        @else
                            <div class="table-scroll">
                                <table class="data-table access-log-table">
                                    <thead><tr><th>USER</th><th>ACCESS TIME</th><th>BROWSER</th><th>IP ADDRESS</th><th>REQUEST</th><th>ACCOUNT</th></tr></thead>
                                    <tbody>
                                        @foreach ($accessLogs as $log)
                                            <tr>
                                                <td><div class="log-user"><strong>{{ $log->user?->username ?? 'Deleted account' }}</strong><small>{{ $log->user?->email ?? 'Account no longer exists' }}</small></div></td>
                                                <td class="log-timestamp">{{ $log->accessed_at->format('M j, Y') }}<small>{{ $log->accessed_at->format('g:i:s A') }}</small></td>
                                                <td><span class="browser-cell">{{ $log->browser ?: 'Unknown' }}</span></td>
                                                <td><code class="ip-cell">{{ $log->ip_address ?: 'Unknown' }}</code></td>
                                                <td><span class="request-cell"><strong>{{ $log->method }}</strong> {{ $log->path ?: '/' }}</span><small class="route-cell">{{ $log->route_name ?: 'Unmatched route' }}</small></td>
                                                <td><span class="status-pill {{ $log->user?->isBanned() ? 'status-banned' : 'status-active' }}">{{ $log->user?->isBanned() ? 'Banned' : ($log->user ? 'Active' : 'Deleted') }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="pagination-wrap">{{ $accessLogs->links() }}</div>
                        @endif
                    </section>
                @elseif ($section === 'categories')
                    <div class="page-heading">
                        <div><h1>Post categories</h1><p class="page-subtitle">Create and maintain the categories members can choose for posts.</p></div>
                        <div class="category-total-card"><span class="heading-icon icon-blue">▤</span><span><strong>{{ $categories->count() }}</strong><small>ACTIVE CATEGORIES</small></span></div>
                    </div>
                    <section class="settings-layout">
                        <div class="panel settings-form-panel">
                            <div class="panel-heading"><div><div class="heading-with-icon"><span class="heading-icon icon-blue">＋</span><h2>Add category</h2></div><p>Keep names short and easy to understand.</p></div></div>
                            <form method="POST" action="{{ route('admin.categories.store') }}" class="stacked-form">
                                @csrf
                                <label for="category_type">Category name</label>
                                <input id="category_type" name="category_type" maxlength="100" placeholder="e.g. Science &amp; Nature" value="{{ old('category_type') }}" required>
                                @error('category_type')<span class="field-error">{{ $message }}</span>@enderror
                                <button class="button button-primary" type="submit">Add category <span>＋</span></button>
                            </form>
                        </div>
                        <div class="panel settings-list-panel">
                            <div class="panel-heading"><div><div class="heading-with-icon"><span class="heading-icon icon-blue">▤</span><h2>All categories</h2></div><p>Default and custom categories, stored in your database.</p></div></div>
                            @forelse ($categories as $category)
                                <div class="setting-row category-setting">
                                    <span class="category-index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="inline-edit-form">
                                        @csrf @method('PUT')
                                        <input aria-label="Category name" name="category_type" value="{{ $category->category_type }}" maxlength="100" required>
                                        <span class="usage-count"><strong>{{ $category->posts_count }}</strong> {{ \Illuminate\Support\Str::plural('post', $category->posts_count) }}</span>
                                        <button class="icon-button" type="submit" aria-label="Save category">✓</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" data-confirm="Posts in this category will remain published but become uncategorized." data-confirm-title="Delete category?" data-confirm-action="Delete category">
                                        @csrf @method('DELETE')
                                        <button class="category-delete-button" type="submit"><span aria-hidden="true">×</span> Delete</button>
                                    </form>
                                </div>
                            @empty
                                <div class="empty-state compact-empty"><strong>No categories yet</strong><p>Add the first post category using the form.</p></div>
                            @endforelse
                        </div>
                    </section>
                @elseif ($section === 'restricted')
                    <div class="page-heading">
                        <div><h1>Keywords</h1><p class="page-subtitle">Manage restricted words and phrases for post moderation.</p></div>
                        <span class="queue-summary"><strong>{{ $keywords->count() }}</strong> keywords</span>
                    </div>
                    <section class="settings-layout">
                        <div class="panel settings-form-panel">
                            <div class="panel-heading"><div><div class="heading-with-icon"><span class="heading-icon icon-rose">⊘</span><h2>Add keyword</h2></div><p>Enter the exact word or phrase to add to the moderation list.</p></div></div>
                            <form method="POST" action="{{ route('admin.keywords.store') }}" class="stacked-form">
                                @csrf
                                <label for="keyword_text">Word or phrase</label>
                                <input id="keyword_text" name="keyword_text" maxlength="255" placeholder="Enter a keyword" value="{{ old('keyword_text') }}" required>
                                @error('keyword_text')<span class="field-error">{{ $message }}</span>@enderror
                                <button class="button button-primary" type="submit">Add to list <span>＋</span></button>
                            </form>
                            <div class="notice-box"><strong>Moderation note</strong><p>Keywords are stored for moderation. Make sure your post-validation flow checks them before publishing content.</p></div>
                        </div>
                        <div class="panel settings-list-panel">
                            <div class="panel-heading"><div><div class="heading-with-icon"><span class="heading-icon icon-rose">⊘</span><h2>Keyword list</h2></div><p>Keywords currently configured for moderation.</p></div></div>
                            <div class="keyword-list">
                                @forelse ($keywords as $keyword)
                                    <div class="keyword-row"><span class="keyword-symbol">⊘</span><code>{{ $keyword->keyword_text }}</code><form method="POST" action="{{ route('admin.keywords.destroy', $keyword) }}" data-confirm="This keyword will no longer be included in your moderation list." data-confirm-title="Remove keyword?" data-confirm-action="Remove keyword">@csrf @method('DELETE')<button class="icon-button icon-button-danger" type="submit" aria-label="Remove keyword">×</button></form></div>
                                @empty
                                    <div class="empty-state compact-empty"><strong>No keywords yet</strong><p>Newly added words and phrases will appear here.</p></div>
                                @endforelse
                            </div>
                        </div>
                    </section>
                @endif

                <footer class="page-footer"><span>Knowvia admin</span></footer>
            </div>
        </main>
    </div>
    <dialog class="confirmation-dialog" aria-labelledby="confirmation-title" aria-describedby="confirmation-description">
        <div class="confirmation-content">
            <span class="confirmation-icon" aria-hidden="true">!</span>
            <h2 id="confirmation-title">Are you sure?</h2>
            <p id="confirmation-description"></p>
            <div class="confirmation-actions">
                <button class="button button-outline" type="button" data-confirm-cancel>Cancel</button>
                <button class="button button-danger" type="button" data-confirm-submit>Continue</button>
            </div>
        </div>
    </dialog>
</body>
</html>
