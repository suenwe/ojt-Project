<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduledPostControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_member_schedules_a_post_that_appears_in_the_feed_only_when_its_time_comes(): void
    {
        $this->freezeTime();
        $member = User::factory()->create();
        $category = Category::query()->firstOrCreate(['category_type' => 'Technology']);

        $this->actingAs($member)
            ->post(route('schedule-post.store'), [
                'content' => 'Posting this tomorrow',
                'category_id' => $category->category_id,
                'post_type' => 'Discussion',
                'scheduled_at' => now()->addDay()->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect(route('posts.schedule'))
            ->assertSessionHas('status', 'Your post is scheduled.');

        $this->assertSame('scheduled', Post::query()->sole()->state);

        $this->get(route('posts.schedule'))->assertSee('Posting this tomorrow');
        $this->get(route('home'))->assertViewHas('posts', fn ($posts): bool => $posts->isEmpty());

        $this->travel(2)->days();

        $this->get(route('home'))->assertViewHas('posts', fn ($posts): bool => $posts->pluck('descriptions')->all() === ['Posting this tomorrow']);
    }

    public function test_scheduling_in_the_past_is_rejected(): void
    {
        $category = Category::query()->firstOrCreate(['category_type' => 'Technology']);

        $this->actingAs(User::factory()->create())
            ->post(route('schedule-post.store'), [
                'content' => 'Too late',
                'category_id' => $category->category_id,
                'post_type' => 'Discussion',
                'scheduled_at' => now()->subHour()->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasErrors(['scheduled_at' => 'Please choose a future date and time.']);

        $this->assertSame(0, Post::query()->count());
    }

    public function test_only_the_author_can_cancel_a_scheduled_post(): void
    {
        $author = User::factory()->create();
        $post = Post::query()->create([
            'descriptions' => 'Upcoming post',
            'state' => 'scheduled',
            'date_time' => now()->addDay(),
            'user_id' => $author->user_id,
        ]);

        $this->actingAs(User::factory()->create())
            ->delete(route('schedule-post.destroy', $post))
            ->assertForbidden();

        $this->assertModelExists($post);

        $this->actingAs($author)
            ->delete(route('schedule-post.destroy', $post))
            ->assertRedirect(route('posts.schedule'));

        $this->assertModelMissing($post);
    }

    public function test_author_moves_a_scheduled_post_to_a_new_time(): void
    {
        $this->freezeTime();
        $author = User::factory()->create();
        $category = Category::query()->firstOrCreate(['category_type' => 'Technology']);
        $post = Post::query()->create([
            'descriptions' => 'Planned post',
            'category_id' => $category->category_id,
            'post_type' => 'Discussion',
            'state' => 'scheduled',
            'date_time' => now()->addDay(),
            'user_id' => $author->user_id,
        ]);
        $newTime = now()->addDays(3)->setTime(9, 30);

        $this->actingAs($author)
            ->get(route('posts.edit', $post))
            ->assertOk()
            ->assertSee('Edit Scheduled Post')
            ->assertSee('value="'.$post->date_time->format('Y-m-d\TH:i').'"', false);

        $this->put(route('posts.update', $post), [
            'content' => 'Planned post, moved',
            'category_id' => $category->category_id,
            'post_type' => 'Discussion',
            'scheduled_at' => $newTime->format('Y-m-d\TH:i'),
        ])
            ->assertRedirect(route('posts.schedule'))
            ->assertSessionHas('status', 'Your scheduled post was updated.');

        $post->refresh();
        $this->assertSame('scheduled', $post->state);
        $this->assertSame($newTime->format('Y-m-d H:i'), $post->date_time->format('Y-m-d H:i'));
        $this->assertSame('Planned post, moved', $post->descriptions);
    }

    public function test_a_scheduled_post_cannot_be_moved_into_the_past(): void
    {
        $author = User::factory()->create();
        $category = Category::query()->firstOrCreate(['category_type' => 'Technology']);
        $post = Post::query()->create([
            'descriptions' => 'Planned post',
            'category_id' => $category->category_id,
            'post_type' => 'Discussion',
            'state' => 'scheduled',
            'date_time' => now()->addDay(),
            'user_id' => $author->user_id,
        ]);

        $this->actingAs($author)
            ->put(route('posts.update', $post), [
                'content' => 'Planned post',
                'category_id' => $category->category_id,
                'post_type' => 'Discussion',
                'scheduled_at' => now()->subHour()->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasErrors(['scheduled_at' => 'Please choose a future date and time.']);

        $this->assertTrue($post->fresh()->date_time->isFuture());
    }

    public function test_editing_a_published_post_does_not_ask_for_a_time(): void
    {
        $author = User::factory()->create();
        $category = Category::query()->firstOrCreate(['category_type' => 'Technology']);
        $post = Post::query()->create([
            'descriptions' => 'Already live',
            'category_id' => $category->category_id,
            'post_type' => 'Discussion',
            'state' => 'published',
            'date_time' => now()->subDay(),
            'user_id' => $author->user_id,
        ]);
        $originalTime = $post->date_time->format('Y-m-d H:i');

        $this->actingAs($author)
            ->get(route('posts.edit', $post))
            ->assertDontSee('Schedule date and time');

        $this->put(route('posts.update', $post), [
            'content' => 'Still live',
            'category_id' => $category->category_id,
            'post_type' => 'Discussion',
        ])->assertRedirect(route('home'));

        $this->assertSame($originalTime, $post->fresh()->date_time->format('Y-m-d H:i'));
    }

    public function test_calendar_lists_only_the_members_own_upcoming_posts_and_is_the_only_schedule_button(): void
    {
        $member = User::factory()->create();
        $upcoming = $this->createScheduledPost($member, 'Mine, coming up', now()->addDays(2)->setTime(15, 30));
        $this->createScheduledPost($member, 'Mine, already live', now()->subHour());
        $this->createScheduledPost(User::factory()->create(), 'Someone else plans this', now()->addDay());

        $response = $this->actingAs($member)->get(route('home'));

        $response->assertOk();
        $response->assertViewHas('scheduleCalendar', fn (array $calendar): bool => $calendar['posts'] === [[
            'id' => $upcoming->post_id,
            'date' => $upcoming->date_time->format('Y-m-d'),
            'time' => '15:30',
            'publishAt' => $upcoming->date_time->toIso8601String(),
            'text' => 'Mine, coming up',
            'hashtag' => null,
            'categoryId' => $upcoming->category_id,
            'postType' => 'Discussion',
            'mediaCount' => 0,
            'media' => [],
            'updateUrl' => route('posts.update', $upcoming),
            'deleteUrl' => route('schedule-post.destroy', $upcoming),
        ]]);
        $response->assertDontSee('Someone else plans this');
        $this->assertSame(1, substr_count($response->getContent(), 'href="'.route('posts.schedule').'"'));
    }

    public function test_scheduling_from_the_calendar_returns_to_the_same_page_with_the_calendar_open(): void
    {
        $member = User::factory()->create();
        $category = Category::query()->firstOrCreate(['category_type' => 'Technology']);

        $response = $this->actingAs($member)
            ->from(route('users.show', $member))
            ->post(route('schedule-post.store'), [
                'content' => 'From the calendar',
                'category_id' => $category->category_id,
                'post_type' => 'Discussion',
                'scheduled_at' => now()->addDay()->format('Y-m-d\TH:i'),
                'from_calendar' => '1',
            ]);

        $response->assertRedirect(route('users.show', $member));
        $response->assertSessionHas('status', 'Your post is scheduled.');
        $response->assertSessionHas('showScheduleCalendar', true);
        $this->assertSame('From the calendar', Post::query()->sole()->descriptions);
    }

    public function test_editing_and_cancelling_from_the_calendar_return_to_the_same_page(): void
    {
        $member = User::factory()->create();
        $post = $this->createScheduledPost($member, 'Planned', now()->addDay());
        $newTime = now()->addDays(3)->setTime(8, 0);

        $this->actingAs($member)
            ->from(route('home'))
            ->put(route('posts.update', $post), [
                'content' => 'Planned, edited',
                'category_id' => $post->category_id,
                'post_type' => 'Discussion',
                'scheduled_at' => $newTime->format('Y-m-d\TH:i'),
                'from_calendar' => '1',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('showScheduleCalendar', true);

        $this->assertSame($newTime->format('Y-m-d H:i'), $post->fresh()->date_time->format('Y-m-d H:i'));

        $this->from(route('home'))
            ->delete(route('schedule-post.destroy', $post), ['from_calendar' => '1'])
            ->assertRedirect(route('home'))
            ->assertSessionHas('status', 'Scheduled post cancelled.');

        $this->assertModelMissing($post);
    }

    public function test_failed_calendar_post_reopens_the_schedule_form_and_not_the_home_composer(): void
    {
        $member = User::factory()->create();

        $response = $this->actingAs($member)
            ->from(route('home'))
            ->followingRedirects()
            ->post(route('schedule-post.store'), [
                'content' => 'Too late',
                'category_id' => Category::query()->firstOrCreate(['category_type' => 'Technology'])->category_id,
                'post_type' => 'Discussion',
                'scheduled_at' => now()->subHour()->format('Y-m-d\TH:i'),
                'from_calendar' => '1',
            ]);

        $response->assertOk();
        $response->assertSee('data-open-on-load="true"', false);
        $response->assertSee('Please choose a future date and time.');
        $response->assertSee('<div class="composer-inline" data-composer-form  hidden >', false);
        $this->assertSame(0, Post::query()->count());
    }

    public function test_members_only_see_their_own_scheduled_posts(): void
    {
        Post::query()->create([
            'descriptions' => 'Someone else plans this',
            'state' => 'scheduled',
            'date_time' => now()->addDay(),
            'user_id' => User::factory()->create()->user_id,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('posts.schedule'))
            ->assertOk()
            ->assertDontSee('Someone else plans this')
            ->assertSee('You have no upcoming scheduled posts.');
    }

    private function createScheduledPost(User $author, string $text, \DateTimeInterface $publishAt): Post
    {
        return Post::query()->create([
            'descriptions' => $text,
            'category_id' => Category::query()->firstOrCreate(['category_type' => 'Technology'])->category_id,
            'post_type' => 'Discussion',
            'state' => 'scheduled',
            'date_time' => $publishAt,
            'user_id' => $author->user_id,
        ]);
    }
}
