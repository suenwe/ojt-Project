<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use App\Notifications\PostReported;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'post_id' => ['required', 'integer', 'exists:posts,post_id'],
            'report_type' => ['required', Rule::in(Report::REPORT_TYPES)],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $post = Post::query()->findOrFail($validated['post_id']);

        if ($post->user_id === $request->user()->user_id) {
            throw ValidationException::withMessages([
                'report_type' => 'You cannot report your own post.',
            ]);
        }

        $report = Report::query()->create([
            'reporter_id' => $request->user()->user_id,
            'target_id' => $post->post_id,
            'report_type' => $validated['report_type'],
            'reason' => $validated['reason'] ?? null,
        ])->load(['reporter', 'post.user']);

        User::query()
            ->where('role', 'admin')
            ->each(fn (User $admin) => $admin->notify(new PostReported($report)));

        return to_route('home')->with('status', 'Your report was sent to the admin team for review.');
    }
}
