<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostMedia;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class ScheduledPostController extends Controller
{
    private const CATEGORIES = [
        'Technology',
        'Education',
        'Gaming',
        'Entertainment',
        'Health & Fitness',
        'Business & Career',
        'Travel',
        'Art & Design',
        'Fashion & Lifestyle',
        'Food & Cooking',
        'Sports',
        'General Discussion',
        'Events & Activities',
        'Books & Literature',
        'News & Updates',
    ];

    public function index(): View
    {
        $scheduledPosts = Post::query()
            ->with(['user', 'category', 'media'])
            ->where('state', 'scheduled')
            ->orderBy('date_time')
            ->get();

        return view('schedule-post', compact('scheduledPosts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1200'],
            'category' => ['required', Rule::in(self::CATEGORIES)],
            'hashtags' => ['nullable', 'string', 'max:150'],
            'schedule_date' => ['required', 'date'],
            'schedule_time' => ['required', 'date_format:H:i'],
            'media' => [
                'nullable',
                'file',
                'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/webm',
                'max:25600',
            ],
        ]);

        $scheduledAt = Carbon::createFromFormat(
            '!Y-m-d H:i',
            $validated['schedule_date'].' '.$validated['schedule_time']
        );

        if ($scheduledAt->isPast()) {
            return back()->withInput()->withErrors([
                'schedule_time' => 'Please select a future date and time.',
            ]);
        }

        $file = $request->file('media');
        if ($file && str_starts_with($file->getMimeType(), 'image/') && $file->getSize() > 5 * 1024 * 1024) {
            return back()->withInput()->withErrors([
                'media' => 'Images must be 5 MB or smaller.',
            ]);
        }

        $user = $request->user() ?? User::query()->orderBy('user_id')->first();
        if (!$user) {
            return back()->withInput()->withErrors([
                'content' => 'No user account exists yet. Run the database seeder before creating posts.',
            ]);
        }

        $categoryType = match ($validated['category']) {
            'Sports' => 'Sport',
            'Books & Literature' => 'Books and Literature',
            default => $validated['category'],
        };
        $storedPath = null;

        try {
            DB::transaction(function () use ($validated, $scheduledAt, $file, $user, $categoryType, &$storedPath): void {
                $category = Category::query()->firstOrCreate([
                    'category_type' => $categoryType,
                ]);

                $post = Post::query()->create([
                    'descriptions' => trim($validated['content']),
                    'category_id' => $category->category_id,
                    'hashtag' => trim($validated['hashtags'] ?? '') ?: null,
                    'state' => 'scheduled',
                    'date_time' => $scheduledAt,
                    'user_id' => $user->user_id,
                ]);

                if ($file) {
                    $storedPath = $file->store('post-media', 'public');

                    $post->media()->create([
                        'media_type' => str_starts_with($file->getMimeType(), 'image/') ? 'photo' : 'video',
                        'mime_type' => $file->getMimeType(),
                        'original_filename' => $file->getClientOriginalName(),
                        'media_data' => null,
                        'media_path' => $storedPath,
                        'file_size' => $file->getSize(),
                    ]);
                }
            });
        } catch (Throwable $exception) {
            if ($storedPath) {
                Storage::disk('public')->delete($storedPath);
            }

            throw $exception;
        }

        return redirect()->route('schedule-post')->with('status', 'Scheduled post saved to the database.');
    }

    public function media(PostMedia $media)
    {
        abort_unless($media->media_path && Storage::disk('public')->exists($media->media_path), 404);

        return Storage::disk('public')->response($media->media_path, $media->original_filename, [
            'Content-Type' => $media->mime_type,
        ]);
    }

    public function destroy(Post $post): RedirectResponse
    {
        abort_unless($post->state === 'scheduled', 404);

        $mediaPaths = $post->media()
            ->whereNotNull('media_path')
            ->pluck('media_path')
            ->all();

        DB::transaction(fn () => $post->delete());
        Storage::disk('public')->delete($mediaPaths);

        return redirect()->route('schedule-post')->with('status', 'Scheduled post cancelled.');
    }
}