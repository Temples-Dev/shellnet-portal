<?php

namespace App\Notifications;

use App\Models\Coupon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoyaltyCouponNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Coupon $coupon,
        private readonly int $subscriptionCount,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('A loyalty reward just for you — '.config('app.name'))
            ->greeting('Hello '.$notifiable->fullname.',')
            ->line("Thank you for being a valued customer! You've made **{$this->subscriptionCount} subscriptions** with us.")
            ->line('As a thank you, here is your exclusive loyalty discount code:')
            ->line("## `{$this->coupon->code}`")
            ->line("**{$this->coupon->value}% off** your next plan purchase.")
            ->line('Valid until **'.$this->coupon->expires_at?->format('d M Y').'** · Single use.')
            ->action('Browse Plans', url('/portal'))
            ->line('Thank you for choosing '.config('app.name').'!');
    }
}
