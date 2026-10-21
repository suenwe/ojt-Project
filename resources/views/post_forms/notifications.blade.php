<ul class="notification-list">
    @forelse ($notifications as $notification)
        @php
            $targetUrl = $notification->targetUrl();
            $rowTag = $targetUrl ? 'a' : 'div';
        @endphp
        <li>
            <{{ $rowTag }} class="notification-item {{ $notification->read_at ? '' : 'is-unread' }}" @if ($targetUrl) href="{{ $targetUrl }}" @endif>
                @if ($notification->isScheduleReminder())
                    <span class="avatar avatar-small notification-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" focusable="false">
                            <circle cx="12" cy="12" r="8.5" fill="none" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M12 7.5V12l3 2" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                @else
                    @include('post_forms.avatar-link', ['user' => $notification->actor, 'sizeClass' => 'avatar-small', 'linked' => false])
                @endif

                <span class="notification-copy">
                    @if ($notification->isScheduleReminder())
                        <strong>{{ $notification->summary() }}</strong>
                    @else
                        <span><strong>{{ $notification->actor?->username ?? 'Someone' }}</strong> {{ $notification->summary() }}</span>
                    @endif
                    @if ($notification->excerpt() !== '')
                        <span class="notification-excerpt">“{{ $notification->excerpt() }}”</span>
                    @endif
                    <time class="notification-time" datetime="{{ $notification->updated_at?->toIso8601String() }}">{{ $notification->updated_at?->diffForHumans(short: true) }}</time>

                    @if ($notification->canStillChangeSchedule())
                        <span class="notification-actions">
                            <a class="btn small" href="{{ route('posts.edit', $notification->post_id) }}" data-reminder-edit="{{ $notification->post_id }}">Edit</a>
                            <form method="POST" action="{{ route('schedule-post.destroy', $notification->post_id) }}" data-confirm="Cancel this scheduled post? It won't be published.">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="from_calendar" value="1">
                                <button class="btn secondary small" type="submit">Delete</button>
                            </form>
                        </span>
                    @endif
                </span>

                @unless ($notification->read_at)
                    <span class="notification-dot" aria-label="Unread"></span>
                @endunless
            </{{ $rowTag }}>
        </li>
    @empty
        <li class="notification-empty">No notifications yet. Reactions, comments and replies to your posts will show up here.</li>
    @endforelse
</ul>
