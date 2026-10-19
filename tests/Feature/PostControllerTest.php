<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_create_a_post_using_the_first_available_user(): void
    {
        $user = User::factory()->create();
        $category = Category::query()->create([
            'category_type' => 'Technology',
        ]);

        $response = $this->post(route('posts.store'), [
            'content' => 'A guest post',
            'category_id' => $category->category_id,
            'post_type' => 'Discussion',
        ]);

        $response->assertRedirect(route('posts.index'));
        $this->assertDatabaseHas('posts', [
            'descriptions' => 'A guest post',
            'user_id' => $user->user_id,
        ]);
    }

    public function test_post_creation_reports_when_no_user_account_exists(): void
    {
        $category = Category::query()->create([
            'category_type' => 'Technology',
        ]);

        $response = $this->from(route('posts.index'))->post(route('posts.store'), [
            'content' => 'A guest post',
            'category_id' => $category->category_id,
            'post_type' => 'Discussion',
        ]);

        $response->assertRedirect(route('posts.index'));
        $response->assertSessionHasErrors('content');
        $this->assertSame(0, Post::query()->count());
    }
}
