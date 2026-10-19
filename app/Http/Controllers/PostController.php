<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use App\Models\PostMedia;
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

        $serverPosts = $posts->map(function (Post $post): array {
            return [
                'id' => $post->post_id,
                'content' => $post->descriptions,
                'category' => $post->category?->category_type ?? 'Uncategorized',
                'hashtags' => $post->hashtag ?? '',
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

        return view('create-post', compact('categories', 'posts', 'serverPosts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'content'     => ['required', 'string', 'max:1200'],
            'category_id' => ['required', 'exists:categories,category_id'],
            'post_type'   => ['required', 'in:Questions,Discussion,Knowledge Sharing'],
            'hashtag'     => ['nullable', 'string', 'max:150'],
            
            // Fixed Validation: Use mimetypes instead of mimes for arrays
            'media'       => ['nullable', 'array', 'max:10'],
            'media.*'     => [
                'file',
                'max:25600', // 25MB limit per file
                'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/webm'
            ],
        ]);

        $user = $request->user() ?? User::query()->orderBy('user_id')->first();

        if (!$user) {
            return back()->withInput()->withErrors([
                'content' => 'No user account exists yet. Run the database seeder before creating posts.',
            ]);
        }

        try {
            DB::beginTransaction();

            $post = Post::create([
                'descriptions' => $validated['content'],
                'category_id'  => $validated['category_id'],
                'post_type'    => $validated['post_type'],
                'hashtag'      => $validated['hashtag'] ?? null,
                'state'        => 'published',
                'date_time'    => now(),
                'user_id'      => $user->user_id,
            ]);

            // Ensure we get an array of files, even if only 1 file is uploaded
            $uploadedFiles = $request->file('media') ?? [];

            foreach ($uploadedFiles as $file) {
                // Fallback in case mimeType returns null
                $mime = $file->getMimeType() ?? $file->getClientMimeType();

                $post->media()->create([
                    'post_id'           => $post->post_id,
                    'media_type'        => str_starts_with($mime, 'image/') ? 'image' : 'video',
                    'mime_type'         => $mime,
                    'original_filename' => $file->getClientOriginalName(),
                    'media_data'        => file_get_contents($file->getRealPath()),
                    'media_path'        => null, 
                    'file_size'         => $file->getSize(),
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Post store failed: ' . $e->getMessage());
            
            return back()->withInput()->withErrors([
                'content' => 'Could not save your post: ' . $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('posts.index')
            ->with('success', 'Your post was published successfully!');
    }

    /**
     * Serve the image/video from PostgreSQL bytea data.
     */
    public function media(int $mediaId)
    {
        $media = PostMedia::findOrFail($mediaId);

        if ($media->media_data) {
            // PostgreSQL bytea might return as a stream resource
            $data = is_resource($media->media_data)
                ? stream_get_contents($media->media_data)
                : $media->media_data;

            return response($data)
                ->header('Content-Type', $media->mime_type)
                ->header('Cache-Control', 'public, max-age=86400');
        }

        // Fallback in case some files were saved to disk instead
        if ($media->media_path) {
            return redirect(Storage::disk('public')->url($media->media_path));
        }

        abort(404);
    }
}