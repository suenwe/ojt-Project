<?php

namespace App\Notifications;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PostReported extends Notification
{
    use Queueable;

    public function __construct(public Report $report) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $post = $this->report->post;

        return (new MailMessage)
            ->subject('A post needs moderation review')
            ->greeting('Hello '.$notifiable->username.',')
            ->line('A member reported a post.')
            ->line('Report #'.$this->report->report_id.' · '.$this->report->report_type)
            ->line('Reported by: '.($this->report->reporter?->username ?? 'Deleted account'))
            ->line('Post author: '.($post?->user?->username ?? 'Unknown author'))
            ->line('Post: '.($post?->descriptions ?? 'Post content is unavailable.'))
            ->line('Additional details: '.($this->report->reason ?: 'None supplied.'))
            ->action('Review report queue', route('admin.dashboard', ['section' => 'reports']));
    }
}
