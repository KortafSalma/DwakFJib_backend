<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Models\Notification;
use Illuminate\Console\Command;

class ExpireReservations extends Command
{
    protected $signature = 'reservations:expire';

    protected $description = 'Expire pending reservations that have passed their expiry time';

    public function handle(): int
    {
        $expired = Reservation::expired()->get();

        foreach ($expired as $reservation) {
            $reservation->markAsExpired();

            Notification::create([
                'user_id' => $reservation->user_id,
                'title' => 'Reservation Expired',
                'message' => "Your reservation for {$reservation->medication->name} has expired.",
                'type' => 'RESERVATION',
                'metadata' => ['reservation_id' => $reservation->id],
            ]);
        }

        $this->info("Expired {$expired->count()} reservations.");

        return Command::SUCCESS;
    }
}
