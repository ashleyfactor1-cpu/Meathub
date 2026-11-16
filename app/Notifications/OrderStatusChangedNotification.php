<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class OrderStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $orderNumber,
        public readonly string $previousStatus,
        public readonly string $status,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'format' => 'filament',
            'title' => 'Order status updated',
            'body' => "Your order {$this->orderNumber} has been marked as ".Str::headline($this->status).'.',
            'status' => match ($this->status) {
                'paid', 'completed' => 'success',
                'cancelled' => 'danger',
                default => 'warning',
            },
            'icon' => 'heroicon-o-shopping-bag',
            'order_number' => $this->orderNumber,
            'previous_status' => $this->previousStatus,
            'order_status' => $this->status,
        ];
    }
}
