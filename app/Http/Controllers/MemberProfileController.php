<?php

namespace App\Http\Controllers;

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
            ->with(['user:user_id,username', 'category:category_id,category_type', 'media'])
            ->withCount(['reactions', 'comments', 'views'])
            ->latest('date_time')
            ->limit(50)
            ->get();

        return view('welcome', [
            'member' => $user,
            'isOwnProfile' => $request->user()->is($user),
            'memberPostCount' => $user->posts()->visibleInFeed()->count(),
            'posts' => $posts,
        ]);
    }
}
