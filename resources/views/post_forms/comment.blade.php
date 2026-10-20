@php
    $replies = $repliesByParent[$comment->comment_id] ?? collect();
    $isAtMaxDepth = $depth >= \App\Models\Comment::MAX_REPLY_DEPTH;
@endphp
<div class="comment-thread">
    <div class="comment {{ $depth > 0 ? 'is-reply' : '' }}" id="comment-{{ $comment->comment_id }}">
        @include('post_forms.avatar-link', ['user' => $comment->user, 'sizeClass' => 'avatar-small'])

        <div class="comment-body">
            <div class="comment-bubble" data-comment-view="{{ $comment->comment_id }}">
                @if ($comment->user)
                    <a class="comment-author" href="{{ route('users.show', $comment->user) }}">{{ $comment->user->username }}</a>
                @else
                    <strong class="comment-author">Deleted member</strong>
                @endif
                <p class="comment-text">{{ $comment->content }}</p>
            </div>

            @can('update', $comment)
                <form class="comment-edit-form" method="POST" action="{{ route('comments.update', $comment) }}" data-comment-action data-comment-edit-form="{{ $comment->comment_id }}" hidden>
                    @csrf
                    @method('PUT')
                    <textarea class="field" name="content" maxlength="1000" rows="2" aria-label="Edit your comment" required>{{ $comment->content }}</textarea>
                    <div class="comment-edit-actions">
                        <button class="btn secondary small" type="button" data-comment-edit-cancel="{{ $comment->comment_id }}">Cancel</button>
                        <button class="btn small" type="submit">Save</button>
                    </div>
                </form>
            @endcan

            <div class="comment-meta" data-comment-meta="{{ $comment->comment_id }}">
                <time datetime="{{ $comment->created_at?->toIso8601String() }}" title="{{ $comment->created_at?->format('M j, Y \a\t g:i A') }}">{{ $comment->created_at?->diffForHumans(short: true) }}</time>
                @if ($comment->wasEdited())
                    <span title="Edited {{ $comment->updated_at->format('M j, Y \a\t g:i A') }}">Edited</span>
                @endif
                <button type="button" class="comment-link" data-reply-to="{{ $comment->comment_id }}" data-reply-name="{{ $comment->user?->username }}" @if ($isAtMaxDepth) data-reply-mention @endif>Reply</button>
                @can('update', $comment)
                    <button type="button" class="comment-link" data-comment-edit="{{ $comment->comment_id }}">Edit</button>
                @endcan
                @can('delete', $comment)
                    <form method="POST" action="{{ route('comments.destroy', $comment) }}" data-comment-action data-confirm="{{ $replies->isNotEmpty() ? 'This comment and its replies will be deleted.' : 'This comment will be deleted.' }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="comment-link comment-delete">Delete</button>
                    </form>
                @endcan
            </div>

            <form class="comment-form reply-form" method="POST" action="{{ route('posts.comments.store', $comment->post_id) }}" data-comment-action data-reply-form="{{ $comment->comment_id }}" hidden>
                @csrf
                <input type="hidden" name="parent_id" value="{{ $comment->comment_id }}">
                <input class="field" name="content" maxlength="1000" placeholder="Reply to {{ $comment->user?->username ?? 'this comment' }}..." aria-label="Write a reply" required autocomplete="off">
                <button class="btn small" type="submit">Reply</button>
            </form>
        </div>
    </div>

    @if ($replies->isNotEmpty())
        @if ($isAtMaxDepth)
            @foreach ($replies as $reply)
                @include('post_forms.comment', ['comment' => $reply, 'depth' => $depth])
            @endforeach
        @else
            <div class="comment-replies">
                @foreach ($replies as $reply)
                    @include('post_forms.comment', ['comment' => $reply, 'depth' => $depth + 1])
                @endforeach
            </div>
        @endif
    @endif
</div>
