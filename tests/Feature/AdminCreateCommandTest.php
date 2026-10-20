<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCreateCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_create_refuses_a_username_that_is_already_taken(): void
    {
        User::factory()->create(['username' => 'Pyae']);

        $this->artisan('admin:create')
            ->expectsQuestion('Administrator email address', 'new.admin@example.test')
            ->expectsQuestion('Administrator username', 'pyae')
            ->expectsOutput('That username is already taken. Choose another one.')
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'new.admin@example.test']);
    }

    public function test_admin_create_rejects_a_weak_password(): void
    {
        $this->artisan('admin:create')
            ->expectsQuestion('Administrator email address', 'new.admin@example.test')
            ->expectsQuestion('Administrator username', 'newadmin')
            ->expectsQuestion('Password (at least 8 characters, with a number and a symbol)', 'onlyletters')
            ->expectsQuestion('Confirm password', 'onlyletters')
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'new.admin@example.test']);
    }

    public function test_admin_create_makes_an_admin_with_a_strong_password(): void
    {
        $this->artisan('admin:create')
            ->expectsQuestion('Administrator email address', 'new.admin@example.test')
            ->expectsQuestion('Administrator username', 'newadmin')
            ->expectsQuestion('Password (at least 8 characters, with a number and a symbol)', 'Admin123!')
            ->expectsQuestion('Confirm password', 'Admin123!')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', ['email' => 'new.admin@example.test', 'role' => 'admin']);
    }
}
