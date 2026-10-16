<?php

namespace App\Models;

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
    ];

    protected function casts(): array
    {
        return [
            'photos_and_videos' => 'array',
            'date_time' => 'datetime',
        ];
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