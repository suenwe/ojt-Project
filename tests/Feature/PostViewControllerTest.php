<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostViewControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_sign_in(): void
    {
        $post = $this->createPost(User::factory()->create());

        $this->post(route('posts.views.store', $post))->assertRedirect(route('login'));

        $this->assertSame(0, PostView::query()->count());
    }

    public function test_each_member_counts_once_per_post(): void
    {
        $post = $this->createPost(User::factory()->create());
        $viewer = User::factory()->create();

        $this->actingAs($viewer)->postJson(route('posts.views.store', $post))->assertOk()->assertExactJson(['viewCount' => 1]);
        $this->actingAs($viewer)->postJson(route('posts.views.store', $post))->assertExactJson(['viewCount' => 1]);
        $this->actingAs(User::factory()->create())->postJson(route('posts.views.store', $post))->assertExactJson(['viewCount' => 2]);

        $this->assertSame(2, PostView::query()->where('post_id', $post->post_id)->count());
    }

    public function test_authors_do_not_add_views_to_their_own_posts(): void
    {
        $author = User::factory()->create();
        $post = $this->createPost($author);

        $this->actingAs($author)->postJson(route('posts.views.store', $post))->assertExactJson(['viewCount' => 0]);

        $this->assertSame(0, PostView::query()->count());
    }

    public function test_posts_that_are_not_live_yet_cannot_be_viewed(): void
    {
        $post = $this->createPost(User::factory()->create(), 'scheduled', now()->addDay());

        $this->actingAs(User::factory()->create())
            ->postJson(route('posts.views.store', $post))
            ->assertNotFound();

        $this->assertSame(0, PostView::query()->count());
    }

    public function test_own_posts_are_marked_so_the_page_never_sends_their_views(): void
    {
        $this->withoutVite();
        $author = User::factory()->create();
        $post = $this->createPost($author);

        $this->actingAs($author)
            ->get(route('home'))
            ->assertSee('data-view-url="'.route('posts.views.store', $post).'"  data-own-post', false);
    }

    public function test_authors_see_who_viewed_their_post(): void
    {
        $author = User::factory()->create();
        $post = $this->createPost($author);
        PostView::query()->create(['post_id' => $post->post_id, 'user_id' => User::factory()->create(['username' => 'curious-reader'])->user_id]);

        $this->actingAs($author)
            ->get(route('posts.views.index', $post))
            ->assertOk()
            ->assertSee('curious-reader');
    }

    public function test_other_members_cannot_see_who_viewed_a_post(): void
    {
        $post = $this->createPost(User::factory()->create());
        PostView::query()->create(['post_id' => $post->post_id, 'user_id' => User::factory()->create(['username' => 'curious-reader'])->user_id]);

        $this->actingAs(User::factory()->create())
            ->get(route('posts.views.index', $post))
            ->assertForbidden();
    }

    public function test_only_the_author_gets_a_clickable_view_count(): void
    {
        $this->withoutVite();
        $author = User::factory()->create();
        $post = $this->createPost($author);

        $this->actingAs($author)->get(route('home'))->assertSee('data-people-url="'.route('posts.views.index', $post).'"', false);
        $this->actingAs(User::factory()->create())->get(route('home'))->assertDontSee('data-people-url="'.route('posts.views.index', $post).'"', false);
    }

    private function createPost(User $author, string $state = 'published', ?\DateTimeInterface $publishAt = null): Post
    {
        return Post::query()->create([
            'descriptions' => 'A post to view',
            'category_id' => Category::query()->firstOrCreate(['category_type' => 'Technology'])->category_id,
            'post_type' => 'Discussion',
            'state' => $state,
            'date_time' => $publishAt ?? now()->subHour(),
            'user_id' => $author->user_id,
        ]);
    }
}
