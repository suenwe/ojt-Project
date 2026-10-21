<?php

namespace App\Notifications;

use Carbon\CarbonInterface;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportConfirmed extends Notification
{
    public function __construct(
        public string $reportType,
        public ?CarbonInterface $expiresAt,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Update about your account suspension')
            ->greeting('Hello '.$notifiable->username.',')
            ->line('An admin reviewed a report about one of your posts and confirmed it as valid.')
            ->line('Report reason: '.$this->reportType)
            ->line('The reported post has been hidden from other members.');

        if ($this->expiresAt === null) {
            return $message->line('Your account was already under an indefinite admin suspension, and that suspension remains in place.');
        }

        return $message
            ->line('Your account is suspended for three days, until '.$this->expiresAt->format('F j, Y g:i A T').'.')
            ->line('Access will be restored automatically after that time.');
    }
}
