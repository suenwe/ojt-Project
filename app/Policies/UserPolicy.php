<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Admins can open any member's admin detail page.
     */
    public function view(User $actor, User $member): bool
    {
        return $actor->role === 'admin';
    }

    /**
     * Admins can add member accounts.
     */
    public function create(User $actor): bool
    {
        return $actor->role === 'admin';
    }

    /**
     * Admins can edit members, but never their own account or another admin from the admin tools.
     */
    public function update(User $actor, User $member): bool
    {
        return $this->managesMember($actor, $member);
    }

    /**
     * Admins can delete members, but never their own account or another admin.
     */
    public function delete(User $actor, User $member): bool
    {
        return $this->managesMember($actor, $member);
    }

    /**
     * Members can delete their own account; admins cannot, so the site always keeps its admins.
     */
    public function deleteOwnAccount(User $actor, User $member): bool
    {
        return $actor->is($member) && $actor->role !== 'admin';
    }

    private function managesMember(User $actor, User $member): bool
    {
        return $actor->role === 'admin'
            && $member->role !== 'admin'
            && ! $actor->is($member);
    }
}
