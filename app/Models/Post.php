<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    protected $table = 'posts';

    protected $primaryKey = 'post_id';

    public $timestamps = false;

    protected $fillable = [
        'photos_and_videos',
        'descriptions',
        'category_id',
        'hashtag',
        'state',
        'date_time',
        'user_id',
        'post_type',
    ];

    protected function casts(): array
    {
        return [
            'photos_and_videos' => 'array',
            'date_time' => 'datetime',
        ];
    }

    /**
     * Limit to posts members can see: published ones, and scheduled ones whose time has come,
     * from authors who are not suspended right now.
     */
    public function scopeVisibleInFeed(Builder $query): Builder
    {
        return $query
            ->where(function (Builder $query): void {
                $query->where('state', 'published')
                    ->orWhere(function (Builder $query): void {
                        $query->where('state', 'scheduled')->where('date_time', '<=', now());
                    });
            })
            ->whereDoesntHave('user', fn (Builder $author) => $author->currentlyBanned());
    }

    /**
     * Count the hashtags used in recent visible posts, most used first.
     *
     * @return array<string, int> hashtag => number of posts that use it
     */
    public static function popularHashtags(int $limit = 5, int $days = 30): array
    {
        $counts = [];

        static::query()
            ->visibleInFeed()
            ->whereNotNull('hashtag')
            ->where('date_time', '>=', now()->subDays($days))
            ->latest('date_time')
            ->limit(500)
            ->pluck('hashtag')
            ->each(function (string $hashtags) use (&$counts): void {
                // \pM keeps vowel signs and other marks, which Burmese (Myanmar) words are built from.
                preg_match_all('/#[\pL\pM\pN_]+/u', $hashtags, $matches);

                foreach (array_unique(array_map('mb_strtolower', $matches[0])) as $tag) {
                    $counts[$tag] = ($counts[$tag] ?? 0) + 1;
                }
            });

        arsort($counts);

        return array_slice($counts, 0, $limit, true);
    }

    /**
     * Whether the post is scheduled and its time has not come yet.
     */
    /**
     * Limit the query to scheduled posts whose publish time has not come yet.
     */
    /**
     * Load everything a post card in the feed needs: author, category, files, counts and the viewer's own reaction.
     */
    public function scopeWithFeedDetails(Builder $query, User $viewer): Builder
    {
        return $query
            ->with(['user:user_id,username,photo', 'category:category_id,category_type', 'media'])
            ->withCount(['reactions', 'comments', 'views'])
            ->addSelect(['viewer_reaction' => Reaction::query()
                ->select('type')
                ->whereColumn('reactions.post_id', 'posts.post_id')
                ->where('reactions.user_id', $viewer->user_id)
                ->limit(1),
            ]);
    }

    /**
     * Give each post a `top_reaction_types` list (most used first, ties in picker order, at most three) with one query for all of them.
     *
     * @param  iterable<Post>  $posts
     */
    public static function loadTopReactionTypes(iterable $posts): void
    {
        $posts = collect($posts);

        $countsByPost = Reaction::query()
            ->whereIn('post_id', $posts->pluck('post_id'))
            ->selectRaw('post_id, type, count(*) as total')
            ->groupBy('post_id', 'type')
            ->get()
            ->groupBy('post_id');

        $typeOrder = array_flip(array_keys(Reaction::TYPES));

        $posts->each(function (Post $post) use ($countsByPost, $typeOrder): void {
            $post->setAttribute('top_reaction_types', ($countsByPost[$post->post_id] ?? collect())
                ->sort(fn (Reaction $first, Reaction $second): int => [(int) $second->total, $typeOrder[$first->type] ?? 99]
                    <=> [(int) $first->total, $typeOrder[$second->type] ?? 99])
                ->take(3)
                ->pluck('type')
                ->values()
                ->all());
        });
    }

    /**
     * Whether members can see (and so react to, comment on and view) this post right now.
     */
    public function isVisibleInFeed(): bool
    {
        $isLive = $this->state === 'published'
            || ($this->state === 'scheduled' && $this->date_time?->isPast() === true);

        return $isLive && $this->user?->isBanned() !== true;
    }

    public function scopeUpcomingScheduled(Builder $query): Builder
    {
        return $query->where('state', 'scheduled')->where('date_time', '>', now());
    }

    /**
     * Describe the post's photos and videos for the preview slider on edit forms.
     *
     * @return list<array{url: string, type: string}>
     */
    public function mediaPreviewItems(): array
    {
        return $this->media
            ->map(fn (PostMedia $media): array => [
                'url' => route('media.show', $media),
                'type' => (string) $media->mime_type,
            ])
            ->values()
            ->all();
    }

    public function isUpcomingScheduled(): bool
    {
        return $this->state === 'scheduled' && $this->date_time?->isFuture() === true;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'post_id', 'post_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class, 'post_id', 'post_id');
    }

    public function views(): HasMany
    {
        return $this->hasMany(PostView::class, 'post_id', 'post_id');
    }

    public function activityNotifications(): HasMany
    {
        return $this->hasMany(ActivityNotification::class, 'post_id', 'post_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(PostMedia::class, 'post_id', 'post_id');
    }
}
