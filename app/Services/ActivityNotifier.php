<?php

namespace App\Services;

use App\Models\ActivityNotification;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

/**
 * Creates and removes bell notifications when members react, comment or reply.
 * Nobody is notified about their own actions.
 */
class ActivityNotifier
{
    /**
     * How long before a scheduled post goes live its author gets a reminder.
     */
    public const SCHEDULE_REMINDER_MINUTES = 5;

    /**
     * Create the reminders that are due for the member's scheduled posts (at most one per post).
     * This runs whenever the member loads a page or the bell checks for news, so no background program is needed.
     */
    public function createDueScheduleReminders(User $member): void
    {
        Post::query()
            ->where('user_id', $member->user_id)
            ->upcomingScheduled()
            ->where('date_time', '<=', now()->addMinutes(self::SCHEDULE_REMINDER_MINUTES))
            ->whereDoesntHave('activityNotifications', fn ($query) => $query->where('type', ActivityNotification::TYPE_SCHEDULE_REMINDER))
            ->pluck('post_id')
            ->each(fn (int $postId) => ActivityNotification::query()->create([
                'user_id' => $member->user_id,
                'actor_id' => $member->user_id,
                'type' => ActivityNotification::TYPE_SCHEDULE_REMINDER,
                'post_id' => $postId,
            ]));
    }

    /**
     * Drop a post's reminder when its time changes, so a new one comes before the new time.
     */
    public function forgetScheduleReminder(Post $post): void
    {
        $post->activityNotifications()
            ->where('type', ActivityNotification::TYPE_SCHEDULE_REMINDER)
            ->delete();
    }

    /**
     * Keep one reaction notification per person per post: create or update it, or remove it when the reaction is gone.
     */
    public function reactionChanged(Post $post, User $actor, ?string $reactionType): void
    {
        if ($post->user_id === $actor->user_id) {
            return;
        }

        $key = [
            'user_id' => $post->user_id,
            'actor_id' => $actor->user_id,
            'type' => ActivityNotification::TYPE_REACTION,
            'post_id' => $post->post_id,
        ];

        if ($reactionType === null) {
            ActivityNotification::query()->where($key)->delete();

            return;
        }

        ActivityNotification::query()->updateOrCreate($key, [
            'reaction_type' => $reactionType,
            'read_at' => null,
        ]);
    }

    /**
     * A new comment notifies the post's author; a reply also notifies the person whose comment was answered.
     */
    public function commentAdded(Comment $comment, User $actor): void
    {
        $post = $comment->post;
        $repliedTo = $comment->parent;

        if ($repliedTo !== null) {
            $this->notify($repliedTo->user_id, $actor, ActivityNotification::TYPE_REPLY, $comment);
        }

        if ($repliedTo?->user_id !== $post->user_id) {
            $this->notify($post->user_id, $actor, ActivityNotification::TYPE_COMMENT, $comment);
        }
    }

    private function notify(int $recipientId, User $actor, string $type, Comment $comment): void
    {
        if ($recipientId === $actor->user_id) {
            return;
        }

        ActivityNotification::query()->create([
            'user_id' => $recipientId,
            'actor_id' => $actor->user_id,
            'type' => $type,
            'post_id' => $comment->post_id,
            'comment_id' => $comment->comment_id,
        ]);
    }
}
