<?php

namespace Tests\Feature;

use App\Models\ActivityNotification;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_reacting_notifies_the_posts_author_but_not_yourself(): void
    {
        $author = User::factory()->create();
        $post = $this->createPost($author);
        $fan = User::factory()->create();

        $this->actingAs($fan)->postJson(route('posts.reactions.store', $post), ['type' => 'love'])->assertOk();
        $this->actingAs($author)->postJson(route('posts.reactions.store', $post), ['type' => 'like'])->assertOk();

        $notification = ActivityNotification::query()->sole();
        $this->assertSame([$author->user_id, $fan->user_id, 'reaction', 'love'], [$notification->user_id, $notification->actor_id, $notification->type, $notification->reaction_type]);
        $this->assertSame('reacted ❤️ to your post', $notification->summary());
    }

    public function test_changing_a_reaction_updates_the_same_notification_and_removing_it_deletes_it(): void
    {
        $author = User::factory()->create();
        $post = $this->createPost($author);
        $fan = User::factory()->create();
        $this->actingAs($fan)->postJson(route('posts.reactions.store', $post), ['type' => 'like']);
        ActivityNotification::query()->update(['read_at' => now()]);

        $this->postJson(route('posts.reactions.store', $post), ['type' => 'haha']);

        $notification = ActivityNotification::query()->sole();
        $this->assertSame('haha', $notification->reaction_type);
        $this->assertNull($notification->read_at);

        $this->postJson(route('posts.reactions.store', $post), ['type' => 'haha']);

        $this->assertSame(0, ActivityNotification::query()->count());
    }

    public function test_commenting_notifies_the_posts_author_but_not_yourself(): void
    {
        $author = User::factory()->create();
        $post = $this->createPost($author);
        $commenter = User::factory()->create();

        $this->actingAs($commenter)->postJson(route('posts.comments.store', $post), ['content' => 'Nice!'])->assertOk();
        $this->actingAs($author)->postJson(route('posts.comments.store', $post), ['content' => 'Thanks!'])->assertOk();

        $notification = ActivityNotification::query()->sole();
        $this->assertSame([$author->user_id, $commenter->user_id, 'comment'], [$notification->user_id, $notification->actor_id, $notification->type]);
        $this->assertSame('Nice!', $notification->excerpt());
    }

    public function test_a_reply_notifies_the_comments_writer_and_the_posts_author(): void
    {
        $author = User::factory()->create();
        $post = $this->createPost($author);
        $commenter = User::factory()->create();
        $comment = $this->createComment($post, $commenter, 'First');
        $replier = User::factory()->create();

        $this->actingAs($replier)->postJson(route('posts.comments.store', $post), ['content' => 'Answer', 'parent_id' => $comment->comment_id])->assertOk();

        $this->assertEqualsCanonicalizing(
            [[$commenter->user_id, 'reply'], [$author->user_id, 'comment']],
            ActivityNotification::query()->get()->map(fn (ActivityNotification $notification): array => [$notification->user_id, $notification->type])->all(),
        );
    }

    public function test_replying_to_the_posts_author_sends_them_one_notification(): void
    {
        $author = User::factory()->create();
        $post = $this->createPost($author);
        $authorsComment = $this->createComment($post, $author, 'Ask me anything');

        $this->actingAs(User::factory()->create())
            ->postJson(route('posts.comments.store', $post), ['content' => 'Question', 'parent_id' => $authorsComment->comment_id]);

        $notification = ActivityNotification::query()->sole();
        $this->assertSame([$author->user_id, 'reply'], [$notification->user_id, $notification->type]);
    }

    public function test_deleting_a_comment_removes_its_notifications(): void
    {
        $post = $this->createPost(User::factory()->create());
        $commenter = User::factory()->create();
        $this->actingAs($commenter)->postJson(route('posts.comments.store', $post), ['content' => 'Oops']);
        $comment = Comment::query()->sole();

        $this->deleteJson(route('comments.destroy', $comment))->assertOk();

        $this->assertSame(0, ActivityNotification::query()->count());
    }

    public function test_guests_are_redirected_to_sign_in(): void
    {
        $this->get(route('notifications.index'))->assertRedirect(route('login'));
        $this->get(route('notifications.unread-count'))->assertRedirect(route('login'));
        $this->post(route('notifications.read'))->assertRedirect(route('login'));
    }

    public function test_list_shows_only_your_notifications_newest_first_with_links_to_the_post(): void
    {
        $member = User::factory()->create();
        $older = ActivityNotification::factory()->create(['user_id' => $member->user_id, 'actor_id' => User::factory()->create(['username' => 'older-fan'])->user_id, 'updated_at' => now()->subHour()]);
        ActivityNotification::factory()->create(['user_id' => $member->user_id, 'actor_id' => User::factory()->create(['username' => '<b>newer-fan</b>'])->user_id]);
        ActivityNotification::factory()->create(['actor_id' => User::factory()->create(['username' => 'someone-elses-fan'])->user_id]);

        $response = $this->actingAs($member)->get(route('notifications.index'));

        $response->assertOk();
        $response->assertSeeInOrder(['&lt;b&gt;newer-fan&lt;/b&gt;', 'older-fan'], false);
        $response->assertDontSee('someone-elses-fan');
        $response->assertSee('href="'.e($older->targetUrl()).'"', false);
        $this->assertStringContainsString('open_post='.$older->post_id, $older->targetUrl());
        $this->assertStringEndsWith('#post-'.$older->post_id, $older->targetUrl());
    }

    public function test_unread_count_and_marking_everything_read(): void
    {
        $member = User::factory()->create();
        ActivityNotification::factory()->count(2)->create(['user_id' => $member->user_id]);
        ActivityNotification::factory()->read()->create(['user_id' => $member->user_id]);
        ActivityNotification::factory()->create();

        $this->actingAs($member)->getJson(route('notifications.unread-count'))->assertExactJson(['unreadCount' => 2]);
        $this->postJson(route('notifications.read'))->assertExactJson(['unreadCount' => 0]);

        $this->assertSame(0, ActivityNotification::query()->where('user_id', $member->user_id)->unread()->count());
        $this->assertSame(1, ActivityNotification::query()->unread()->count());
    }

    public function test_the_bell_shows_the_unread_number(): void
    {
        $member = User::factory()->create();
        ActivityNotification::factory()->count(3)->create(['user_id' => $member->user_id]);

        $this->actingAs($member)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('data-notification-badge >3</span>', false);
    }

    public function test_schedule_reminder_arrives_five_minutes_before_and_only_once(): void
    {
        $this->freezeTime();
        $member = User::factory()->create();
        $soon = $this->createScheduledPost($member, now()->addMinutes(4));
        $this->createScheduledPost($member, now()->addMinutes(6));
        $this->createScheduledPost(User::factory()->create(), now()->addMinutes(3));

        $this->actingAs($member)->getJson(route('notifications.unread-count'))->assertExactJson(['unreadCount' => 1]);
        $this->getJson(route('notifications.unread-count'))->assertExactJson(['unreadCount' => 1]);

        $reminder = ActivityNotification::query()->sole();
        $this->assertSame([$member->user_id, 'schedule_reminder', $soon->post_id], [$reminder->user_id, $reminder->type, $reminder->post_id]);
    }

    public function test_loading_a_page_also_creates_due_reminders(): void
    {
        $member = User::factory()->create();
        $this->createScheduledPost($member, now()->addMinutes(2));

        $this->actingAs($member)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('data-notification-badge >1</span>', false);
    }

    public function test_reminder_offers_edit_and_delete_until_the_post_goes_live(): void
    {
        $this->travelTo(now()->setTime(15, 0));
        $member = User::factory()->create();
        $post = $this->createScheduledPost($member, now()->addMinutes(5));

        $response = $this->actingAs($member)->get(route('notifications.index'));

        $response->assertOk();
        $response->assertSee('Your scheduled post goes live at 3:05 PM');
        $response->assertSee('href="'.route('posts.edit', $post).'" data-reminder-edit="'.$post->post_id.'"', false);
        $response->assertSee('action="'.route('schedule-post.destroy', $post).'"', false);

        $this->travel(10)->minutes();

        $response = $this->get(route('notifications.index'));

        $response->assertSee('Your scheduled post went live at 3:05 PM');
        $response->assertDontSee('data-reminder-edit', false);
        $response->assertSee('open_post='.$post->post_id, false);
    }

    public function test_moving_a_scheduled_post_replaces_its_reminder(): void
    {
        $this->freezeTime();
        $member = User::factory()->create();
        $post = $this->createScheduledPost($member, now()->startOfMinute()->addMinutes(3));
        $this->actingAs($member)->getJson(route('notifications.unread-count'));
        $this->assertSame(1, ActivityNotification::query()->count());

        $this->put(route('posts.update', $post), [
            'content' => 'Same text',
            'category_id' => $post->category_id,
            'post_type' => 'Discussion',
            'scheduled_at' => $post->date_time->format('Y-m-d\TH:i'),
        ]);
        $this->assertSame(1, ActivityNotification::query()->count());

        $this->put(route('posts.update', $post), [
            'content' => 'Same text',
            'category_id' => $post->category_id,
            'post_type' => 'Discussion',
            'scheduled_at' => now()->addHours(2)->format('Y-m-d\TH:i'),
        ]);
        $this->assertSame(0, ActivityNotification::query()->count());
    }

    public function test_cancelling_a_scheduled_post_removes_its_reminder(): void
    {
        $member = User::factory()->create();
        $post = $this->createScheduledPost($member, now()->addMinutes(3));
        $this->actingAs($member)->getJson(route('notifications.unread-count'));

        $this->delete(route('schedule-post.destroy', $post), ['from_calendar' => '1'])->assertRedirect();

        $this->assertSame(0, ActivityNotification::query()->count());
    }

    private function createScheduledPost(User $author, \DateTimeInterface $publishAt): Post
    {
        return Post::query()->create([
            'descriptions' => 'Planned post',
            'category_id' => Category::query()->firstOrCreate(['category_type' => 'Technology'])->category_id,
            'post_type' => 'Discussion',
            'state' => 'scheduled',
            'date_time' => $publishAt,
            'user_id' => $author->user_id,
        ]);
    }

    private function createPost(User $author): Post
    {
        return Post::query()->create([
            'descriptions' => 'A post people react to',
            'category_id' => Category::query()->firstOrCreate(['category_type' => 'Technology'])->category_id,
            'post_type' => 'Discussion',
            'state' => 'published',
            'date_time' => now()->subHour(),
            'user_id' => $author->user_id,
        ]);
    }

    private function createComment(Post $post, User $author, string $content): Comment
    {
        return Comment::query()->create([
            'post_id' => $post->post_id,
            'user_id' => $author->user_id,
            'content' => $content,
        ]);
    }
}
