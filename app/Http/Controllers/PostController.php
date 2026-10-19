<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostMedia;
use App\Services\PostPublisher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::query()
            ->visibleInFeed()
            ->with(['user:user_id,username', 'category:category_id,category_type', 'media'])
            ->withCount(['reactions', 'comments', 'views'])
            ->latest('date_time')
            ->limit(50)
            ->get();

        return view('welcome', [
            'posts' => $posts,
            'categories' => Category::query()->orderBy('category_type')->get(),
        ]);
    }

    public function create(): View
    {
        return view('welcome', [
            'hidePosts' => true,
            'isScheduling' => false,
            'categories' => Category::query()->orderBy('category_type')->get(),
        ]);
    }

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

    public function update(Request $request, Post $post): Response
    {
        abort_unless($post->user_id === $request->user()->user_id, 403);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1200'],
            'category_id' => ['nullable', 'exists:categories,category_id'],
            'post_type' => ['nullable', 'in:Questions,Discussion,Knowledge Sharing'],
            'hashtag' => ['nullable', 'string', 'max:150'],
            'media' => ['nullable', 'array', 'max:10'],
            'media.*' => ['file', 'max:25600', 'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/webm'],
        ]);

        $post->update([
            'descriptions' => $validated['content'],
            'category_id' => $validated['category_id'] ?? $post->category_id,
            'post_type' => $validated['post_type'] ?? $post->post_type,
            'hashtag' => $validated['hashtag'] ?? $post->hashtag,
        ]);

        if ($request->hasFile('media')) {
            foreach ($post->media as $oldMedia) {
                if ($oldMedia->media_path) {
                    Storage::disk('public')->delete($oldMedia->media_path);
                }
                $oldMedia->delete();
            }

            foreach ($request->file('media') as $file) {
                $mime = $file->getMimeType() ?? $file->getClientMimeType();
                $path = $file->store('post-media', 'public');

                $post->media()->create([
                    'media_type' => str_starts_with($mime, 'image/') ? 'image' : 'video',
                    'mime_type' => $mime,
                    'original_filename' => $file->getClientOriginalName(),
                    'media_path' => $path,
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, Post $post): Response
    {
        abort_unless($post->user_id === $request->user()->user_id, 403);

        foreach ($post->media as $media) {
            if ($media->media_path) {
                Storage::disk('public')->delete($media->media_path);
            }
        }

        $post->delete();

        return response()->json(['success' => true]);
    }
}
