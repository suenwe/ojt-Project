<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPhotoControllerTest extends TestCase
{
    use RefreshDatabase;

    private const TINY_PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guests_are_redirected_to_sign_in(): void
    {
        $member = User::factory()->create(['photo' => base64_decode(self::TINY_PNG)]);

        $this->get(route('users.photo', $member))->assertRedirect(route('login'));
    }

    public function test_returns_the_members_photo_as_an_image(): void
    {
        $member = User::factory()->create(['photo' => base64_decode(self::TINY_PNG)]);

        $response = $this->actingAs(User::factory()->create())->get(route('users.photo', $member));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
        $this->assertSame(base64_decode(self::TINY_PNG), $response->getContent());
    }

    public function test_returns_304_when_the_browser_already_has_the_same_photo(): void
    {
        $member = User::factory()->create(['photo' => base64_decode(self::TINY_PNG)]);
        $viewer = User::factory()->create();
        $etag = $this->actingAs($viewer)->get(route('users.photo', $member))->headers->get('ETag');

        $response = $this->actingAs($viewer)
            ->withHeader('If-None-Match', $etag)
            ->get(route('users.photo', $member));

        $response->assertStatus(304);
    }

    public function test_returns_404_when_the_member_has_no_photo(): void
    {
        $member = User::factory()->create(['photo' => null]);

        $this->actingAs(User::factory()->create())
            ->get(route('users.photo', $member))
            ->assertNotFound();
    }

    public function test_suspended_members_photo_is_hidden_from_other_members(): void
    {
        $member = User::factory()->create(['photo' => base64_decode(self::TINY_PNG), 'banned_at' => now()]);

        $this->actingAs(User::factory()->create())
            ->get(route('users.photo', $member))
            ->assertNotFound();
    }

    public function test_feed_shows_the_authors_photo_and_falls_back_to_initials(): void
    {
        $withPhoto = User::factory()->create(['username' => 'photo-owner', 'photo' => base64_decode(self::TINY_PNG)]);
        $withoutPhoto = User::factory()->create(['username' => 'zed-plain', 'photo' => null]);
        $this->createPost($withPhoto);
        $this->createPost($withoutPhoto);

        $response = $this->actingAs(User::factory()->create())->get(route('home'));

        $response->assertOk();
        $response->assertSee('src="'.route('users.photo', $withPhoto).'"', false);
        $response->assertDontSee('src="'.route('users.photo', $withoutPhoto).'"', false);
        $this->assertMatchesRegularExpression(
            '/href="'.preg_quote(route('users.show', $withoutPhoto), '/').'" aria-hidden="true" tabindex="-1">\s*ZE\s*<\/a>/',
            $response->getContent(),
        );
    }

    private function createPost(User $author): Post
    {
        return Post::query()->create([
            'descriptions' => 'Hello from '.$author->username,
            'category_id' => Category::query()->firstOrCreate(['category_type' => 'Technology'])->category_id,
            'post_type' => 'Discussion',
            'state' => 'published',
            'date_time' => now()->subHour(),
            'user_id' => $author->user_id,
        ]);
    }
}
