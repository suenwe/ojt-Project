<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guests_are_redirected_to_sign_in(): void
    {
        $member = User::factory()->create();

        $this->get(route('users.show', $member))->assertRedirect(route('login'));
    }

    public function test_members_see_another_members_profile_and_posts_but_not_private_details(): void
    {
        $member = User::factory()->create([
            'username' => 'skywalker',
            'email' => 'luke@example.test',
            'phone_number' => '09123456789',
            'birthday' => '1998-04-12',
        ]);
        $this->createPost($member, 'Published thoughts', 'published', now()->subDay());
        $this->createPost($member, 'Planned for later', 'scheduled', now()->addDay());

        $this->actingAs(User::factory()->create())
            ->get(route('users.show', $member))
            ->assertOk()
            ->assertSee('skywalker')
            ->assertSee('Joined '.now()->format('F Y'))
            ->assertSee('1 post')
            ->assertSee('Published thoughts')
            ->assertDontSee('Planned for later')
            ->assertDontSee('luke@example.test')
            ->assertDontSee('09123456789')
            ->assertDontSee('1998')
            ->assertDontSee('Edit profile');
    }

    public function test_members_see_an_edit_button_on_their_own_profile(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->get(route('users.show', $member))
            ->assertOk()
            ->assertSee('Edit profile')
            ->assertSee('Your posts will show up here.');
    }

    public function test_suspended_members_profiles_are_hidden_from_other_members(): void
    {
        $banned = User::factory()->create(['banned_at' => now()]);

        $this->actingAs(User::factory()->create())
            ->get(route('users.show', $banned))
            ->assertNotFound();

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('users.show', $banned))
            ->assertOk()
            ->assertSee('This account is suspended.');
    }

    public function test_post_author_names_in_the_feed_link_to_their_profiles(): void
    {
        $author = User::factory()->create(['username' => 'skywalker']);
        $this->createPost($author, 'Linked post', 'published', now()->subHour());

        $this->actingAs(User::factory()->create())
            ->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('users.show', $author).'"', false);
    }

    public function test_unknown_members_return_not_found(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('users.show', 999))
            ->assertNotFound();
    }

    private function createPost(User $author, string $text, string $state, mixed $dateTime): Post
    {
        return Post::query()->create([
            'descriptions' => $text,
            'state' => $state,
            'date_time' => $dateTime,
            'user_id' => $author->user_id,
        ]);
    }
}
