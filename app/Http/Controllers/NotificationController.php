<?php

namespace App\Http\Controllers;

use App\Models\ActivityNotification;
use App\Services\ActivityNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * The member's latest notifications, newest first, for the bell's drop-down.
     */
    public function index(Request $request, ActivityNotifier $notifier): View
    {
        $notifier->createDueScheduleReminders($request->user());

        $notifications = ActivityNotification::query()
            ->where('user_id', $request->user()->user_id)
            ->with([
                'actor:user_id,username,photo',
                'post:post_id,user_id,descriptions,state,date_time',
                'comment:comment_id,content',
            ])
            ->latest('updated_at')
            ->limit(30)
            ->get();

        return view('post_forms.notifications', ['notifications' => $notifications]);
    }

    /**
     * How many notifications the member hasn't seen yet (the bell's red number).
     */
    public function unreadCount(Request $request, ActivityNotifier $notifier): JsonResponse
    {
        $notifier->createDueScheduleReminders($request->user());

        return response()->json([
            'unreadCount' => ActivityNotification::query()
                ->where('user_id', $request->user()->user_id)
                ->unread()
                ->count(),
        ]);
    }

    /**
     * Mark all of the member's notifications as seen (when they open the bell).
     */
    public function markAllRead(Request $request): JsonResponse
    {
        ActivityNotification::query()
            ->where('user_id', $request->user()->user_id)
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['unreadCount' => 0]);
    }
}
