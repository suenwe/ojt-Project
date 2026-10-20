<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReactionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guests_are_redirected_to_sign_in(): void
    {
        $post = $this->createPost(User::factory()->create());

        $this->post(route('posts.reactions.store', $post), ['type' => 'like'])
            ->assertRedirect(route('login'));

        $this->assertSame(0, Reaction::query()->count());
    }

    public function test_member_reacts_and_gets_the_new_count_and_summary(): void
    {
        $post = $this->createPost(User::factory()->create());
        $member = User::factory()->create();

        $response = $this->actingAs($member)->postJson(route('posts.reactions.store', $post), ['type' => 'love']);

        $response->assertOk();
        $response->assertExactJson(['viewerReaction' => 'love', 'reactionCount' => 1, 'topReactionTypes' => ['love']]);
        $this->assertDatabaseHas('reactions', ['post_id' => $post->post_id, 'user_id' => $member->user_id, 'type' => 'love']);
    }

    public function test_picking_another_reaction_changes_it_and_picking_the_same_one_removes_it(): void
    {
        $post = $this->createPost(User::factory()->create());
        $member = User::factory()->create();
        $this->actingAs($member)->postJson(route('posts.reactions.store', $post), ['type' => 'like']);

        $this->postJson(route('posts.reactions.store', $post), ['type' => 'haha'])
            ->assertJson(['viewerReaction' => 'haha', 'reactionCount' => 1]);
        $this->assertSame(['haha'], Reaction::query()->pluck('type')->all());

        $this->postJson(route('posts.reactions.store', $post), ['type' => 'haha'])
            ->assertExactJson(['viewerReaction' => null, 'reactionCount' => 0, 'topReactionTypes' => []]);
        $this->assertSame(0, Reaction::query()->count());
    }

    public function test_unknown_reaction_type_is_rejected(): void
    {
        $post = $this->createPost(User::factory()->create());

        $this->actingAs(User::factory()->create())
            ->postJson(route('posts.reactions.store', $post), ['type' => 'party'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['type' => 'The selected type is invalid.']);

        $this->assertSame(0, Reaction::query()->count());
    }

    public function test_posts_that_are_not_live_yet_cannot_be_reacted_to(): void
    {
        $post = $this->createPost(User::factory()->create(), 'scheduled', now()->addDay());

        $this->actingAs(User::factory()->create())
            ->postJson(route('posts.reactions.store', $post), ['type' => 'like'])
            ->assertNotFound();

        $this->assertSame(0, Reaction::query()->count());
    }

    public function test_without_javascript_reacting_returns_to_the_page(): void
    {
        $post = $this->createPost(User::factory()->create());

        $this->actingAs(User::factory()->create())
            ->from(route('home'))
            ->post(route('posts.reactions.store', $post), ['type' => 'wow'])
            ->assertRedirect(route('home'));

        $this->assertSame(['wow'], Reaction::query()->pluck('type')->all());
    }

    public function test_feed_shows_the_viewers_reaction_and_the_most_used_reactions(): void
    {
        $post = $this->createPost(User::factory()->create());
        $viewer = User::factory()->create();
        foreach (['love', 'love', 'sad'] as $type) {
            Reaction::query()->create(['post_id' => $post->post_id, 'user_id' => User::factory()->create()->user_id, 'type' => $type]);
        }
        Reaction::query()->create(['post_id' => $post->post_id, 'user_id' => $viewer->user_id, 'type' => 'wow']);

        $response = $this->actingAs($viewer)->get(route('home'));

        $response->assertOk();
        $response->assertSee('data-reaction-emojis>❤️😮😢</span>', false);
        $response->assertSee('4 reactions');
        $response->assertSee('class="post-action reaction-main is-reacted reaction-wow"', false);
        $response->assertSee('value="wow" title="Wow" aria-label="Wow" aria-pressed="true"', false);
    }

    public function test_reaction_list_shows_who_reacted_with_a_tab_per_emoji(): void
    {
        $post = $this->createPost(User::factory()->create());
        foreach (['ana' => 'wow', 'bo' => 'love', 'cy' => 'love'] as $username => $type) {
            Reaction::query()->create(['post_id' => $post->post_id, 'user_id' => User::factory()->create(['username' => $username])->user_id, 'type' => $type]);
        }

        $response = $this->actingAs(User::factory()->create())->get(route('posts.reactions.index', $post));

        $response->assertOk();
        $response->assertSeeInOrder(['All 3', '❤️ 2', '😮 1']);
        $response->assertSee('data-people-type="love"', false);
        $response->assertSee('ana');
        $response->assertSee('bo');
        $response->assertSee('cy');
    }

    public function test_reaction_list_of_a_post_that_is_not_live_yet_is_not_available(): void
    {
        $post = $this->createPost(User::factory()->create(), 'scheduled', now()->addDay());

        $this->actingAs(User::factory()->create())
            ->get(route('posts.reactions.index', $post))
            ->assertNotFound();
    }

    private function createPost(User $author, string $state = 'published', ?\DateTimeInterface $publishAt = null): Post
    {
        return Post::query()->create([
            'descriptions' => 'A post to react to',
            'category_id' => Category::query()->firstOrCreate(['category_type' => 'Technology'])->category_id,
            'post_type' => 'Discussion',
            'state' => $state,
            'date_time' => $publishAt ?? now()->subHour(),
            'user_id' => $author->user_id,
        ]);
    }
}
