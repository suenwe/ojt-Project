<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_members_can_sign_in_with_valid_credentials(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }

    public function test_banned_members_are_refused_with_a_suspended_message(): void
    {
        $user = User::factory()->create(['banned_at' => now()]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'This account has been suspended.']);

        $this->assertGuest();
    }

    public function test_expired_report_ban_is_cleared_and_member_can_sign_in(): void
    {
        $this->travelTo('2026-10-19 12:00:00');
        $user = User::factory()->create([
            'banned_at' => now()->subDays(3),
            'ban_expires_at' => now()->subSecond(),
            'ban_reason' => 'Confirmed report #1',
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'banned_at' => null,
            'ban_expires_at' => null,
            'ban_reason' => null,
        ]);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors(['email' => 'These credentials do not match our records.']);

        $this->assertGuest();
    }
}
