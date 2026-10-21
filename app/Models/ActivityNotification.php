<?php

namespace App\Models;

use Database\Factories\ActivityNotificationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A bell notification: someone (the actor) reacted to, commented on or replied to something of the member's (the user).
 */
class ActivityNotification extends Model
{
    /** @use HasFactory<ActivityNotificationFactory> */
    use HasFactory;

    public const TYPE_REACTION = 'reaction';

    public const TYPE_COMMENT = 'comment';

    public const TYPE_REPLY = 'reply';

    public const TYPE_SCHEDULE_REMINDER = 'schedule_reminder';

    protected $table = 'activity_notifications';

    protected $primaryKey = 'activity_id';

    protected $fillable = [
        'user_id',
        'actor_id',
        'type',
        'post_id',
        'comment_id',
        'reaction_type',
        'read_at',
    ];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id', 'user_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id', 'post_id');
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'comment_id', 'comment_id');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function isScheduleReminder(): bool
    {
        return $this->type === self::TYPE_SCHEDULE_REMINDER;
    }

    /**
     * A schedule reminder can still be edited or cancelled until the post goes live.
     */
    public function canStillChangeSchedule(): bool
    {
        return $this->isScheduleReminder() && $this->post?->isUpcomingScheduled() === true;
    }

    /**
     * What happened, without the actor's name, e.g. "reacted ❤️ to your post".
     * Schedule reminders read as a full sentence because they're about the member's own post.
     */
    public function summary(): string
    {
        if ($this->isScheduleReminder()) {
            $publishAt = $this->post?->date_time;
            $time = $publishAt?->isToday() ? $publishAt->format('g:i A') : $publishAt?->format('M j \a\t g:i A');

            return $this->canStillChangeSchedule()
                ? 'Your scheduled post goes live at '.$time
                : 'Your scheduled post went live at '.$time;
        }

        return match ($this->type) {
            self::TYPE_REACTION => 'reacted '.(Reaction::TYPES[$this->reaction_type]['emoji'] ?? '').' to your post',
            self::TYPE_REPLY => 'replied to your comment',
            default => 'commented on your post',
        };
    }

    /**
     * A short quote of what it's about: the comment for comments and replies, otherwise the post.
     */
    public function excerpt(): string
    {
        $text = in_array($this->type, [self::TYPE_REACTION, self::TYPE_SCHEDULE_REMINDER], true)
            ? $this->post?->descriptions
            : $this->comment?->content;

        return Str::limit((string) $text, 70);
    }

    /**
     * Where clicking the notification goes: the post on its author's profile, with the comments opened at the comment.
     * A reminder for a post that isn't live yet has nowhere to go (it shows Edit and Delete instead).
     */
    public function targetUrl(): ?string
    {
        $post = $this->post;

        if ($this->canStillChangeSchedule()) {
            return null;
        }

        return route('users.show', [
            'user' => $post->user_id,
            'open_post' => $post->post_id,
            'comment' => $this->comment_id,
        ]).'#post-'.$post->post_id;
    }
}
