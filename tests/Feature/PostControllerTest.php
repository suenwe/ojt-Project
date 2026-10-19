<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\PostMedia;
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

    public function test_signed_in_user_can_create_a_post(): void
    {
        $user = User::factory()->create();
        $category = Category::query()->create([
            'category_type' => 'Technology',
        ]);

        $response = $this->actingAs($user)->post(route('posts.store'), [
            'content' => 'A guest post',
            'category_id' => $category->category_id,
            'post_type' => 'Discussion',
        ]);

        $response->assertRedirect(route('posts.create'));
        $this->assertDatabaseHas('posts', [
            'descriptions' => 'A guest post',
            'user_id' => $user->user_id,
        ]);
    }

    public function test_signed_in_user_can_publish_a_post_with_media(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $category = Category::query()->create([
            'category_type' => 'Technology',
        ]);

        $response = $this->actingAs($user)->post(route('posts.store'), [
            'content' => 'A post with an image',
            'category_id' => $category->category_id,
            'post_type' => 'Discussion',
            'media' => [
                UploadedFile::fake()->image('community.png'),
            ],
        ]);

        $response->assertRedirect(route('posts.create'));
        $this->assertDatabaseHas('posts', [
            'descriptions' => 'A post with an image',
            'user_id' => $user->user_id,
        ]);

        $media = PostMedia::query()->firstOrFail();
        $this->assertNotNull($media->media_path);
        $this->assertNull($media->media_data);
        $this->assertTrue(Storage::disk('public')->exists($media->media_path));
    }

    public function test_guest_is_redirected_to_sign_in_from_create_post_page(): void
    {
        $this->get(route('posts.create'))
            ->assertRedirect(route('login'));
    }

    public function test_create_post_page_shows_the_signed_in_user(): void
    {
        $user = User::factory()->create(['username' => 'current-member']);

        $this->actingAs($user)
            ->get(route('posts.create'))
            ->assertOk()
            ->assertSee('current-member')
            ->assertDontSee('Alex Morgan');
    }
}
