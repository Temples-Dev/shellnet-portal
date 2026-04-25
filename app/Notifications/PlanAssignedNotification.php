<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlanAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Subscription $subscription) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Plan Has Been Activated — ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->fullname . ',')
            ->line("Your **{$this->subscription->plan_name}** plan has been activated.")
            ->line("**Expires:** " . $this->subscription->expires_at?->format('d M Y'))
            ->line("**Router:** " . $this->subscription->router_name)
            ->action('View Your Account', url('/'))
            ->line('Thank you for choosing ' . config('app.name') . '!');
    }
}
