<?php

namespace App\Listeners;

use App\Events\LowStockAlert;
use App\Services\NotificationService;
use App\Models\User;

class SendLowStockNotification
{
    public function handle(LowStockAlert $event): void
    {
        $pharmacyUser = $event->medication->pharmacy?->user;

        if ($pharmacyUser) {
            NotificationService::sendToUser(
                $pharmacyUser,
                'Low Stock Alert',
                "Medication '{$event->medication->name}' is running low ({$event->currentStock} remaining).",
                'STOCK',
                ['medication_id' => $event->medication->id, 'threshold' => $event->threshold]
            );
        }

        NotificationService::sendToAdmins(
            'Low Stock Alert',
            "Medication '{$event->medication->name}' at {$event->medication->pharmacy?->name} has low stock ({$event->currentStock}).",
            ['medication_id' => $event->medication->id, 'pharmacy_id' => $event->medication->pharmacy_id]
        );
    }
}
