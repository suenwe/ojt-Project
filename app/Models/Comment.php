<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    protected $table = 'comments';

    protected $primaryKey = 'comment_id';

    /**
     * Replies nest under the comment they answer, up to this many steps; deeper replies stay at this depth.
     */
    public const MAX_REPLY_DEPTH = 3;

    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'content',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id', 'post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            Comment::class,
            'parent_id',
            'comment_id'
        );
    }

    public function replies(): HasMany
    {
        return $this->hasMany(
            Comment::class,
            'parent_id',
            'comment_id'
        );
    }

    /**
     * Whether the comment was changed after it was posted.
     */
    public function wasEdited(): bool
    {
        return $this->created_at !== null
            && $this->updated_at !== null
            && $this->updated_at->greaterThan($this->created_at->copy()->addSecond());
    }
}
