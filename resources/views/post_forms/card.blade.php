@php
    $reactionTypes = \App\Models\Reaction::TYPES;
    $viewerReaction = $post->viewer_reaction;
@endphp
<article class="panel post-card" data-post-card data-view-url="{{ route('posts.views.store', $post) }}" @if ($post->user_id === auth()->id()) data-own-post @endif>
    <div class="post-header">
        @include('post_forms.avatar-link', ['user' => $post->user])
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
        @php
            $mediaAlt = 'Photo shared by '.($post->user?->username ?? 'a member');
            $mediaItems = array_map(fn (array $item): array => $item + ['alt' => $mediaAlt], $post->mediaPreviewItems());
        @endphp
        <div class="post-media" data-media-carousel='@json($mediaItems)'>
            <div class="post-media-grid post-media-count-{{ min($post->media->count(), 4) }}">
                @foreach ($post->media as $media)
                    @if (str_starts_with($media->mime_type, 'video/'))
                        <video class="post-media-item" src="{{ route('media.show', $media) }}" controls preload="metadata"></video>
                    @else
                        <img class="post-media-item" src="{{ route('media.show', $media) }}" alt="{{ $mediaAlt }}" loading="lazy">
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    <div class="post-counts">
        <button class="reaction-summary post-count-link" type="button" data-people-url="{{ route('posts.reactions.index', $post) }}" data-people-title="Reactions">
            <span class="reaction-summary-emojis" data-reaction-emojis>@foreach ($post->top_reaction_types ?? [] as $type){{ $reactionTypes[$type]['emoji'] ?? '' }}@endforeach</span>
            <span data-reaction-count data-singular="reaction" data-plural="reactions">{{ $post->reactions_count }} {{ \Illuminate\Support\Str::plural('reaction', $post->reactions_count) }}</span>
        </button>
        <span>
            <button class="post-count-link" type="button" data-comments-toggle><span data-comment-count data-singular="comment" data-plural="comments">{{ $post->comments_count }} {{ \Illuminate\Support\Str::plural('comment', $post->comments_count) }}</span></button>
            ·
            @can('viewViewers', $post)
                <button class="post-count-link" type="button" data-people-url="{{ route('posts.views.index', $post) }}" data-people-title="Who viewed this post"><span data-view-count data-singular="view" data-plural="views">{{ $post->views_count }} {{ \Illuminate\Support\Str::plural('view', $post->views_count) }}</span></button>
            @else
                <span data-view-count data-singular="view" data-plural="views">{{ $post->views_count }} {{ \Illuminate\Support\Str::plural('view', $post->views_count) }}</span>
            @endcan
        </span>
    </div>
    <div class="post-actions">
        <form class="reaction-control" method="POST" action="{{ route('posts.reactions.store', $post) }}" data-reaction-control>
            @csrf
            <button class="post-action reaction-main {{ $viewerReaction ? 'is-reacted reaction-'.$viewerReaction : '' }}" type="submit" name="type" value="{{ $viewerReaction ?? 'like' }}" data-reaction-main aria-pressed="{{ $viewerReaction ? 'true' : 'false' }}">
                <span data-reaction-main-emoji>{{ $reactionTypes[$viewerReaction]['emoji'] ?? '👍' }}</span>
                <span data-reaction-main-label>{{ $reactionTypes[$viewerReaction]['label'] ?? 'Like' }}</span>
            </button>
            <div class="reaction-picker" role="group" aria-label="Pick a reaction" data-reaction-picker>
                @foreach ($reactionTypes as $type => $reaction)
                    <button class="reaction-option" type="submit" name="type" value="{{ $type }}" title="{{ $reaction['label'] }}" aria-label="{{ $reaction['label'] }}" aria-pressed="{{ $viewerReaction === $type ? 'true' : 'false' }}">{{ $reaction['emoji'] }}</button>
                @endforeach
            </div>
        </form>
        <button class="post-action" type="button" data-comments-toggle aria-expanded="false" aria-controls="comments-{{ $post->post_id }}">💬 Comment</button>
    </div>
    <section class="post-comments" id="comments-{{ $post->post_id }}" aria-label="Comments" data-comments data-comments-url="{{ route('posts.comments.index', $post) }}" hidden></section>
</article>
