<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $medicationName,
        public int $currentStock,
        public int $threshold,
        public ?string $pharmacyName = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $pharmacyInfo = $this->pharmacyName ? " at {$this->pharmacyName}" : '';

        return [
            'title' => 'Low Stock Alert',
            'message' => "Medication '{$this->medicationName}'{$pharmacyInfo} is running low ({$this->currentStock} remaining, threshold: {$this->threshold}).",
            'type' => 'STOCK',
            'medication_name' => $this->medicationName,
            'current_stock' => $this->currentStock,
            'threshold' => $this->threshold,
        ];
    }
}
