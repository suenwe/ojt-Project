<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
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
                'current_password' => 'password',
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

    public function test_profile_update_rejects_a_username_used_by_another_member_in_any_letter_case(): void
    {
        User::factory()->create(['username' => 'Pyae']);
        $user = User::factory()->create(['username' => 'original']);

        $this->actingAs($user)
            ->put(route('user-profile-information.update'), [
                'username' => 'PYAE',
                'email' => $user->email,
                'birthday' => '1998-04-12',
            ])
            ->assertSessionHasErrorsIn('updateProfileInformation', [
                'username' => 'This username is already taken. Please choose another one.',
            ]);

        $this->assertSame('original', $user->fresh()->username);
    }

    public function test_member_can_change_the_letter_case_of_their_own_username(): void
    {
        $user = User::factory()->create(['username' => 'pyae']);

        $this->actingAs($user)
            ->put(route('user-profile-information.update'), [
                'username' => 'Pyae',
                'email' => $user->email,
                'birthday' => '1998-04-12',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Pyae', $user->fresh()->username);
    }

    public function test_changing_email_requires_the_correct_current_password(): void
    {
        $user = User::factory()->create(['email' => 'old@example.test']);
        $details = ['username' => $user->username, 'email' => 'new@example.test', 'birthday' => '1998-04-12'];

        $this->actingAs($user)
            ->put(route('user-profile-information.update'), $details)
            ->assertSessionHasErrorsIn('updateProfileInformation', [
                'current_password' => 'Enter your current password to change your email address.',
            ]);

        $this->put(route('user-profile-information.update'), $details + ['current_password' => 'wrong-password'])
            ->assertSessionHasErrorsIn('updateProfileInformation', [
                'current_password' => 'The current password is incorrect.',
            ]);

        $this->assertSame('old@example.test', $user->fresh()->email);
    }

    public function test_keeping_the_same_email_does_not_need_the_current_password(): void
    {
        $user = User::factory()->create(['email' => 'same@example.test']);

        $this->actingAs($user)
            ->put(route('user-profile-information.update'), [
                'username' => 'renamed',
                'email' => 'SAME@example.test',
                'birthday' => '1998-04-12',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('renamed', $user->fresh()->username);
    }

    public function test_member_can_remove_their_profile_picture(): void
    {
        $user = User::factory()->create([
            'photo' => base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC'),
        ]);
        $this->assertNotNull($user->photoDataUri());

        $this->actingAs($user)
            ->put(route('user-profile-information.update'), [
                'username' => $user->username,
                'email' => $user->email,
                'birthday' => '1998-04-12',
                'remove_photo' => '1',
            ])
            ->assertSessionHasNoErrors();

        $this->assertNull($user->fresh()->photoDataUri());
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

    public function test_password_change_signs_the_member_out_of_other_devices(): void
    {
        $user = User::factory()->create(['remember_token' => 'old-remember-token']);
        $someoneElse = User::factory()->create();

        DB::table('sessions')->insert([
            ['id' => 'other-device', 'user_id' => $user->user_id, 'ip_address' => '192.0.2.1', 'user_agent' => 'Phone', 'payload' => '', 'last_activity' => now()->timestamp],
            ['id' => 'someone-else', 'user_id' => $someoneElse->user_id, 'ip_address' => '192.0.2.2', 'user_agent' => 'Laptop', 'payload' => '', 'last_activity' => now()->timestamp],
        ]);

        $this->actingAs($user)
            ->put(route('user-password.update'), [
                'current_password' => 'password',
                'password' => 'NewSecret1!',
                'password_confirmation' => 'NewSecret1!',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('sessions', ['id' => 'other-device']);
        $this->assertDatabaseHas('sessions', ['id' => 'someone-else']);
        $this->assertNotSame('old-remember-token', $user->fresh()->remember_token);
        $this->assertAuthenticatedAs($user);
    }
}
