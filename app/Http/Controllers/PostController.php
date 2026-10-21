<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostMedia;
use App\Services\ActivityNotifier;
use App\Services\PostPublisher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PostController extends Controller
{
    /**
     * Show the main feed with published posts and scheduled posts whose time has come.
     * Categories are included for the post form that opens from "What's on your mind?".
     */
            public function index()
    {
        $categories = Category::all();

        // 1. Added withCount to fix the missing counts
        // 2. Added visibleInFeed to fix the scheduled posts showing early
        $posts = Post::with(['user', 'category', 'media', 'reactions'])
            ->withCount(['reactions', 'comments', 'views'])
            ->visibleInFeed()
            ->latest('date_time')
            ->get();

        // Get popular hashtags using the model method we just fixed
        $popularHashtags = Post::popularHashtags();

        // Format the posts for the frontend
        $serverPosts = $posts->map(function (Post $post) {
            
            // Extract hashtags safely (Unicode supported)
            $searchHashtags = '';
            if ($post->hashtag) {
                // Split by spaces or commas, then trim
                $tags = preg_split('/[\s,]+/u', $post->hashtag, -1, PREG_SPLIT_NO_EMPTY);
                $formattedTags = [];
                foreach ($tags as $tag) {
                    // Ensure it starts with # and is lowercase
                    $tag = '#' . ltrim($tag, '#');
                    $formattedTags[] = mb_strtolower($tag, 'UTF-8');
                }
                $searchHashtags = implode(' ', $formattedTags);
            }

            // Prepare data-search-text
            $searchText = mb_strtolower(
                $post->descriptions . ' ' . 
                $searchHashtags . ' ' . 
                ($post->user->username ?? '') . ' ' . 
                ($post->category->category_type ?? ''), 
                'UTF-8'
            );

            return [
                'id' => $post->post_id,
                'content' => $post->descriptions,
                'category' => $post->category?->category_type ?? 'Uncategorized',
                'post_type' => $post->post_type,
                'author' => $post->user?->username ?? 'Deleted user',
                'authorInitials' => $post->user?->initials() ?? '?',
                'hashtags' => $post->hashtag,
                'searchText' => $searchText,
                'searchHashtags' => $searchHashtags,
                'likes' => $post->reactions_count ?? 0,
                'comments_count' => $post->comments_count ?? 0,
                'views_count' => $post->views_count ?? 0,
                'mediaItems' => $post->media->map(function (PostMedia $media) {
                    return [
                        'url' => route('media.show', $media->media_id),
                        'type' => $media->mime_type,
                    ];
                })->values()->all(),
            ];
        })->values();

        return view('welcome', compact('posts', 'serverPosts', 'categories', 'popularHashtags'));
    }

    /**
     * Show the create post form inside the main page.
     */
    public function create(): View
    {
        return view('welcome', [
            'hidePosts' => true,
            'isScheduling' => false,
            'categories' => Category::query()->orderBy('category_type')->get(),
        ]);
    }

    /**
     * Publish a new post for the signed-in member.
     */
    public function store(StorePostRequest $request, PostPublisher $publisher): RedirectResponse
    {
        try {
            $publisher->create(
                $request->user(),
                $request->validated(),
                $request->file('media') ?? [],
                'published',
                now(),
            );
        } catch (Throwable $exception) {
            Log::error('Post store failed: '.$exception->getMessage());

            return back()->withInput()->withErrors([
                'content' => 'Your post could not be saved. Please try again.',
            ]);
        }

        return redirect()->route('home')->with('status', 'Your post was published.');
    }

    /**
     * Show the edit form for one of the member's own posts.
     */
    public function edit(Post $post): View
    {
        Gate::authorize('update', $post);

        return view('welcome', [
            'hidePosts' => true,
            'isScheduling' => false,
            'editingPost' => $post->loadCount('media'),
            'categories' => Category::query()->orderBy('category_type')->get(),
        ]);
    }

    /**
     * Save changes to one of the member's own posts.
     */
    public function update(StorePostRequest $request, Post $post, PostPublisher $publisher, ActivityNotifier $notifier): RedirectResponse
    {
        Gate::authorize('update', $post);

        $isRescheduling = $request->isReschedulingPost();
        $previousPublishAt = $post->date_time?->copy();

        try {
            $publisher->update(
                $post,
                $request->validated(),
                $request->file('media') ?? [],
                $isRescheduling ? Carbon::parse($request->validated('scheduled_at')) : null,
            );
        } catch (Throwable $exception) {
            Log::error('Post update failed: '.$exception->getMessage());

            return back()->withInput()->withErrors([
                'content' => 'Your changes could not be saved. Please try again.',
            ]);
        }

        if ($isRescheduling) {
            if (! $post->fresh()->date_time?->equalTo($previousPublishAt)) {
                $notifier->forgetScheduleReminder($post);
            }

            return ScheduledPostController::redirectAfterChange($request, 'Your scheduled post was updated.');
        }

        return redirect()->route('home')->with('status', 'Your post was updated.');
    }

    /**
     * Delete one of the member's own posts and its files.
     */
    public function destroy(Post $post, PostPublisher $publisher): RedirectResponse
    {
        Gate::authorize('delete', $post);

        $publisher->delete($post);

        return back()->with('status', 'Your post was deleted.');
    }

    /**
     * Serve a post's photo or video.
     */
    public function media(PostMedia $media): Response
    {
        if ($media->media_path && Storage::disk('public')->exists($media->media_path)) {
            return response()->stream(function () use ($media): void {
                $stream = Storage::disk('public')->readStream($media->media_path);

                if (is_resource($stream)) {
                    try {
                        fpassthru($stream);
                    } finally {
                        fclose($stream);
                    }
                }
            }, 200, [
                'Content-Type' => $media->mime_type,
                'Cache-Control' => 'private, max-age=86400',
            ]);
        }

        $data = $media->media_data;

        if (is_resource($data)) {
            $data = stream_get_contents($data);
        }

        abort_unless(is_string($data) && $data !== '', 404);

        return response($data)
            ->header('Content-Type', $media->mime_type)
            ->header('Cache-Control', 'private, max-age=86400');
    }
}
