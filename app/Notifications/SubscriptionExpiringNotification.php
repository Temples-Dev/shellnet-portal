<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiringNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Subscription $subscription,
        private readonly int $daysLeft,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Your Plan Expires in {$this->daysLeft} Day(s) — " . config('app.name'))
            ->greeting('Hello ' . $notifiable->fullname . ',')
            ->line("Your **{$this->subscription->plan_name}** plan expires in **{$this->daysLeft} day(s)**.")
            ->line("**Expiry Date:** " . $this->subscription->expires_at?->format('d M Y'))
            ->line('Please renew before it expires to avoid service interruption.')
            ->action('Renew Now', url('/'))
            ->line('Thank you for being with ' . config('app.name') . '.');
    }
}
