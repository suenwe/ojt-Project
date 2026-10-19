@if ($users->isEmpty())
    <div class="empty-state"><span class="empty-symbol">♙</span><strong>{{ $compact ? 'No member accounts yet' : 'No users match these filters' }}</strong><p>{{ $compact ? 'Newly registered members will appear here.' : 'Try a different email, username, role, or status filter.' }}</p></div>
@else
    @unless ($compact)
        <form method="GET" action="{{ route('admin.dashboard') }}" class="user-filter-bar">
            <input type="hidden" name="section" value="users">
            <label class="search-box"><span>⌕</span><input type="search" name="search" value="{{ $userFilters['search'] }}" placeholder="Search username or email..." aria-label="Search by username or email"></label>
            <select name="role" aria-label="Filter by role">
                <option value="">All roles</option>
                <option value="user" @selected($userFilters['role'] === 'user')>Member</option>
                <option value="admin" @selected($userFilters['role'] === 'admin')>Admin</option>
            </select>
            <select name="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                <option value="active" @selected($userFilters['status'] === 'active')>Active</option>
                <option value="banned" @selected($userFilters['status'] === 'banned')>Banned</option>
            </select>
            <button class="button button-outline button-small" type="submit">Filter users</button>
            @if ($userFilters['search'] !== '' || $userFilters['role'] !== '' || $userFilters['status'] !== '')
                <a class="text-link" href="{{ route('admin.dashboard', ['section' => 'users']) }}">Clear filters</a>
            @endif
        </form>
        <div class="table-tools"><span class="table-help">Profile and latest recorded browser/IP. Admin accounts are protected from bans.</span></div>
    @endunless
    <div class="table-scroll">
        <table class="data-table member-table">
            <thead><tr><th>USER</th><th>EMAIL</th>@unless ($compact)<th>ROLE</th>@endunless<th>BIRTHDAY</th><th>BROWSER</th><th>IP ADDRESS</th><th>STATUS</th>@unless ($compact)<th>ACCOUNT ACTION</th>@endunless</tr></thead>
            <tbody data-searchable-rows>
                @foreach ($users->when($compact, fn ($paginator) => $paginator->take(5)) as $user)
                    <tr>
                        <td><div class="member-cell">
                            @if ($user->photo_data_uri)
                                <img class="member-avatar" src="{{ $user->photo_data_uri }}" alt="{{ $user->username }} profile photo">
                            @else
                                <span class="member-avatar avatar-fallback">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->username, 0, 1)) }}</span>
                            @endif
                            <span><strong>{{ $user->username }}</strong><small>Joined {{ $user->created_date?->format('M j, Y') ?? 'date unavailable' }}</small></span>
                        </div></td>
                        <td class="muted-cell">{{ $user->email }}</td>
                        @unless ($compact)<td><span class="role-pill {{ $user->role === 'admin' ? 'role-admin' : 'role-member' }}">{{ $user->role === 'admin' ? 'Admin' : 'Member' }}</span></td>@endunless
                        <td class="muted-cell">{{ $user->birthday ? \Illuminate\Support\Carbon::parse($user->birthday)->format('M j, Y') : '—' }}</td>
                        <td><span class="browser-cell">{{ $user->browser ?: 'Not recorded' }}</span></td>
                        <td><code class="ip-cell">{{ $user->IP_address ?: 'Not recorded' }}</code></td>
                        <td><span class="status-pill {{ $user->isBanned() ? 'status-banned' : 'status-active' }}">{{ $user->isBanned() ? 'Banned' : 'Active' }}</span></td>
                        @unless ($compact)
                            <td>
                                <div class="button-group user-action-group">
                                    <a class="button button-outline button-small" href="{{ route('admin.dashboard', ['section' => 'access-logs', 'user_id' => $user->user_id]) }}">Check</a>
                                @if ($user->role !== 'admin' && $user->user_id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.ban', $user) }}" class="user-ban-form" data-confirm="{{ $user->isBanned() ? 'This member will be able to access their account again.' : 'This member will lose access to the community until an admin restores it.' }}" data-confirm-title="{{ $user->isBanned() ? 'Restore member access?' : 'Ban this member?' }}" data-confirm-action="{{ $user->isBanned() ? 'Restore access' : 'Ban member' }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="action" value="{{ $user->isBanned() ? 'unban' : 'ban' }}">
                                        @if (! $user->isBanned())<input class="ban-reason-input" name="ban_reason" maxlength="1000" placeholder="Reason (optional)" aria-label="Ban reason">@endif
                                        <button class="button {{ $user->isBanned() ? 'button-outline' : 'button-danger' }} button-small" type="submit">{{ $user->isBanned() ? 'Unban' : 'Ban' }}</button>
                                    </form>
                                @else
                                    <button class="button button-danger button-small" type="button" disabled title="Admin accounts and your own account cannot be banned">Ban</button>
                                @endif
                                </div>
                            </td>
                        @endunless
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @unless ($compact)
        <div class="pagination-wrap">{{ $users->links() }}</div>
    @endunless
@endif
