<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guests_are_redirected_from_profile_settings_to_sign_in(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
    }

    public function test_profile_settings_page_prefills_the_member_details(): void
    {
        $user = User::factory()->create([
            'username' => 'skywalker',
            'email' => 'luke@example.test',
            'birthday' => '1998-04-12',
        ]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('value="skywalker"', false)
            ->assertSee('value="luke@example.test"', false)
            ->assertSee('value="1998-04-12"', false);
    }

    public function test_valid_profile_update_saves_the_details_and_shows_a_status_message(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('user-profile-information.update'), [
                'username' => 'newname',
                'email' => 'new@example.test',
                'birthday' => '2000-01-31',
                'phone_number' => '+65 9123 4567',
                'website' => 'https://example.test',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'profile-information-updated');

        $user->refresh();
        $this->assertSame('newname', $user->username);
        $this->assertSame('new@example.test', $user->email);
        $this->assertSame('2000-01-31', $user->birthday->format('Y-m-d'));
        $this->assertSame('+65 9123 4567', $user->phone_number);
        $this->assertSame('https://example.test', $user->website);
    }

    public function test_uploaded_profile_picture_is_saved_and_shown(): void
    {
        $user = User::factory()->create(['birthday' => '1998-04-12']);

        $this->actingAs($user)->put(route('user-profile-information.update'), [
            'username' => $user->username,
            'email' => $user->email,
            'birthday' => '1998-04-12',
            'photo' => UploadedFile::fake()->image('avatar.png', 40, 40),
        ])->assertSessionHasNoErrors();

        $this->assertStringStartsWith('data:image/png;base64,', $user->fresh()->photoDataUri());

        $this->get(route('profile.edit'))->assertSee('src="data:image/png;base64,', false);
    }

    public function test_profile_update_rejects_an_email_used_by_another_member(): void
    {
        User::factory()->create(['email' => 'taken@example.test']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('user-profile-information.update'), [
                'username' => $user->username,
                'email' => 'taken@example.test',
                'birthday' => '1998-04-12',
            ])
            ->assertSessionHasErrorsIn('updateProfileInformation', [
                'email' => 'The email has already been taken.',
            ]);

        $this->assertNotSame('taken@example.test', $user->fresh()->email);
    }

    public function test_password_change_rejects_a_wrong_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('user-password.update'), [
                'current_password' => 'not-my-password',
                'password' => 'NewSecret1!',
                'password_confirmation' => 'NewSecret1!',
            ])
            ->assertSessionHasErrorsIn('updatePassword', [
                'current_password' => 'The provided password does not match your current password.',
            ]);

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_valid_password_change_saves_the_new_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('user-password.update'), [
                'current_password' => 'password',
                'password' => 'NewSecret1!',
                'password_confirmation' => 'NewSecret1!',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'password-updated');

        $this->assertTrue(Hash::check('NewSecret1!', $user->fresh()->password));
    }
}
