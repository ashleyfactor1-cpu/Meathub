<?php

namespace App\Observers;

use App\Models\Order;
use App\Notifications\OrderStatusChangedNotification;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('payment_status') || ! $order->user) {
            return;
        }

        $order->user->notify(new OrderStatusChangedNotification(
            orderNumber: (string) $order->order_number,
            previousStatus: (string) $order->getOriginal('payment_status'),
            status: (string) $order->payment_status,
        ));
    }
}
