<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ReservationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $action,
        public mixed $reservation,
        public string $medicationName,
        public int $quantity
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $messages = [
            'created' => "Your reservation for {$this->medicationName} (Qty: {$this->quantity}) has been created.",
            'confirmed' => "Your reservation for {$this->medicationName} has been confirmed.",
            'cancelled' => "Your reservation for {$this->medicationName} has been cancelled.",
            'expired' => "Your reservation for {$this->medicationName} has expired.",
        ];

        return [
            'title' => 'Reservation ' . ucfirst($this->action),
            'message' => $messages[$this->action] ?? 'Reservation updated.',
            'type' => 'RESERVATION',
            'reservation_id' => $this->reservation->id ?? null,
        ];
    }
}
