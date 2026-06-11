<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Services\NotificationService;

class SendOrderStatusNotification
{
    public function handle(OrderStatusChanged $event): void
    {
        NotificationService::orderStatusChanged(
            $event->order,
            $event->oldStatus,
            $event->newStatus
        );
    }
}
