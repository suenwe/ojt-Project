<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Keyword;
use App\Models\Notification;
use App\Models\Report;
use App\Models\User;
use App\Models\UserAccessLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $section = $request->query('section', 'overview');
        abort_unless(in_array($section, ['overview', 'reports', 'users', 'access-logs', 'categories', 'restricted'], true), 404);

        $userFilters = [
            'search' => trim((string) $request->query('search', '')),
            'role' => '',
            'status' => '',
        ];

        $users = null;
        if (in_array($section, ['overview', 'users'], true)) {
            $userQuery = User::query()
                ->select(['user_id', 'username', 'email', 'photo', 'birthday', 'browser', 'IP_address', 'role', 'banned_at', 'ban_expires_at', 'created_date']);

            if ($section === 'overview') {
                $userQuery->where('role', '!=', 'admin');
            } else {
                $search = $userFilters['search'];
                $role = $request->query('role');
                $status = $request->query('status');

                if ($search !== '') {
                    $search = addcslashes(mb_strtolower($search), '\\%_');
                    $userQuery->where(function ($query) use ($search): void {
                        $query->whereRaw('LOWER(username) LIKE ?', ['%'.$search.'%'])
                            ->orWhereRaw('LOWER(email) LIKE ?', ['%'.$search.'%']);
                    });
                }

                if (in_array($role, ['user', 'admin'], true)) {
                    $userQuery->where('role', $role);
                    $userFilters['role'] = $role;
                }

                if ($status === 'banned') {
                    $userQuery->whereNotNull('banned_at')
                        ->where(function ($query): void {
                            $query->whereNull('ban_expires_at')
                                ->orWhere('ban_expires_at', '>', now());
                        });
                    $userFilters['status'] = $status;
                } elseif ($status === 'active') {
                    $userQuery->where(function ($query): void {
                        $query->whereNull('banned_at')
                            ->orWhere(function ($query): void {
                                $query->whereNotNull('ban_expires_at')
                                    ->where('ban_expires_at', '<=', now());
                            });
                    });
                    $userFilters['status'] = $status;
                }
            }

            $users = $userQuery
                ->latest('created_date')
                ->paginate(8, ['*'], 'users_page')
                ->withQueryString();

            $users->getCollection()->transform(function (User $user): User {
                $user->setAttribute('photo_data_uri', $this->photoDataUri($user->photo));

                return $user;
            });
        }

        $accessLogUserId = filter_var($request->query('user_id'), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]) ?: null;
        $accessLogs = $section === 'access-logs'
            ? UserAccessLog::query()
                ->with('user:user_id,username,email,role,banned_at,ban_expires_at')
                ->when($accessLogUserId, fn ($query) => $query->where('user_id', $accessLogUserId))
                ->latest('accessed_at')
                ->paginate(25, ['*'], 'logs_page')
                ->withQueryString()
            : null;

        $reports = null;
        if (in_array($section, ['overview', 'reports'], true)) {
            $reports = Report::query()
                ->with(['reporter:user_id,username,email', 'reviewer:user_id,username', 'post.user:user_id,username,email', 'post.category:category_id,category_type'])
                ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
                ->latest('report_id')
                ->paginate(8, ['*'], 'reports_page')
                ->withQueryString();
        }

        return view('admin.dashboard', [
            'section' => $section,
            'admin' => $request->user(),
            'users' => $users,
            'userFilters' => $userFilters,
            'accessLogs' => $accessLogs,
            'reports' => $reports,
            'categories' => $section === 'categories' ? Category::query()->withCount('posts')->orderBy('category_type')->get() : collect(),
            'keywords' => $section === 'restricted' ? Keyword::query()->orderBy('keyword_text')->get() : collect(),
            'notifications' => $section === 'notifications' ? Notification::query()->withCount('recipients')->orderBy('notification_type')->get() : collect(),
            'stats' => [
                'users' => User::query()->where('role', '!=', 'admin')->count(),
                'pending_reports' => Report::query()->where('status', 'pending')->count(),
                'categories' => Category::query()->count(),
                'banned_users' => User::query()
                    ->whereNotNull('banned_at')
                    ->where(function ($query): void {
                        $query->whereNull('ban_expires_at')
                            ->orWhere('ban_expires_at', '>', now());
                    })
                    ->count(),
            ],
        ]);
    }

    private function photoDataUri(mixed $photo): ?string
    {
        if (is_resource($photo)) {
            $photo = stream_get_contents($photo);
        }

        if (! is_string($photo) || $photo === '') {
            return null;
        }

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->buffer($photo);

        if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)) {
            return null;
        }

        return 'data:'.$mimeType.';base64,'.base64_encode($photo);
    }
}
