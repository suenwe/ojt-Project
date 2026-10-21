<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Services\ActivityNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CommentController extends Controller
{
    /**
     * Show a post's comments with their replies (loaded when the member opens the comments).
     */
    public function index(Post $post): View
    {
        abort_unless($post->isVisibleInFeed(), 404);

        return $this->commentsView($post);
    }

    /**
     * Add a comment, or a reply to any comment or reply on the same post.
     */
    public function store(StoreCommentRequest $request, Post $post, ActivityNotifier $notifier): JsonResponse|RedirectResponse
    {
        abort_unless($post->isVisibleInFeed(), 404);

        $repliedTo = $request->parentComment();

        $comment = Comment::query()->create([
            'post_id' => $post->post_id,
            'user_id' => $request->user()->user_id,
            'parent_id' => $repliedTo?->comment_id,
            'content' => $request->validated('content'),
        ]);
        $notifier->commentAdded($comment, $request->user());

        return $this->respond($request, $post);
    }

    /**
     * Change the text of one of the member's own comments.
     */
    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse|RedirectResponse
    {
        Gate::authorize('update', $comment);

        $comment->update(['content' => $request->validated('content')]);

        return $this->respond($request, $comment->post);
    }

    /**
     * Delete a comment (and its replies).
     */
    public function destroy(Request $request, Comment $comment): JsonResponse|RedirectResponse
    {
        Gate::authorize('delete', $comment);

        $post = $comment->post;
        $comment->delete();

        return $this->respond($request, $post);
    }

    /**
     * Send the refreshed comment list to the page, or go back when JavaScript is off.
     */
    private function respond(Request $request, Post $post): JsonResponse|RedirectResponse
    {
        if (! $request->expectsJson()) {
            return back();
        }

        return response()->json([
            'html' => $this->commentsView($post)->render(),
            'commentCount' => $post->comments()->count(),
        ]);
    }

    private function commentsView(Post $post): View
    {
        $comments = $post->comments()
            ->with('user:user_id,username,photo')
            ->oldest()
            ->get();

        return view('post_forms.comments', [
            'post' => $post,
            'comments' => $comments->whereNull('parent_id'),
            'repliesByParent' => $comments->whereNotNull('parent_id')->groupBy('parent_id'),
        ]);
    }
}
