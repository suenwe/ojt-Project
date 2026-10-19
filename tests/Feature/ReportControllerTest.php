<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use App\Notifications\PostReported;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_report_another_members_post_and_admins_receive_the_details(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $secondAdmin = User::factory()->create(['role' => 'admin']);
        $reporter = User::factory()->create();
        $author = User::factory()->create();
        $post = Post::query()->create([
            'user_id' => $author->user_id,
            'descriptions' => 'Post that needs review',
        ]);

        $this->actingAs($reporter)
            ->post(route('report.store'), [
                'post_id' => $post->post_id,
                'report_type' => 'Bullying, harassment or abuse',
                'reason' => 'This post targets a member.',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('status', 'Your report was sent to the admin team for review.');

        $report = Report::query()->with(['reporter', 'post.user'])->sole();
        $this->assertDatabaseHas('reports', [
            'report_id' => $report->report_id,
            'reporter_id' => $reporter->user_id,
            'target_id' => $post->post_id,
            'report_type' => 'Bullying, harassment or abuse',
            'reason' => 'This post targets a member.',
            'status' => 'pending',
        ]);
        Notification::assertSentTo($admin, PostReported::class);
        Notification::assertSentTo($secondAdmin, PostReported::class);

        $mail = (new PostReported($report))->toMail($admin)->render();
        $this->assertStringContainsString('This post targets a member.', $mail);
        $this->assertStringContainsString('Post that needs review', $mail);
    }

    public function test_member_cannot_report_their_own_post(): void
    {
        Notification::fake();
        $member = User::factory()->create();
        $post = Post::query()->create([
            'user_id' => $member->user_id,
            'descriptions' => 'My own post',
        ]);

        $this->actingAs($member)
            ->post(route('report.store'), [
                'post_id' => $post->post_id,
                'report_type' => 'Bullying, harassment or abuse',
            ])
            ->assertSessionHasErrors('report_type');

        $this->assertSame(0, Report::query()->count());
        Notification::assertNothingSent();
    }
}
