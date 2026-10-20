<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Reaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReactionController extends Controller
{
    /**
     * List who reacted to a post and how, for the "who reacted" pop-up.
     */
    public function index(Post $post): View
    {
        abort_unless($post->isVisibleInFeed(), 404);

        $reactions = $post->reactions()
            ->with('user:user_id,username,photo')
            ->latest('updated_at')
            ->get();

        return view('post_forms.people-list', [
            'people' => $reactions->map(fn (Reaction $reaction): array => [
                'user' => $reaction->user,
                'type' => $reaction->type,
                'detail' => Reaction::TYPES[$reaction->type]['emoji'] ?? '',
                'detailLabel' => Reaction::TYPES[$reaction->type]['label'] ?? '',
            ]),
            'reactionTabs' => collect(Reaction::TYPES)
                ->map(fn (array $reaction, string $type): int => $reactions->where('type', $type)->count())
                ->filter(),
            'emptyMessage' => 'No reactions yet.',
        ]);
    }

    /**
     * Set, change or remove the member's reaction to a post.
     * Picking the reaction you already have removes it; picking another one replaces it.
     */
    public function store(Request $request, Post $post): JsonResponse|RedirectResponse
    {
        abort_unless($post->isVisibleInFeed(), 404);

        $validated = $request->validate([
            'type' => ['required', Rule::in(array_keys(Reaction::TYPES))],
        ]);

        $member = $request->user();
        $currentReaction = $post->reactions()->where('user_id', $member->user_id)->first();

        if ($currentReaction?->type === $validated['type']) {
            $currentReaction->delete();
        } else {
            Reaction::query()->updateOrCreate(
                ['post_id' => $post->post_id, 'user_id' => $member->user_id],
                ['type' => $validated['type']],
            );
        }

        if (! $request->expectsJson()) {
            return back();
        }

        Post::loadTopReactionTypes([$post]);

        return response()->json([
            'viewerReaction' => $post->reactions()->where('user_id', $member->user_id)->value('type'),
            'reactionCount' => $post->reactions()->count(),
            'topReactionTypes' => $post->top_reaction_types,
        ]);
    }
}
