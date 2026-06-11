<?php

namespace App\Listeners;

use App\Events\ReservationCreated;
use App\Services\NotificationService;

class SendReservationCreatedNotification
{
    public function handle(ReservationCreated $event): void
    {
        NotificationService::reservationCreated($event->reservation);
    }
}
