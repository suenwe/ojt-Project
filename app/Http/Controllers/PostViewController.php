<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PostViewController extends Controller
{
    /**
     * List who viewed the post and when. Only the post's author may see this.
     */
    public function index(Post $post): View
    {
        Gate::authorize('viewViewers', $post);

        $views = $post->views()
            ->with('user:user_id,username,photo')
            ->latest('created_at')
            ->get();

        return view('post_forms.people-list', [
            'people' => $views->map(fn (PostView $view): array => [
                'user' => $view->user,
                'type' => null,
                'detail' => $view->created_at?->diffForHumans(short: true),
                'detailLabel' => 'Viewed '.$view->created_at?->format('M j, Y \a\t g:i A'),
            ]),
            'reactionTabs' => collect(),
            'emptyMessage' => 'Nobody has viewed this post yet.',
        ]);
    }

    /**
     * Count a view when a post has been on the member's screen. Each member counts once per post,
     * and authors don't add views to their own posts.
     */
    public function store(Request $request, Post $post): JsonResponse
    {
        abort_unless($post->isVisibleInFeed(), 404);

        $viewer = $request->user();

        if ($post->user_id !== $viewer->user_id) {
            PostView::query()->firstOrCreate([
                'post_id' => $post->post_id,
                'user_id' => $viewer->user_id,
            ]);
        }

        return response()->json([
            'viewCount' => $post->views()->count(),
        ]);
    }
}
