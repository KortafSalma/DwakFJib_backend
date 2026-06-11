<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class GenericNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public string $type = 'ALERT',
        public array $metadata = []
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title)
            ->line($this->message)
            ->action('View Dashboard', url('/dashboard'));
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'metadata' => $this->metadata,
        ]);
    }
}
