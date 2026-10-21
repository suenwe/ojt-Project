<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberProfileController extends Controller
{
    /**
     * Show a member's public profile with the posts other members can see.
     */
    public function __invoke(Request $request, User $user): View
    {
        abort_if($user->isBanned() && $request->user()->role !== 'admin', 404);

        $posts = $user->posts()
            ->visibleInFeed()
            ->withFeedDetails($request->user())
            ->latest('date_time')
            ->limit(50)
            ->get();
        Post::loadTopReactionTypes($posts);

        return view('welcome', [
            'member' => $user,
            'isOwnProfile' => $request->user()->is($user),
            'memberPostCount' => $user->posts()->visibleInFeed()->count(),
            'posts' => $posts,
        ]);
    }
}
