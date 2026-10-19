<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_member_deletes_their_account_with_posts_and_files_and_is_signed_out(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('post-media/photo.png', 'image-bytes');
        $member = User::factory()->create();
        $post = Post::query()->create([
            'descriptions' => 'Soon gone',
            'state' => 'published',
            'date_time' => now(),
            'user_id' => $member->user_id,
        ]);
        $post->media()->create([
            'media_type' => 'photo',
            'mime_type' => 'image/png',
            'media_path' => 'post-media/photo.png',
            'file_size' => 11,
        ]);

        $this->actingAs($member)
            ->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Your account has been deleted.');

        $this->assertGuest();
        $this->assertModelMissing($member);
        $this->assertModelMissing($post);
        Storage::disk('public')->assertMissing('post-media/photo.png');
    }

    public function test_wrong_password_keeps_the_account(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->delete(route('profile.destroy'), ['password' => 'wrong-password'])
            ->assertSessionHasErrorsIn('deleteAccount', ['password' => 'The password is incorrect.']);

        $this->assertModelExists($member);
        $this->assertAuthenticatedAs($member);
    }

    public function test_admins_cannot_delete_their_own_account_and_do_not_see_the_option(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertDontSee('Delete my account');

        $this->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertForbidden();

        $this->assertModelExists($admin);
    }
}
