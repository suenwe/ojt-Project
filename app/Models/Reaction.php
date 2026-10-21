<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reaction extends Model
{
    protected $table = 'reactions';

    protected $primaryKey = 'reaction_id';

    /**
     * The reactions members can pick, in the order they appear in the picker.
     *
     * @var array<string, array{label: string, emoji: string}>
     */
    public const TYPES = [
        'like' => ['label' => 'Like', 'emoji' => '👍'],
        'love' => ['label' => 'Love', 'emoji' => '❤️'],
        'haha' => ['label' => 'Haha', 'emoji' => '😆'],
        'wow' => ['label' => 'Wow', 'emoji' => '😮'],
        'sad' => ['label' => 'Sad', 'emoji' => '😢'],
        'angry' => ['label' => 'Angry', 'emoji' => '😠'],
    ];

    protected $fillable = [
        'user_id',
        'post_id',
        'type',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id', 'post_id');
    }
}
