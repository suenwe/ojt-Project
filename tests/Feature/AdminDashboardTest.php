<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Keyword;
use App\Models\Notification;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use App\Models\UserAccessLog;
use App\Notifications\ReportConfirmed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guests_are_sent_to_the_shared_sign_in_page(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.login'))
            ->assertRedirect(route('login'));

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Use your Knowvia email and password.');
    }

    public function test_admin_returns_to_the_dashboard_after_signing_in_on_the_shared_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->get(route('admin.login'));

        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_goes_to_the_dashboard_even_after_first_opening_the_main_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->get(route('home'))->assertRedirect(route('login'));

        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_returns_to_the_admin_page_they_were_opening(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->get(route('admin.dashboard', ['section' => 'users']))->assertRedirect(route('login'));

        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard', ['section' => 'users']));
    }

    public function test_members_signing_in_on_the_shared_page_go_to_the_feed(): void
    {
        $member = User::factory()->create();

        $this->post(route('login.store'), [
            'email' => $member->email,
            'password' => 'password',
        ])->assertRedirect(route('home'));
    }

    public function test_non_admin_accounts_cannot_open_the_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_banned_accounts_cannot_access_web_routes(): void
    {
        $user = User::factory()->create(['banned_at' => now()]);

        $this->actingAs($user)
            ->get('/')
            ->assertForbidden();
    }

    public function test_admin_can_sign_in_and_view_member_details(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create([
            'username' => 'skywalker',
            'photo' => base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC'),
            'birthday' => '1998-04-12',
            'browser' => 'Firefox 135.0',
            'IP_address' => '192.0.2.12',
        ]);

        $this->get(route('admin.dashboard'));

        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard', ['section' => 'users']))
            ->assertOk()
            ->assertSee('skywalker')
            ->assertSee('1998')
            ->assertSee('Firefox 135.0')
            ->assertSee('192.0.2.12')
            ->assertSee('data:image/png;base64,')
            ->assertDontSee('ACCOUNT ACTION')
            ->assertDontSee('Unban');

        $this->assertAuthenticatedAs($admin);
        $this->assertDatabaseHas('users', ['user_id' => $member->user_id]);
    }

    public function test_authenticated_web_activity_updates_the_users_browser_and_ip(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withHeader('User-Agent', 'Mozilla/5.0 Chrome/124.0.0.0 Safari/537.36')
            ->get('/')
            ->assertOk();

        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'IP_address' => '127.0.0.1',
            'browser' => 'Chrome 124.0.0.0',
        ]);

        $this->assertDatabaseHas('user_access_logs', [
            'user_id' => $user->user_id,
            'ip_address' => '127.0.0.1',
            'browser' => 'Chrome 124.0.0.0',
            'method' => 'GET',
            'path' => '/',
        ]);
    }

    public function test_user_management_includes_admins_and_filters_accounts(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'username' => 'consoleadmin',
            'email' => 'consoleadmin@example.com',
        ]);
        $member = User::factory()->create([
            'username' => 'filtertarget',
            'email' => 'filtertarget@example.com',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['section' => 'users', 'search' => 'filtertarget', 'role' => 'user']))
            ->assertOk()
            ->assertSee('filtertarget@example.com')
            ->assertDontSee('consoleadmin@example.com')
            ->assertSee('User management');

        $this->get(route('admin.dashboard', ['section' => 'users', 'role' => 'admin']))
            ->assertOk()
            ->assertSee('consoleadmin@example.com')
            ->assertDontSee('filtertarget@example.com');

        $this->assertDatabaseHas('users', ['user_id' => $member->user_id]);
    }

    public function test_admin_can_unban_a_member_from_user_management(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create([
            'banned_at' => now(),
            'ban_reason' => 'Manual admin ban',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['section' => 'users', 'status' => 'banned']))
            ->assertOk()
            ->assertSee('Unban')
            ->assertSee(route('admin.users.ban', $member), false);

        $this->patch(route('admin.users.ban', $member), ['action' => 'unban'])
            ->assertRedirect(route('admin.dashboard', ['section' => 'users']))
            ->assertSessionHas('status', 'User unbanned.');

        $this->assertDatabaseHas('users', [
            'user_id' => $member->user_id,
            'banned_at' => null,
            'ban_expires_at' => null,
            'ban_reason' => null,
        ]);
    }

    public function test_admin_report_queue_displays_post_images_and_videos(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = User::factory()->create();
        $reporter = User::factory()->create();
        $post = Post::query()->create([
            'user_id' => $author->user_id,
            'descriptions' => 'Reported post with media',
        ]);
        $image = $post->media()->create([
            'media_type' => 'photo',
            'mime_type' => 'image/png',
            'media_path' => 'post-media/reported.png',
            'file_size' => 9,
        ]);
        $video = $post->media()->create([
            'media_type' => 'video',
            'mime_type' => 'video/mp4',
            'media_path' => 'post-media/reported.mp4',
            'file_size' => 9,
        ]);
        Report::query()->create([
            'reporter_id' => $reporter->user_id,
            'target_id' => $post->post_id,
            'report_type' => 'Adult content',
            'reason' => 'Please review the attached media.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['section' => 'reports']))
            ->assertOk()
            ->assertSee('Reported post with media')
            ->assertSee(route('media.show', $image), false)
            ->assertSee(route('media.show', $video), false)
            ->assertSee('<img', false)
            ->assertSee('<video', false)
            ->assertSee('controls', false)
            ->assertSee("data-media-carousel='".json_encode([
                ['url' => route('media.show', $image), 'type' => 'image/png', 'alt' => 'Image attached to reported post'],
                ['url' => route('media.show', $video), 'type' => 'video/mp4', 'alt' => 'Image attached to reported post'],
            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)."'", false);
    }

    public function test_admin_can_view_persisted_user_access_logs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['username' => 'loggedmember']);

        $this->actingAs($member)
            ->withHeader('User-Agent', 'Mozilla/5.0 Firefox/136.0')
            ->get('/')
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['section' => 'access-logs']))
            ->assertOk()
            ->assertSee('User access log')
            ->assertSee('loggedmember')
            ->assertSee('Firefox 136.0')
            ->assertSee('127.0.0.1');

        $this->assertTrue(UserAccessLog::query()->where('user_id', $member->user_id)->exists());
    }

    public function test_admin_can_open_every_dashboard_section(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach ([
            'overview' => 'Members, reports and categories at a glance.',
            'reports' => 'Report queue',
            'users' => 'User management',
            'access-logs' => 'User access log',
            'categories' => 'Delete category?',
            'restricted' => 'Keywords',
        ] as $section => $heading) {
            $this->actingAs($admin)
                ->get(route('admin.dashboard', $section === 'overview' ? [] : ['section' => $section]))
                ->assertOk()
                ->assertSee($heading);
        }
    }

    public function test_notifications_are_no_longer_an_admin_dashboard_section(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Notifications');

        $this->get(route('admin.dashboard', ['section' => 'notifications']))
            ->assertNotFound();
    }

    public function test_original_post_categories_are_seeded_and_visible_to_admins(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['section' => 'categories']))
            ->assertOk()
            ->assertSee('Technology')
            ->assertSee('Education')
            ->assertSee('General Discussion')
            ->assertSee('15')
            ->assertSee('ACTIVE CATEGORIES');

        $this->assertSame(15, Category::query()->count());
    }

    public function test_destructive_actions_use_a_centered_confirmation_dialog(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['section' => 'categories']))
            ->assertOk()
            ->assertSee('<dialog class="confirmation-dialog"', false)
            ->assertSee('data-confirm-title="Delete category?"', false)
            ->assertSee('data-confirm-action="Delete category"', false)
            ->assertSee('data-confirm-cancel', false);
    }

    public function test_successful_report_review_suspends_the_post_author_for_three_days_and_notifies_them(): void
    {
        $this->travelTo('2026-10-19 12:00:00');
        NotificationFacade::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $author = User::factory()->create();
        $reporter = User::factory()->create();
        $post = Post::query()->create([
            'user_id' => $author->user_id,
            'descriptions' => 'Reported post content',
        ]);
        $report = Report::query()->create([
            'reporter_id' => $reporter->user_id,
            'target_id' => $post->post_id,
            'report_type' => 'Bullying, harassment or abuse',
            'reason' => 'This post targets another member.',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.reports.review', $report), ['decision' => 'valid'])
            ->assertRedirect(route('admin.dashboard', ['section' => 'reports']));

        $this->assertDatabaseHas('reports', [
            'report_id' => $report->report_id,
            'status' => 'valid',
            'reviewed_by' => $admin->user_id,
        ]);
        $this->assertDatabaseHas('users', [
            'user_id' => $author->user_id,
            'ban_reason' => 'Confirmed report #'.$report->report_id.': Bullying, harassment or abuse',
        ]);
        $this->assertNotNull($author->fresh()->banned_at);
        $this->assertSame('2026-10-22 12:00:00', $author->fresh()->ban_expires_at->format('Y-m-d H:i:s'));
        NotificationFacade::assertSentTo($author, ReportConfirmed::class);

        $this->assertSame(Post::STATE_HIDDEN, $post->fresh()->state);
        $this->travel(4)->days();
        $this->actingAs($reporter)->get(route('home'))->assertOk()->assertDontSee('Reported post content');
    }

    public function test_dismissing_a_report_does_not_ban_the_post_author(): void
    {
        NotificationFacade::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $author = User::factory()->create();
        $report = $this->makeReport($author);

        $this->actingAs($admin)
            ->patch(route('admin.reports.review', $report), ['decision' => 'invalid'])
            ->assertRedirect();

        $this->assertDatabaseHas('reports', ['report_id' => $report->report_id, 'status' => 'invalid']);
        $this->assertNull($author->fresh()->banned_at);
        $this->assertSame('published', $report->post->fresh()->state);
        NotificationFacade::assertNothingSent();
    }

    public function test_confirming_a_report_does_not_shorten_an_existing_indefinite_admin_ban(): void
    {
        NotificationFacade::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $author = User::factory()->create([
            'banned_at' => now()->subDay(),
            'ban_expires_at' => null,
            'ban_reason' => 'Manual admin ban',
        ]);
        $report = $this->makeReport($author);

        $this->actingAs($admin)
            ->patch(route('admin.reports.review', $report), ['decision' => 'valid'])
            ->assertRedirect(route('admin.dashboard', ['section' => 'reports']))
            ->assertSessionHas('status', 'Report confirmed. The post is hidden, and its author remains under an indefinite admin suspension.');

        $this->assertDatabaseHas('users', [
            'user_id' => $author->user_id,
            'ban_reason' => 'Manual admin ban',
            'ban_expires_at' => null,
        ]);
        NotificationFacade::assertSentTo($author, ReportConfirmed::class);
    }

    public function test_expired_report_suspension_is_cleared_when_the_member_returns(): void
    {
        $this->travelTo('2026-10-19 12:00:00');
        $member = User::factory()->create([
            'banned_at' => now()->subDays(3),
            'ban_expires_at' => now()->subSecond(),
            'ban_reason' => 'Confirmed report #1',
        ]);

        $this->actingAs($member)->get(route('home'))->assertOk();

        $this->assertDatabaseHas('users', [
            'user_id' => $member->user_id,
            'banned_at' => null,
            'ban_expires_at' => null,
            'ban_reason' => null,
        ]);
    }

    public function test_admin_can_manage_categories_restricted_terms_and_notifications(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), ['category_type' => 'Science & Nature'])
            ->assertRedirect(route('admin.dashboard', ['section' => 'categories']));

        $category = Category::query()->where('category_type', 'Science & Nature')->firstOrFail();
        $this->put(route('admin.categories.update', $category), ['category_type' => 'Science'])
            ->assertRedirect();
        $this->assertDatabaseHas('categories', ['category_id' => $category->category_id, 'category_type' => 'Science']);

        $this->post(route('admin.keywords.store'), ['keyword_text' => 'restricted phrase'])->assertRedirect();
        $keyword = Keyword::query()->where('keyword_text', 'restricted phrase')->firstOrFail();
        $this->delete(route('admin.keywords.destroy', $keyword))->assertRedirect();
        $this->assertDatabaseMissing('keywords', ['keyword_id' => $keyword->keyword_id]);

        $this->post(route('admin.notifications.store'), [
            'notification_type' => 'Community update',
            'notification_text' => 'A new update is available.',
        ])->assertRedirect();

        $notification = Notification::query()->where('notification_type', 'Community update')->firstOrFail();
        $this->put(route('admin.notifications.update', $notification), [
            'notification_type' => 'Community news',
            'notification_text' => 'A refreshed message.',
        ])->assertRedirect();
        $this->assertDatabaseHas('notifications', [
            'notification_id' => $notification->notification_id,
            'notification_type' => 'Community news',
            'notification_text' => 'A refreshed message.',
        ]);
    }

    public function test_admin_creates_another_admin_account_from_the_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['section' => 'users']))
            ->assertOk()
            ->assertSee('Create admin account');

        $this->post(route('admin.admins.store'), [
            'username' => 'newadmin',
            'email' => 'new.admin@example.test',
            'password' => 'Admin123!',
            'password_confirmation' => 'Admin123!',
        ])
            ->assertRedirect(route('admin.dashboard', ['section' => 'users']))
            ->assertSessionHas('status');

        $newAdmin = User::query()->where('email', 'new.admin@example.test')->sole();
        $this->assertSame('admin', $newAdmin->role);
        $this->assertTrue(Hash::check('Admin123!', $newAdmin->password));
    }

    public function test_members_cannot_create_admin_accounts(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.admins.store'), [
                'username' => 'sneaky',
                'email' => 'sneaky@example.test',
                'password' => 'Admin123!',
                'password_confirmation' => 'Admin123!',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'sneaky@example.test']);
    }

    public function test_creating_an_admin_rejects_a_weak_password_and_a_taken_username(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'username' => 'Boss']);

        $this->actingAs($admin)
            ->post(route('admin.admins.store'), [
                'username' => 'boss',
                'email' => 'second@example.test',
                'password' => 'weakpass',
                'password_confirmation' => 'weakpass',
            ])
            ->assertSessionHasErrorsIn('createAdmin', ['username', 'password']);

        $this->assertDatabaseMissing('users', ['email' => 'second@example.test']);
    }

    private function makeReport(User $author): Report
    {
        $post = Post::query()->create([
            'user_id' => $author->user_id,
            'descriptions' => 'A reported post',
        ]);

        return Report::query()->create([
            'reporter_id' => User::factory()->create()->user_id,
            'target_id' => $post->post_id,
            'report_type' => 'Scam, fraud or false information',
            'reason' => 'This claim is false.',
        ]);
    }
}
