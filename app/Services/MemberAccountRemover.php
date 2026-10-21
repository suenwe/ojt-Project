<?php

namespace App\Services;

use App\Models\PostMedia;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MemberAccountRemover
{
    /**
     * Permanently delete a member, their logins and their uploaded post files.
     *
     * The database removes their posts, comments, reactions, views and reports along with the account.
     */
    public function delete(User $member): void
    {
        $mediaPaths = PostMedia::query()
            ->whereIn('post_id', $member->posts()->select('post_id'))
            ->whereNotNull('media_path')
            ->pluck('media_path')
            ->all();

        DB::transaction(function () use ($member): void {
            DB::table('sessions')->where('user_id', $member->getKey())->delete();
            $member->delete();
        });

        Storage::disk('public')->delete($mediaPaths);
    }
}
