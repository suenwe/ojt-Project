<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_saves_the_birthday_and_phone_number_with_the_new_account(): void
    {
        $this->post(route('register.store'), [
            'username' => 'newmember',
            'email' => 'newmember@example.test',
            'phone_number' => '09123456789',
            'birthday' => '2001-06-15',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ])->assertRedirect('/');

        $user = User::where('email', 'newmember@example.test')->firstOrFail();
        $this->assertSame('2001-06-15', $user->birthday->format('Y-m-d'));
        $this->assertSame('09123456789', $user->phone_number);
    }
}
