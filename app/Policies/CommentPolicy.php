<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * Only the person who wrote a comment can edit it.
     */
    public function update(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->user_id;
    }

    /**
     * Members can delete their own comments, and any comment on their own post.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->user_id
            || $comment->post?->user_id === $user->user_id;
    }
}
