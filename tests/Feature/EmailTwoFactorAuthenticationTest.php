<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\SendTwoFactorCode;
use App\Services\EmailOtpService;
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

    public function test_login_signs_in_with_email_and_password_without_sending_a_code(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
        Notification::assertNothingSent();
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

    public function test_correct_code_works_only_once(): void
    {
        Notification::fake();
        $this->startRegistration('once@example.test');
        $code = $this->sentCodeFor('once@example.test');

        $this->post(route('verification-code.verify'), ['code' => $code])
            ->assertRedirect('/');

        $this->assertAuthenticatedAs(User::query()->where('email', 'once@example.test')->sole());

        $this->post(route('verification-code.verify'), ['code' => $code])
            ->assertRedirect(route('login'));
    }

    public function test_incorrect_code_does_not_create_the_account(): void
    {
        Notification::fake();
        $this->startRegistration('wrong.code@example.test');

        $this->from(route('verification-code.challenge'))
            ->post(route('verification-code.verify'), ['code' => '000000'])
            ->assertRedirect(route('verification-code.challenge'))
            ->assertSessionHasErrors('code');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'wrong.code@example.test']);
    }

    public function test_expired_code_is_rejected_and_resend_replaces_it(): void
    {
        Notification::fake();
        $this->startRegistration('expired@example.test');
        $codes = $this->sentCodesFor('expired@example.test');
        $this->travel(9)->minutes();
        $this->assertFalse(app(EmailOtpService::class)->hasExpired());
        $this->travel(2)->minutes();

        $this->from(route('verification-code.challenge'))
            ->post(route('verification-code.verify'), ['code' => $codes[0]])
            ->assertRedirect(route('verification-code.challenge'))
            ->assertSessionHasErrors('code');

        $this->post(route('verification-code.resend'))
            ->assertRedirect(route('verification-code.challenge'));

        $codes = $this->sentCodesFor('expired@example.test');
        $this->assertCount(2, $codes);
        $this->assertNotSame($codes[0], $codes[1]);

        $this->post(route('verification-code.verify'), ['code' => $codes[1]])
            ->assertRedirect('/');

        $this->assertAuthenticatedAs(User::query()->where('email', 'expired@example.test')->sole());
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
            ->assertRedirect(route('otp.password.reset'));

        $this->get(route('otp.password.reset'))->assertOk();

        $this->post(route('password.update'), [
            'password' => 'New-strong-password1!',
            'password_confirmation' => 'New-strong-password1!',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('New-strong-password1!', $user->fresh()->password));
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

        $this->get(route('otp.password.reset'))->assertRedirect(route('password.request'));
    }

    private function startRegistration(string $email): void
    {
        $this->post(route('register.store'), [
            'username' => strstr($email, '@', true),
            'email' => $email,
            'birthday' => '2001-06-15',
            'password' => 'Strong-password1!',
            'password_confirmation' => 'Strong-password1!',
        ])->assertRedirect(route('verification-code.challenge'));
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
