<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

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
        Storage::disk('public')->assertExists($post->media->sole()->media_path);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Hello Knowvia')
            ->assertSee('skywalker')
            ->assertSee(route('media.show', $post->media->sole()), false);

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

    public function test_feed_shows_an_empty_state_when_there_are_no_posts(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('home'))
            ->assertOk()
            ->assertSee('No posts yet');
    }
}
