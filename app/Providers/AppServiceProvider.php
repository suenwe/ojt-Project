<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('welcome', function ($view): void {
            $view->with('popularHashtags', Post::popularHashtags());

            $member = auth()->user();

            if ($member instanceof User) {
                $view->with('scheduleCalendar', $this->scheduleCalendarFor($member));
                $view->with('scheduleCategories', Category::query()->orderBy('category_type')->get());
            }
        });
    }

    /**
     * Build the data the round calendar button needs: today's date and the member's upcoming scheduled posts.
     *
     * @return array{today: string, posts: list<array{id: int, date: string, time: string, publishAt: string, text: string, hashtag: ?string, categoryId: int, postType: ?string, mediaCount: int, media: list<array{url: string, type: string}>, updateUrl: string, deleteUrl: string}>}
     */
    private function scheduleCalendarFor(User $member): array
    {
        $posts = Post::query()
            ->where('user_id', $member->user_id)
            ->upcomingScheduled()
            ->with('media')
            ->orderBy('date_time')
            ->get()
            ->map(fn (Post $post): array => [
                'id' => $post->post_id,
                'date' => $post->date_time->format('Y-m-d'),
                'time' => $post->date_time->format('H:i'),
                'publishAt' => $post->date_time->toIso8601String(),
                'text' => $post->descriptions,
                'hashtag' => $post->hashtag,
                'categoryId' => $post->category_id,
                'postType' => $post->post_type,
                'mediaCount' => $post->media->count(),
                'media' => $post->mediaPreviewItems(),
                'updateUrl' => route('posts.update', $post),
                'deleteUrl' => route('schedule-post.destroy', $post),
            ])
            ->values()
            ->all();

        return [
            'today' => now()->format('Y-m-d'),
            'posts' => $posts,
        ];
    }
}
