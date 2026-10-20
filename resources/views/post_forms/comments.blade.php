<div class="comment-list">
    @forelse ($comments as $comment)
        @include('post_forms.comment', ['comment' => $comment, 'depth' => 0])
    @empty
        <p class="comment-empty">No comments yet. Be the first to comment.</p>
    @endforelse
</div>

<form class="comment-form" method="POST" action="{{ route('posts.comments.store', $post) }}" data-comment-action>
    @csrf
    <input class="field" name="content" maxlength="1000" placeholder="Write a comment..." aria-label="Write a comment" required autocomplete="off">
    <button class="btn small" type="submit">Send</button>
</form>
<p class="comment-error" data-comment-error role="alert" hidden></p>
