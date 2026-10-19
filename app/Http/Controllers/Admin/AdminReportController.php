<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use App\Notifications\ReportConfirmed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminReportController extends Controller
{
    public function review(Request $request, Report $report): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:valid,invalid'],
        ]);

        $banExpiresAt = null;
        $authorId = DB::transaction(function () use ($request, $report, $validated, &$banExpiresAt): ?int {
            $report = Report::query()->with('post.user')->lockForUpdate()->findOrFail($report->report_id);

            if ($report->status !== 'pending') {
                throw ValidationException::withMessages([
                    'decision' => 'This report has already been reviewed.',
                ]);
            }

            if ($validated['decision'] === 'valid') {
                $author = $report->post?->user;

                if (! $author) {
                    throw ValidationException::withMessages([
                        'decision' => 'The post author could not be found, so this report cannot be confirmed.',
                    ]);
                }

                if ($author->role === 'admin') {
                    throw ValidationException::withMessages([
                        'decision' => 'Admin accounts cannot be banned through post reports.',
                    ]);
                }

                if ($author->isBanned() && $author->ban_expires_at === null) {
                    $banExpiresAt = null;
                } else {
                    $author->forceFill([
                        'banned_at' => now(),
                        'ban_expires_at' => $banExpiresAt = now()->addDays(3),
                        'ban_reason' => 'Confirmed report #'.$report->report_id.': '.$report->report_type,
                    ])->save();
                }

                $authorId = (int) $author->user_id;
            } else {
                $authorId = null;
            }

            $report->forceFill([
                'status' => $validated['decision'],
                'reviewed_at' => now(),
                'reviewed_by' => $request->user()->user_id,
            ])->save();

            return $authorId;
        });

        if ($authorId !== null) {
            User::query()->find($authorId)?->notify(new ReportConfirmed($report->report_type, $banExpiresAt));
        }

        return to_route('admin.dashboard', ['section' => 'reports'])
            ->with('status', $validated['decision'] === 'valid'
                ? ($banExpiresAt === null
                    ? 'Report confirmed. The post author remains under an indefinite admin suspension.'
                    : 'Report confirmed. The post author has been suspended for three days.')
                : 'Report dismissed.');
    }
}
