<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Services\PostPublisher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class ScheduledPostController extends Controller
{
    /**
     * Show the schedule form and the member's upcoming scheduled posts.
     */
    public function index(Request $request): View
    {
        return view('welcome', [
            'hidePosts' => true,
            'isScheduling' => true,
            'categories' => Category::query()->orderBy('category_type')->get(),
            'scheduledPosts' => Post::query()
                ->where('user_id', $request->user()->user_id)
                ->where('state', 'scheduled')
                ->where('date_time', '>', now())
                ->with('category:category_id,category_type')
                ->withCount('media')
                ->orderBy('date_time')
                ->get(),
        ]);
    }

    /**
     * Save a post that will appear in the feed at the chosen time.
     */
    public function store(StorePostRequest $request, PostPublisher $publisher): RedirectResponse
    {
        try {
            $publisher->create(
                $request->user(),
                $request->validated(),
                $request->file('media') ?? [],
                'scheduled',
                Carbon::parse($request->validated('scheduled_at')),
            );
        } catch (Throwable $exception) {
            Log::error('Scheduled post store failed: '.$exception->getMessage());

            return back()->withInput()->withErrors([
                'content' => 'Your scheduled post could not be saved. Please try again.',
            ]);
        }

        return redirect()->route('posts.schedule')->with('status', 'Your post is scheduled.');
    }

    /**
     * Cancel one of the member's own scheduled posts before it goes live.
     */
    public function destroy(Request $request, Post $post, PostPublisher $publisher): RedirectResponse
    {
        abort_unless($post->user_id === $request->user()->user_id, 403);
        abort_unless($post->state === 'scheduled' && $post->date_time->isFuture(), 404);

        $publisher->delete($post);

        return redirect()->route('posts.schedule')->with('status', 'Scheduled post cancelled.');
    }
}
