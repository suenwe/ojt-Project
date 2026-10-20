<?php

namespace Tests\Feature;
use App\Models\Category;
use App\Models\PostMedia;
use App\Models\Keyword;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_sign_in_before_creating_a_post(): void
    {
        $this->post(route('posts.store'), [
            'content' => 'A guest post',
            'category_id' => 1,
            'post_type' => 'Discussion',
        ])->assertRedirect(route('login'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guests_are_redirected_to_sign_in_and_cannot_post(): void
    {
        User::factory()->create();
        $category = Category::query()->firstOrCreate(['category_type' => 'Technology']);

        $this->get(route('posts.create'))->assertRedirect(route('login'));

        $this->post(route('posts.store'), [
            'content' => 'A guest post',
            'category_id' => $category->category_id,
            'post_type' => 'Discussion',
        ])->assertRedirect(route('login'));

        $this->assertSame(0, Post::query()->count());
    }

    public function test_member_publishes_a_post_with_a_photo_and_sees_it_in_the_feed(): void
    {
        Storage::fake('public');
        $member = User::factory()->create(['username' => 'skywalker']);
        $category = Category::query()->firstOrCreate(['category_type' => 'Technology']);

        $this->actingAs($member)
            ->post(route('posts.store'), [
                'content' => 'Hello Knowvia',
                'category_id' => $category->category_id,
                'post_type' => 'Questions',
                'hashtag' => '#laravel',
                'media' => [UploadedFile::fake()->image('photo.png', 40, 40)],
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('status', 'Your post was published.');

        $post = Post::query()->with('media')->sole();
        $this->assertSame($member->user_id, $post->user_id);
        $this->assertSame('published', $post->state);
        $this->assertSame('Questions', $post->post_type);
        $this->assertTrue(Storage::disk('public')->exists($post->media->sole()->media_path));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Hello Knowvia')
            ->assertSee('skywalker')
            ->assertSee(route('media.show', $post->media->sole()), false)
            ->assertSee("data-media-carousel='".json_encode([[
                'url' => route('media.show', $post->media->sole()),
                'type' => 'image/png',
                'alt' => 'Photo shared by skywalker',
            ]], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)."'", false);

        $this->get(route('media.show', $post->media->sole()))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }

    public function test_post_requires_an_existing_category_and_a_known_post_type(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('posts.store'), [
                'content' => 'Missing details',
                'category_id' => 999,
                'post_type' => 'Gossip',
            ])
            ->assertSessionHasErrors(['category_id', 'post_type']);

        $this->assertSame(0, Post::query()->count());
    }

    public function test_restricted_keyword_blocks_post_and_returns_a_specific_notice(): void
    {
        $author = User::factory()->create();
        $category = Category::query()->firstOrCreate(['category_type' => 'Technology']);
        Keyword::query()->create(['keyword_text' => 'restricted phrase']);

        $this->actingAs($author)
            ->post(route('posts.store'), [
                'content' => 'This RESTRICTED PHRASE should not be published.',
                'category_id' => $category->category_id,
                'post_type' => 'Discussion',
            ])
            ->assertSessionHasErrors([
                'content' => 'Your post contains the restricted keyword "restricted phrase" and cannot be published.',
            ]);

        $this->assertSame(0, Post::query()->count());
    }

    public function test_sidebar_shows_real_popular_hashtags_from_recent_posts(): void
    {
        $author = User::factory()->create();
        foreach (['#laravel #php', '#Laravel', '#design'] as $hashtags) {
            Post::query()->create([
                'descriptions' => 'Tagged post',
                'hashtag' => $hashtags,
                'state' => 'published',
                'date_time' => now()->subDay(),
                'user_id' => $author->user_id,
            ]);
        }

        $this->assertSame(['#laravel' => 2, '#php' => 1, '#design' => 1], Post::popularHashtags());

        $this->actingAs($author)
            ->get(route('home'))
            ->assertSeeInOrder(['Popular hashtags', '#laravel', '2'])
            ->assertDontSee('#postgresql');
    }

    public function test_schedule_is_reached_from_the_round_calendar_button(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('home'))
            ->assertSee('class="schedule-fab', false)
            ->assertSee('aria-label="Schedule a post"', false)
            ->assertDontSee('>Schedule post</a>', false);
    }

    public function test_home_feed_has_a_closed_post_form_whose_photo_button_opens_the_file_picker(): void
    {
        Category::query()->firstOrCreate(['category_type' => 'Technology']);

        $response = $this->actingAs(User::factory()->create())->get(route('home'));

        $response->assertOk();
        $response->assertSee('action="'.route('posts.store').'"', false);
        $response->assertSee('<label class="icon-button" for="post-media"', false);
        $response->assertSee('<div class="composer-inline" data-composer-form  hidden >', false);
        $response->assertSee('>Technology</option>', false);
    }

    public function test_post_form_stays_open_on_the_home_feed_after_a_failed_post(): void
    {
        $member = User::factory()->create();

        $response = $this->actingAs($member)
            ->from(route('home'))
            ->followingRedirects()
            ->post(route('posts.store'), [
                'content' => 'Keep this draft',
                'category_id' => 999,
                'post_type' => 'Discussion',
            ]);

        $response->assertOk();
        $response->assertSee('<div class="composer-inline" data-composer-form >', false);
        $response->assertSee('Keep this draft');
    }

    public function test_feed_shows_an_empty_state_when_there_are_no_posts(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('home'))
            ->assertOk()
            ->assertSee('No posts yet');
    }
}
