<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_renders_the_reset_link_form(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Send Reset Link');
    }

    public function test_requesting_a_reset_link_emails_the_user_and_shows_a_status_message(): void
    {
        $user = User::factory()->create();
        Notification::fake();

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('status', 'We have emailed your password reset link.');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_page_prefills_the_email_from_the_link(): void
    {
        $this->get(route('password.reset', ['token' => 'reset-token', 'email' => 'member@example.test']))
            ->assertOk()
            ->assertSee('value="reset-token"', false)
            ->assertSee('value="member@example.test"', false);
    }

    public function test_valid_reset_changes_the_password_and_redirects_to_sign_in(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewSecret1!',
            'password_confirmation' => 'NewSecret1!',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Your password has been reset.');

        $this->assertTrue(Hash::check('NewSecret1!', $user->fresh()->password));
    }

    public function test_reset_rejects_a_password_without_a_number_or_symbol(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'onlyletters',
            'password_confirmation' => 'onlyletters',
        ])
            ->assertSessionHasErrors(['password' => 'The password field must contain at least one symbol.'])
            ->assertSessionHasErrors(['password' => 'The password field must contain at least one number.']);

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}
