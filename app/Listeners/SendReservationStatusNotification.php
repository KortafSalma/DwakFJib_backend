<?php

namespace App\Listeners;

use App\Events\ReservationStatusChanged;
use App\Services\NotificationService;

class SendReservationStatusNotification
{
    public function handle(ReservationStatusChanged $event): void
    {
        if ($event->newStatus === 'CONFIRMED') {
            NotificationService::reservationApproved($event->reservation);
        }
    }
}
