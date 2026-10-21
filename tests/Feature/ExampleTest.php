<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_main_page_to_sign_in(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_signed_in_members_see_their_own_name_on_the_main_page(): void
    {
        $this->withoutVite();

        $this->actingAs(User::factory()->create(['username' => 'skywalker']))
            ->get('/')
            ->assertOk()
            ->assertSee('skywalker')
            ->assertDontSee('Alex Morgan');
    }
}
