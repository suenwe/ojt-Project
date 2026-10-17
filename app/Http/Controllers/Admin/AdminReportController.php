<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
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

        DB::transaction(function () use ($request, $report, $validated): void {
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

                $author->forceFill([
                    'banned_at' => $author->banned_at ?? now(),
                    'ban_reason' => $author->ban_reason ?: 'Confirmed report #'.$report->report_id.': '.$report->report_type,
                ])->save();
            }

            $report->forceFill([
                'status' => $validated['decision'],
                'reviewed_at' => now(),
                'reviewed_by' => $request->user()->user_id,
            ])->save();
        });

        return to_route('admin.dashboard', ['section' => 'reports'])
            ->with('status', $validated['decision'] === 'valid'
                ? 'Report confirmed. The post author has been banned.'
                : 'Report dismissed.');
    }
}
