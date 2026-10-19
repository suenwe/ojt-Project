<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostMedia;
use App\Services\PostPublisher;
use Illuminate\Http\RedirectResponse;
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
    public function index(): View
    {
        $posts = Post::query()
            ->visibleInFeed()
            ->with(['user:user_id,username,photo', 'category:category_id,category_type', 'media'])
            ->withCount(['reactions', 'comments', 'views'])
            ->latest('date_time')
            ->limit(50)
            ->get();

        return view('welcome', [
            'posts' => $posts,
            'categories' => Category::query()->orderBy('category_type')->get(),
        ]);
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
    public function update(StorePostRequest $request, Post $post, PostPublisher $publisher): RedirectResponse
    {
        Gate::authorize('update', $post);

        $isRescheduling = $request->isReschedulingPost();

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
            return redirect()->route('posts.schedule')->with('status', 'Your scheduled post was updated.');
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
            return Storage::disk('public')->response($media->media_path, $media->original_filename, [
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
