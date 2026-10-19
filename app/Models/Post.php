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
     * Limit to posts members can see: published ones, and scheduled ones whose time has come.
     */
    public function scopeVisibleInFeed(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->where('state', 'published')
                ->orWhere(function (Builder $query): void {
                    $query->where('state', 'scheduled')->where('date_time', '<=', now());
                });
        });
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
                preg_match_all('/#[\pL\pN_]+/u', $hashtags, $matches);

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

    public function media(): HasMany
    {
        return $this->hasMany(PostMedia::class, 'post_id', 'post_id');
    }
}
