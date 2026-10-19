<article class="panel post-card">
    <div class="post-header">
        @if ($post->user)
            <a class="avatar" href="{{ route('users.show', $post->user) }}" aria-hidden="true" tabindex="-1">{{ $post->user->initials() }}</a>
        @else
            <span class="avatar" aria-hidden="true">?</span>
        @endif
        <div class="post-author">
            @if ($post->user)
                <a class="post-author-link" href="{{ route('users.show', $post->user) }}"><strong>{{ $post->user->username }}</strong></a>
            @else
                <strong>Deleted member</strong>
            @endif
            <small>{{ $post->date_time->format('M j, Y \a\t g:i A') }} · {{ $post->post_type ?? 'Discussion' }}</small>
        </div>
        <span class="post-category">{{ $post->category?->category_type ?? 'Uncategorized' }}</span>
        <div class="post-report-menu-wrap">
            <button
                type="button"
                class="post-report-menu-button"
                aria-label="Post options"
                aria-haspopup="true"
                aria-expanded="false"
            >&hellip;</button>
            <div class="post-report-menu" hidden>
                @can('update', $post)
                    <a href="{{ route('posts.edit', $post) }}">Edit post</a>
                    <form method="POST" action="{{ route('posts.destroy', $post) }}" data-confirm="Your post and its photos or videos will be permanently deleted." data-confirm-title="Delete this post?" data-confirm-action="Delete post">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="post-menu-danger">Delete post</button>
                    </form>
                @else
                    <button type="button" class="open-post-report" data-post-id="{{ $post->post_id }}">Report Post</button>
                @endcan
            </div>
        </div>
    </div>

    <p class="post-text">{{ $post->descriptions }}</p>

    @if ($post->hashtag)
        <p class="post-hashtags">{{ $post->hashtag }}</p>
    @endif

    @if ($post->media->isNotEmpty())
        <div class="post-media-grid post-media-count-{{ min($post->media->count(), 4) }}">
            @foreach ($post->media as $media)
                @if (str_starts_with($media->mime_type, 'video/'))
                    <video class="post-media-item" src="{{ route('media.show', $media) }}" controls preload="metadata"></video>
                @else
                    <img class="post-media-item" src="{{ route('media.show', $media) }}" alt="Photo shared by {{ $post->user?->username ?? 'a member' }}" loading="lazy">
                @endif
            @endforeach
        </div>
    @endif

    <div class="post-counts">
        <span>{{ $post->reactions_count }} {{ \Illuminate\Support\Str::plural('reaction', $post->reactions_count) }}</span>
        <span>{{ $post->comments_count }} {{ \Illuminate\Support\Str::plural('comment', $post->comments_count) }} · {{ $post->views_count }} {{ \Illuminate\Support\Str::plural('view', $post->views_count) }}</span>
    </div>
    <div class="post-actions">
        <button class="post-action" type="button">♡ React</button>
        <button class="post-action" type="button">Comment</button>
    </div>
</article>
