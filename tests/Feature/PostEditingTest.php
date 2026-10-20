<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostEditingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_author_edits_their_post_and_new_files_replace_the_old_ones(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('post-media/old.png', 'old-bytes');
        $author = User::factory()->create();
        $post = $this->createPost($author, 'First version');
        $post->media()->create([
            'media_type' => 'photo',
            'mime_type' => 'image/png',
            'media_path' => 'post-media/old.png',
            'file_size' => 9,
        ]);
        $category = Category::query()->firstOrCreate(['category_type' => 'Education']);

        $this->actingAs($author)
            ->get(route('posts.edit', $post))
            ->assertOk()
            ->assertSee('Edit Post')
            ->assertSee('First version');

        $this->put(route('posts.update', $post), [
            'content' => 'Second version',
            'category_id' => $category->category_id,
            'post_type' => 'Questions',
            'hashtag' => '#edited',
            'media' => [UploadedFile::fake()->image('new.png', 20, 20)],
        ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('status', 'Your post was updated.');

        $post->refresh();
        $this->assertSame('Second version', $post->descriptions);
        $this->assertSame($category->category_id, $post->category_id);
        $this->assertSame('Questions', $post->post_type);
        $this->assertSame('#edited', $post->hashtag);
        Storage::disk('public')->assertMissing('post-media/old.png');
        Storage::disk('public')->assertExists($post->media()->sole()->media_path);
    }

    public function test_edit_form_previews_the_posts_current_photos_in_the_slider(): void
    {
        $author = User::factory()->create();
        $post = $this->createPost($author, 'With two photos');
        $firstPhoto = $post->media()->create(['media_type' => 'photo', 'mime_type' => 'image/png', 'media_path' => 'post-media/one.png', 'file_size' => 9]);
        $secondPhoto = $post->media()->create(['media_type' => 'photo', 'mime_type' => 'image/jpeg', 'media_path' => 'post-media/two.jpg', 'file_size' => 9]);

        $response = $this->actingAs($author)->get(route('posts.edit', $post));

        $response->assertOk();
        $response->assertSee('data-media-preview="post-media-preview"', false);
        $response->assertSee("data-current-media='".json_encode([
            ['url' => route('media.show', $firstPhoto), 'type' => 'image/png'],
            ['url' => route('media.show', $secondPhoto), 'type' => 'image/jpeg'],
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)."'", false);
    }

    public function test_editing_without_new_files_keeps_the_current_ones(): void
    {
        $author = User::factory()->create();
        $post = $this->createPost($author, 'Keep my photo');
        $post->media()->create([
            'media_type' => 'photo',
            'mime_type' => 'image/png',
            'media_path' => 'post-media/keep.png',
            'file_size' => 9,
        ]);

        $this->actingAs($author)->put(route('posts.update', $post), [
            'content' => 'Text only change',
            'category_id' => $post->category_id,
            'post_type' => 'Discussion',
        ])->assertSessionHasNoErrors();

        $this->assertSame('post-media/keep.png', $post->media()->sole()->media_path);
    }

    public function test_author_deletes_their_post_and_its_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('post-media/gone.png', 'bytes');
        $author = User::factory()->create();
        $post = $this->createPost($author, 'Delete me');
        $post->media()->create([
            'media_type' => 'photo',
            'mime_type' => 'image/png',
            'media_path' => 'post-media/gone.png',
            'file_size' => 5,
        ]);

        $this->actingAs($author)
            ->from(route('home'))
            ->delete(route('posts.destroy', $post))
            ->assertRedirect(route('home'))
            ->assertSessionHas('status', 'Your post was deleted.');

        $this->assertModelMissing($post);
        Storage::disk('public')->assertMissing('post-media/gone.png');
    }

    public function test_other_members_cannot_edit_or_delete_someone_elses_post(): void
    {
        $post = $this->createPost(User::factory()->create(), 'Not yours');

        $this->actingAs(User::factory()->create());

        $this->get(route('posts.edit', $post))->assertForbidden();
        $this->put(route('posts.update', $post), [
            'content' => 'Hijacked',
            'category_id' => $post->category_id,
            'post_type' => 'Discussion',
        ])->assertForbidden();
        $this->delete(route('posts.destroy', $post))->assertForbidden();

        $this->assertSame('Not yours', $post->fresh()->descriptions);
    }

    public function test_feed_shows_edit_and_delete_only_on_your_own_posts(): void
    {
        $member = User::factory()->create();
        $mine = $this->createPost($member, 'My post');
        $theirs = $this->createPost(User::factory()->create(), 'Their post');

        $this->actingAs($member)
            ->get(route('home'))
            ->assertSee(route('posts.edit', $mine), false)
            ->assertDontSee(route('posts.edit', $theirs), false)
            ->assertSee('Report Post');
    }

    private function createPost(User $author, string $text): Post
    {
        return Post::query()->create([
            'descriptions' => $text,
            'category_id' => Category::query()->firstOrCreate(['category_type' => 'Technology'])->category_id,
            'post_type' => 'Discussion',
            'state' => 'published',
            'date_time' => now()->subHour(),
            'user_id' => $author->user_id,
        ]);
    }
}
