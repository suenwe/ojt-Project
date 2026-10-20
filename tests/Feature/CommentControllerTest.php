<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Keyword;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentControllerTest extends TestCase
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

        $this->get(route('posts.comments.index', $post))->assertRedirect(route('login'));
        $this->post(route('posts.comments.store', $post), ['content' => 'Hi'])->assertRedirect(route('login'));

        $this->assertSame(0, Comment::query()->count());
    }

    public function test_member_comments_and_gets_the_refreshed_list_and_count(): void
    {
        $post = $this->createPost(User::factory()->create());
        $member = User::factory()->create(['username' => 'commenter']);

        $response = $this->actingAs($member)->postJson(route('posts.comments.store', $post), ['content' => 'Great post!']);

        $response->assertOk();
        $response->assertJsonPath('commentCount', 1);
        $this->assertStringContainsString('Great post!', $response->json('html'));
        $this->assertStringContainsString('commenter', $response->json('html'));
        $this->assertDatabaseHas('comments', ['post_id' => $post->post_id, 'user_id' => $member->user_id, 'parent_id' => null, 'content' => 'Great post!']);
    }

    public function test_a_reply_is_saved_under_the_exact_comment_it_answers(): void
    {
        $post = $this->createPost(User::factory()->create());
        $topComment = $this->createComment($post, User::factory()->create(), 'Top comment');
        $reply = $this->createComment($post, User::factory()->create(), 'First reply', $topComment);

        $this->actingAs(User::factory()->create())
            ->postJson(route('posts.comments.store', $post), ['content' => 'Answering the reply', 'parent_id' => $reply->comment_id])
            ->assertOk()
            ->assertJsonPath('commentCount', 3);

        $this->assertSame($reply->comment_id, Comment::query()->where('content', 'Answering the reply')->sole()->parent_id);
    }

    public function test_replies_nest_in_boxes_three_steps_deep_and_deeper_ones_mention_the_name(): void
    {
        $post = $this->createPost(User::factory()->create());
        $parent = $this->createComment($post, User::factory()->create(), 'Level 0');
        foreach ([1, 2, 3, 4] as $level) {
            $parent = $this->createComment($post, User::factory()->create(), 'Level '.$level, $parent);
        }

        $html = $this->actingAs(User::factory()->create())->get(route('posts.comments.index', $post))->assertOk()->getContent();

        $this->assertSame(3, substr_count($html, 'class="comment-replies"'));
        $this->assertSame(2, substr_count($html, 'data-reply-mention'));
        $this->assertMatchesRegularExpression('/Level 3.*Level 4/s', $html);
    }

    public function test_replies_to_a_comment_on_another_post_are_rejected(): void
    {
        $post = $this->createPost(User::factory()->create());
        $otherPostComment = $this->createComment($this->createPost(User::factory()->create()), User::factory()->create(), 'Elsewhere');

        $this->actingAs(User::factory()->create())
            ->postJson(route('posts.comments.store', $post), ['content' => 'Reply', 'parent_id' => $otherPostComment->comment_id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['parent_id' => 'The comment you are replying to no longer exists.']);

        $this->assertSame(1, Comment::query()->count());
    }

    public function test_empty_comments_are_rejected(): void
    {
        $post = $this->createPost(User::factory()->create());

        $this->actingAs(User::factory()->create())
            ->postJson(route('posts.comments.store', $post), ['content' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['content' => 'Write something before sending.']);

        $this->assertSame(0, Comment::query()->count());
    }

    public function test_restricted_keywords_block_a_comment(): void
    {
        Keyword::query()->create(['keyword_text' => 'spamword']);
        $post = $this->createPost(User::factory()->create());

        $this->actingAs(User::factory()->create())
            ->postJson(route('posts.comments.store', $post), ['content' => 'Buy SpamWord now'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['content' => 'Your comment contains the restricted keyword "spamword" and cannot be posted.']);

        $this->assertSame(0, Comment::query()->count());
    }

    public function test_comment_list_shows_replies_under_their_comment_and_escapes_text(): void
    {
        $post = $this->createPost(User::factory()->create());
        $topComment = $this->createComment($post, User::factory()->create(), 'First comment');
        $this->createComment($post, User::factory()->create(), 'A reply', $topComment);
        $this->createComment($post, User::factory()->create(), '<script>alert(1)</script>');

        $response = $this->actingAs(User::factory()->create())->get(route('posts.comments.index', $post));

        $response->assertOk();
        $response->assertSeeInOrder(['First comment', 'comment-replies', 'A reply', '&lt;script&gt;alert(1)&lt;/script&gt;'], false);
        $response->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_comments_on_posts_that_are_not_live_yet_are_not_available(): void
    {
        $post = $this->createPost(User::factory()->create(), 'scheduled', now()->addDay());
        $member = User::factory()->create();

        $this->actingAs($member)->get(route('posts.comments.index', $post))->assertNotFound();
        $this->postJson(route('posts.comments.store', $post), ['content' => 'Too early'])->assertNotFound();

        $this->assertSame(0, Comment::query()->count());
    }

    public function test_members_delete_their_own_comment_and_its_replies(): void
    {
        $post = $this->createPost(User::factory()->create());
        $commenter = User::factory()->create();
        $comment = $this->createComment($post, $commenter, 'Mine');
        $this->createComment($post, User::factory()->create(), 'Reply to mine', $comment);

        $this->actingAs($commenter)
            ->deleteJson(route('comments.destroy', $comment))
            ->assertOk()
            ->assertJsonPath('commentCount', 0);

        $this->assertSame(0, Comment::query()->count());
    }

    public function test_post_authors_can_delete_any_comment_on_their_post(): void
    {
        $author = User::factory()->create();
        $comment = $this->createComment($this->createPost($author), User::factory()->create(), 'Off topic');

        $this->actingAs($author)->deleteJson(route('comments.destroy', $comment))->assertOk();

        $this->assertModelMissing($comment);
    }

    public function test_members_cannot_delete_other_peoples_comments_on_other_peoples_posts(): void
    {
        $comment = $this->createComment($this->createPost(User::factory()->create()), User::factory()->create(), 'Not yours');

        $this->actingAs(User::factory()->create())
            ->deleteJson(route('comments.destroy', $comment))
            ->assertForbidden();

        $this->assertModelExists($comment);
    }

    public function test_members_edit_their_own_comment_and_it_shows_as_edited(): void
    {
        $post = $this->createPost(User::factory()->create());
        $commenter = User::factory()->create();
        $comment = $this->createComment($post, $commenter, 'Frist!');
        $this->travel(5)->minutes();

        $response = $this->actingAs($commenter)->putJson(route('comments.update', $comment), ['content' => 'First!']);

        $response->assertOk();
        $this->assertSame('First!', $comment->fresh()->content);
        $this->assertStringContainsString('First!', $response->json('html'));
        $this->assertStringContainsString('>Edited</span>', $response->json('html'));
    }

    public function test_only_the_writer_can_edit_a_comment_not_even_the_post_author(): void
    {
        $author = User::factory()->create();
        $comment = $this->createComment($this->createPost($author), User::factory()->create(), 'Original');

        $this->actingAs($author)
            ->putJson(route('comments.update', $comment), ['content' => 'Changed by someone else'])
            ->assertForbidden();

        $this->assertSame('Original', $comment->fresh()->content);
    }

    public function test_edits_are_checked_for_restricted_keywords(): void
    {
        Keyword::query()->create(['keyword_text' => 'spamword']);
        $commenter = User::factory()->create();
        $comment = $this->createComment($this->createPost(User::factory()->create()), $commenter, 'Clean');

        $this->actingAs($commenter)
            ->putJson(route('comments.update', $comment), ['content' => 'now with spamword'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['content' => 'Your comment contains the restricted keyword "spamword" and cannot be posted.']);

        $this->assertSame('Clean', $comment->fresh()->content);
    }

    public function test_edit_link_only_shows_on_your_own_comments(): void
    {
        $post = $this->createPost(User::factory()->create());
        $viewer = User::factory()->create();
        $ownComment = $this->createComment($post, $viewer, 'Mine');
        $otherComment = $this->createComment($post, User::factory()->create(), 'Theirs');

        $response = $this->actingAs($viewer)->get(route('posts.comments.index', $post));

        $response->assertSee('data-comment-edit="'.$ownComment->comment_id.'"', false);
        $response->assertDontSee('data-comment-edit="'.$otherComment->comment_id.'"', false);
    }

    public function test_delete_button_only_shows_on_comments_you_may_delete(): void
    {
        $post = $this->createPost(User::factory()->create());
        $viewer = User::factory()->create();
        $ownComment = $this->createComment($post, $viewer, 'Mine');
        $otherComment = $this->createComment($post, User::factory()->create(), 'Theirs');

        $response = $this->actingAs($viewer)->get(route('posts.comments.index', $post));

        $response->assertSee('action="'.route('comments.destroy', $ownComment).'"', false);
        $response->assertDontSee('action="'.route('comments.destroy', $otherComment).'"', false);
    }

    private function createPost(User $author, string $state = 'published', ?\DateTimeInterface $publishAt = null): Post
    {
        return Post::query()->create([
            'descriptions' => 'A post to comment on',
            'category_id' => Category::query()->firstOrCreate(['category_type' => 'Technology'])->category_id,
            'post_type' => 'Discussion',
            'state' => $state,
            'date_time' => $publishAt ?? now()->subHour(),
            'user_id' => $author->user_id,
        ]);
    }

    private function createComment(Post $post, User $author, string $content, ?Comment $parent = null): Comment
    {
        return Comment::query()->create([
            'post_id' => $post->post_id,
            'user_id' => $author->user_id,
            'parent_id' => $parent?->comment_id,
            'content' => $content,
        ]);
    }
}
