<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendTwoFactorCode extends Notification
{
    public function __construct(
        public string $code,
        public string $purpose,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Knowvia verification code')
            ->greeting('Hello!')
            ->line('Your verification code is: '.$this->code)
            ->line('This code expires in ten minutes and can only be used once.')
            ->line(match ($this->purpose) {
                'registration' => 'Use this code to verify your email and finish creating your account.',
                'password_reset' => 'Use this code to verify your email before resetting your password.',
                default => 'Use this code to finish signing in to your account.',
            })
            ->line('If you did not request this code, you can ignore this email.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
