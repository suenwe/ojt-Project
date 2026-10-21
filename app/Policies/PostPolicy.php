<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Only the author can edit their post.
     */
    public function update(User $user, Post $post): bool
    {
        return $post->user_id === $user->user_id;
    }

    /**
     * Only the author can see the list of who viewed their post.
     */
    public function viewViewers(User $user, Post $post): bool
    {
        return $post->user_id === $user->user_id;
    }

    /**
     * Everyone can open the photos and videos of live posts; those of scheduled, hidden
     * or suspended members' posts only open for the author and for admins (report queue).
     */
    public function viewMedia(User $user, Post $post): bool
    {
        return $post->isVisibleInFeed()
            || $post->user_id === $user->user_id
            || $user->role === 'admin';
    }

    /**
     * Only the author can delete their post.
     */
    public function delete(User $user, Post $post): bool
    {
        return $post->user_id === $user->user_id;
    }
}
