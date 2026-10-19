<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PostPublisher
{
    /**
     * The kinds of post a member can choose.
     *
     * @var list<string>
     */
    public const POST_TYPES = ['Discussion', 'Questions', 'Knowledge Sharing'];

    /**
     * Save a post and its uploaded files.
     *
     * Files go to the public disk (media_path) instead of the binary column, because PostgreSQL
     * rejects raw file bytes sent as text.
     *
     * @param  array{content: string, category_id: int|string, post_type: string, hashtag?: string|null}  $data
     * @param  array<int, UploadedFile>  $files
     *
     * @throws Throwable
     */
    public function create(User $author, array $data, array $files, string $state, CarbonInterface $dateTime): Post
    {
        $storedPaths = [];

        try {
            return DB::transaction(function () use ($author, $data, $files, $state, $dateTime, &$storedPaths): Post {
                $post = Post::query()->create([
                    'descriptions' => trim($data['content']),
                    'category_id' => (int) $data['category_id'],
                    'post_type' => $data['post_type'],
                    'hashtag' => trim((string) ($data['hashtag'] ?? '')) ?: null,
                    'state' => $state,
                    'date_time' => $dateTime,
                    'user_id' => $author->user_id,
                ]);

                foreach ($files as $file) {
                    $mimeType = $file->getMimeType() ?? $file->getClientMimeType();
                    $storedPaths[] = $path = $file->store('post-media', 'public');

                    $post->media()->create([
                        'media_type' => str_starts_with($mimeType, 'image/') ? 'photo' : 'video',
                        'mime_type' => $mimeType,
                        'original_filename' => $file->getClientOriginalName(),
                        'media_path' => $path,
                        'file_size' => $file->getSize(),
                    ]);
                }

                return $post;
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPaths);

            throw $exception;
        }
    }

    /**
     * Delete a post and its stored files.
     */
    public function delete(Post $post): void
    {
        $paths = $post->media()->whereNotNull('media_path')->pluck('media_path')->all();

        DB::transaction(fn () => $post->delete());

        Storage::disk('public')->delete($paths);
    }
}
