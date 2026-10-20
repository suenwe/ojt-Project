<?php

namespace Tests\Feature;
use App\Models\User;
use App\Notifications\SendTwoFactorCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_saves_the_birthday_and_phone_number_with_the_new_account(): void
    {
        Notification::fake();
        $email = 'newmember@example.test';

        $this->post(route('register.store'), [
            'username' => 'newmember',
            'email' => $email,
            'phone_number' => '09123456789',
            'birthday' => '2001-06-15',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ])->assertRedirect(route('verification-code.challenge'));

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => $email]);

        $code = '';
        Notification::assertSentOnDemand(SendTwoFactorCode::class, function (
            SendTwoFactorCode $notification,
            array $channels,
            object $notifiable,
        ) use ($email, &$code): bool {
            if ($notifiable->routeNotificationFor('mail') !== $email
                || $notification->purpose !== 'registration') {
                return false;
            }

            $code = $notification->code;

            return true;
        });

        $this->post(route('verification-code.verify'), ['code' => $code])
            ->assertRedirect('/');

        $user = User::where('email', $email)->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('2001-06-15', $user->birthday->format('Y-m-d'));
        $this->assertSame('09123456789', $user->phone_number);
    }

    public function test_registration_rejects_a_username_that_is_taken_in_any_letter_case(): void
    {
        Notification::fake();
        User::factory()->create(['username' => 'Pyae']);

        $this->from(route('register'))
            ->post(route('register.store'), [
                'username' => 'pyae',
                'email' => 'second.pyae@example.test',
                'birthday' => '2001-06-15',
                'password' => 'Secret123!',
                'password_confirmation' => 'Secret123!',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['username' => 'This username is already taken. Please choose another one.']);

        Notification::assertNothingSent();
    }

    public function test_verification_fails_cleanly_when_the_username_was_taken_while_waiting_for_the_code(): void
    {
        Notification::fake();
        $email = 'late.member@example.test';

        $this->post(route('register.store'), [
            'username' => 'latecomer',
            'email' => $email,
            'birthday' => '2001-06-15',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ])->assertRedirect(route('verification-code.challenge'));

        $code = '';
        Notification::assertSentOnDemand(SendTwoFactorCode::class, function (SendTwoFactorCode $notification) use (&$code): bool {
            $code = $notification->code;

            return true;
        });

        User::factory()->create(['username' => 'LateComer']);

        $this->post(route('verification-code.verify'), ['code' => $code])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('username');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => $email]);
    }
}
