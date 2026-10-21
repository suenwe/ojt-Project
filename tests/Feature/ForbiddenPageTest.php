<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForbiddenPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_member_opening_the_admin_area_gets_a_friendly_page_with_a_link_home(): void
    {
        $member = User::factory()->create();

        $response = $this->actingAs($member)->get(route('admin.dashboard'));

        $response->assertForbidden();
        $response->assertSee("You can't open this page", false);
        $response->assertSee('<a class="btn" href="'.route('home').'">Back to home</a>', false);
    }

    public function test_admin_denied_a_page_is_pointed_to_the_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $post = Post::query()->create([
            'descriptions' => 'Someone else wrote this',
            'category_id' => Category::query()->firstOrCreate(['category_type' => 'Technology'])->category_id,
            'post_type' => 'Discussion',
            'state' => 'published',
            'date_time' => now()->subHour(),
            'user_id' => User::factory()->create()->user_id,
        ]);

        $response = $this->actingAs($admin)->get(route('posts.edit', $post));

        $response->assertForbidden();
        $response->assertSee('<a class="btn" href="'.route('admin.dashboard').'">Go to admin dashboard</a>', false);
    }

    public function test_suspended_member_sees_the_reason_and_can_only_sign_out(): void
    {
        $member = User::factory()->create(['banned_at' => now()]);

        $response = $this->actingAs($member)->get(route('home'));

        $response->assertForbidden();
        $response->assertSee('This account has been suspended.');
        $response->assertSee('action="'.route('logout').'"', false);
        $response->assertDontSee('Back to home');
    }

    public function test_member_banned_while_signed_in_can_still_sign_out(): void
    {
        $member = User::factory()->create();
        $this->actingAs($member)->get(route('home'))->assertOk();

        $member->forceFill(['banned_at' => now(), 'ban_reason' => 'Confirmed report'])->save();

        $this->get(route('home'))->assertForbidden();
        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->assertGuest();
        $this->get(route('home'))->assertRedirect(route('login'));
    }
}
