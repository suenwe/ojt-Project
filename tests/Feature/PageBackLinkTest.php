<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageBackLinkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_home_feed_does_not_show_a_back_link(): void
    {
        $member = User::factory()->create();

        $response = $this->actingAs($member)->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('class="page-back"', false);
    }

    public function test_back_link_returns_to_the_previous_page_on_this_site(): void
    {
        $member = User::factory()->create();
        $previousPage = route('users.show', $member);

        $response = $this->actingAs($member)->from($previousPage)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee('<a class="page-back" href="'.$previousPage.'">', false);
    }

    public function test_back_link_falls_back_to_home_when_coming_from_another_site(): void
    {
        $member = User::factory()->create();

        $response = $this->actingAs($member)->from('https://example.com/elsewhere')->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee('<a class="page-back" href="'.route('home').'">', false);
    }

    public function test_admin_sections_link_back_but_the_overview_does_not(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $overview = $this->actingAs($admin)->get(route('admin.dashboard'));
        $reports = $this->actingAs($admin)->get(route('admin.dashboard', ['section' => 'reports']));

        $overview->assertOk();
        $overview->assertDontSee('class="page-back"', false);
        $reports->assertOk();
        $reports->assertSee('<a class="page-back" href="'.route('admin.dashboard').'">', false);
    }
}
