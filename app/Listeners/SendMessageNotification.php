<?php

namespace App\Listeners;

use App\Events\MessageSent;
use App\Services\NotificationService;

class SendMessageNotification
{
    public function handle(MessageSent $event): void
    {
        $message = $event->message;
        $conversation = $event->conversation;

        $participants = $conversation->participants()
            ->where('user_id', '!=', $message->sender_id)
            ->get();

        foreach ($participants as $participant) {
            $senderName = $message->sender->name;
            $subject = $conversation->subject ?? 'New Message';

            NotificationService::sendToUser(
                $participant->user,
                "New message from {$senderName}",
                $subject,
                'MESSAGE',
                [
                    'conversation_id' => $conversation->id,
                    'message_id' => $message->id,
                    'sender_name' => $senderName,
                ]
            );
        }
    }
}
