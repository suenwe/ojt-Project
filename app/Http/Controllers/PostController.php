<?php

namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostMedia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
            public function index()
    {
        $categories = Category::all(); 
        $posts = Post::with(['user', 'category', 'media'])
            ->latest('date_time')
            ->get();

        $currentUser = request()->user();

        $serverPosts = $posts->map(function (Post $post) use ($currentUser): array {
            // Check if the current user is the owner of the post
            $isOwner = $currentUser && $currentUser->user_id === $post->user_id;
            
            // Get profile picture if it exists
            $avatarUrl = null;
            if ($post->user && method_exists($post->user, 'photoDataUri')) {
                $avatarUrl = $post->user->photoDataUri();
            }

            return [
                'id' => $post->post_id,
                'content' => $post->descriptions,
                'category' => $post->category?->category_type ?? 'Uncategorized',
                'post_type' => $post->post_type,
                'author' => $post->user?->username ?? 'Deleted user',
                'authorInitials' => method_exists($post->user, 'initials') ? $post->user->initials() : '?',
                'authorAvatar' => $avatarUrl,
                'hashtags' => $post->hashtag ?? '',
                'time' => $post->date_time->toIso8601String(),
                'is_owner' => $isOwner,
                'likes' => 0,
                'liked' => false,
                'comments' => [],
                'mediaItems' => $post->media->map(function (PostMedia $media): array {
                    return [
                        'url' => route('media.show', $media->media_id),
                        'type' => $media->mime_type,
                    ];
                })->values()->all(),
            ];
        })->values();

        return view('welcome', compact('posts', 'serverPosts', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('create-post', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1200'],
            'category_id' => ['required', 'exists:categories,category_id'],
            'post_type' => ['required', 'in:Questions,Discussion,Knowledge Sharing'],
            'hashtag' => ['nullable', 'string', 'max:150'],
            
            // Fixed Validation: Use mimetypes instead of mimes for arrays
            'media' => ['nullable', 'array', 'max:10'],
            'media.*' => [
                'file',
                'max:25600', // 25MB limit per file
                'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/webm',
            ],
        ]);

        $user = $request->user();
        abort_unless($user instanceof User, 401);
        $storedPaths = [];

        try {
            DB::transaction(function () use ($validated, $request, $user, &$storedPaths): void {
                $post = Post::create([
                    'descriptions' => $validated['content'],
                    'category_id' => $validated['category_id'],
                    'post_type' => $validated['post_type'],
                    'hashtag' => $validated['hashtag'] ?? null,
                    'state' => 'published',
                    'date_time' => now(),
                    'user_id' => $user->user_id,
                ]);

                foreach ($request->file('media', []) as $file) {
                    $mime = $file->getMimeType() ?? $file->getClientMimeType();
                    $path = $file->store('post-media', 'public');

                    if ($path === false) {
                        throw new \RuntimeException('The uploaded media could not be stored.');
                    }

                    $storedPaths[] = $path;

                    $post->media()->create([
                        'media_type' => str_starts_with($mime, 'image/') ? 'image' : 'video',
                        'mime_type' => $mime,
                        'original_filename' => $file->getClientOriginalName(),
                        'media_data' => null,
                        'media_path' => $path,
                        'file_size' => $file->getSize(),
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($storedPaths);
            Log::error('Post store failed: '.$e->getMessage());

            return back()->withInput()->withErrors([
                'content' => 'Could not save your post: '.$e->getMessage(),
            ]);
        }

        return redirect()
            ->route('posts.create')
            ->with('success', 'Your post was published successfully!');
    }

    /**
     * Serve the image/video from PostgreSQL bytea data.
     */
    public function media(int $mediaId)
    {
        $media = PostMedia::findOrFail($mediaId);

        if ($media->media_path && Storage::disk('public')->exists($media->media_path)) {
            return response()->file(Storage::disk('public')->path($media->media_path), [
                'Content-Type' => $media->mime_type,
            ]);
        }

        if ($media->media_data) {
            $data = is_resource($media->media_data)
                ? stream_get_contents($media->media_data)
                : $media->media_data;

            return response($data)
                ->header('Content-Type', $media->mime_type)
                ->header('Cache-Control', 'public, max-age=86400');
        }
        abort(404);
    }

            public function update(Request $request, Post $post)
    {
        if ($post->user_id !== $request->user()->user_id) {
            abort(403);
        }

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

        // Handle new media uploads (overwrite old ones)
        if ($request->hasFile('media')) {
            // Delete old media first
            foreach ($post->media as $oldMedia) {
                if ($oldMedia->media_path) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($oldMedia->media_path);
                }
                $oldMedia->delete();
            }

            // Save new media
            foreach ($request->file('media') as $file) {
                $mime = $file->getMimeType() ?? $file->getClientMimeType();
                $path = $file->store('post-media', 'public');

                $post->media()->create([
                    'post_id' => $post->post_id,
                    'media_type' => str_starts_with($mime, 'image/') ? 'image' : 'video',
                    'mime_type' => $mime,
                    'original_filename' => $file->getClientOriginalName(),
                    'media_data' => file_get_contents($file->getRealPath()),
                    'media_path' => $path,
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, Post $post)
    {
        // Ensure the user owns the post
        if ($post->user_id !== $request->user()->user_id) {
            abort(403);
        }

        // Delete associated media from storage if needed
        foreach ($post->media as $media) {
            if ($media->media_path) {
                Storage::disk('public')->delete($media->media_path);
            }
        }

        $post->delete();

        return response()->json(['success' => true]);
    }
}
