<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\SendTwoFactorCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailTwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_uses_ui_admin_form_with_email_verification(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Create Your Account')
            ->assertSee('Phone Number (Optional)')
            ->assertSee('Birthday')
            ->assertSee('Email me a verification code')
            ->assertSee(route('register.store'));
    }

    public function test_login_sends_an_email_code_and_does_not_authenticate_before_verification(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('verification-code.challenge'));

        $this->assertGuest();
        Notification::assertSentOnDemand(SendTwoFactorCode::class, function (
            SendTwoFactorCode $notification,
            array $channels,
            object $notifiable,
        ) use ($user): bool {
            return $notifiable->routeNotificationFor('mail') === $user->email
                && $notification->purpose === 'login'
                && strlen($notification->code) === 6;
        });
    }

    public function test_invalid_password_does_not_send_a_code(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'not-the-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        Notification::assertNothingSent();
        $this->assertGuest();
    }

    public function test_correct_code_authenticates_once(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $code = $this->sentCodeFor($user->email);

        $this->post(route('verification-code.verify'), ['code' => $code])
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($user);

        $this->post(route('verification-code.verify'), ['code' => $code])
            ->assertRedirect(route('login'));
    }

    public function test_incorrect_code_does_not_authenticate(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->from(route('verification-code.challenge'))
            ->post(route('verification-code.verify'), ['code' => '000000'])
            ->assertRedirect(route('verification-code.challenge'))
            ->assertSessionHasErrors('code');

        $this->assertGuest();
    }

    public function test_expired_code_is_rejected_and_resend_replaces_it(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $codes = $this->sentCodesFor($user->email);
        $this->travel(61)->seconds();

        $this->from(route('verification-code.challenge'))
            ->post(route('verification-code.verify'), ['code' => $codes[0]])
            ->assertRedirect(route('verification-code.challenge'))
            ->assertSessionHasErrors('code');

        $this->post(route('verification-code.resend'))
            ->assertRedirect(route('verification-code.challenge'));

        $codes = $this->sentCodesFor($user->email);
        $this->assertCount(2, $codes);
        $this->assertNotSame($codes[0], $codes[1]);

        $this->post(route('verification-code.verify'), ['code' => $codes[1]])
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_creates_the_user_only_after_email_verification(): void
    {
        Notification::fake();
        $email = 'new.member@example.test';

        $this->post(route('register.store'), [
            'username' => 'new-member',
            'email' => $email,
            'phone_number' => '09123456789',
            'birthday' => '2001-06-15',
            'password' => 'Strong-password1!',
            'password_confirmation' => 'Strong-password1!',
        ])->assertRedirect(route('verification-code.challenge'));

        $this->assertDatabaseMissing('users', ['email' => $email]);
        $code = $this->sentCodeFor($email);

        $this->post(route('verification-code.verify'), ['code' => $code])
            ->assertRedirect('/');

        $user = User::query()->where('email', $email)->firstOrFail();
        $this->assertModelExists($user);
        $this->assertSame('new-member', $user->username);
        $this->assertSame('09123456789', $user->phone_number);
        $this->assertSame('2001-06-15', $user->birthday->format('Y-m-d'));
        $this->assertTrue(Hash::check('Strong-password1!', $user->password));
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_rejects_missing_birthday_and_weak_password_without_sending_a_code(): void
    {
        Notification::fake();
        $email = 'incomplete.member@example.test';

        $this->from(route('register'))
            ->post(route('register.store'), [
                'username' => 'new-member',
                'email' => $email,
                'password' => 'weak-password',
                'password_confirmation' => 'weak-password',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['birthday', 'password']);

        $this->assertDatabaseMissing('users', ['email' => $email]);
        Notification::assertNothingSent();
    }

    public function test_password_reset_requires_email_code_and_changes_the_password(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect(route('verification-code.challenge'));

        $code = $this->sentCodeFor($user->email);
        $this->post(route('verification-code.verify'), ['code' => $code])
            ->assertRedirect(route('password.reset'));

        $this->get(route('password.reset'))->assertOk();

        $this->post(route('password.update'), [
            'password' => 'new-strong-password',
            'password_confirmation' => 'new-strong-password',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('new-strong-password', $user->fresh()->password));
        $this->assertGuest();
    }

    public function test_unknown_password_reset_email_does_not_send_a_code_or_allow_reset(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'missing@example.test'])
            ->assertRedirect(route('verification-code.challenge'))
            ->assertSessionHas('status');

        Notification::assertNothingSent();

        $this->from(route('verification-code.challenge'))
            ->post(route('verification-code.verify'), ['code' => '123456'])
            ->assertRedirect(route('verification-code.challenge'))
            ->assertSessionHasErrors('code');

        $this->get(route('password.reset'))->assertRedirect(route('forgot-password'));
    }

    public function test_each_new_login_sends_a_new_code(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $firstCode = $this->sentCodeFor($user->email);
        $this->post(route('verification-code.verify'), ['code' => $firstCode])
            ->assertRedirect('/');
        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('verification-code.challenge'));

        $codes = $this->sentCodesFor($user->email);
        $this->assertCount(2, $codes);
        $this->assertNotSame($codes[0], $codes[1]);
        $this->assertGuest();
    }

    private function sentCodeFor(string $email): string
    {
        return $this->sentCodesFor($email)[0];
    }

    /**
     * @return list<string>
     */
    private function sentCodesFor(string $email): array
    {
        $codes = [];

        Notification::assertSentOnDemand(SendTwoFactorCode::class, function (
            SendTwoFactorCode $notification,
            array $channels,
            object $notifiable,
        ) use ($email, &$codes): bool {
            if ($notifiable->routeNotificationFor('mail') !== $email) {
                return false;
            }

            $codes[] = $notification->code;

            return true;
        });

        return $codes;
    }
}
