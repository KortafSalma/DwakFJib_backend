<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public static function sendToUser(User $user, string $title, string $message, string $type = 'ALERT', array $metadata = [], string $channel = 'in_app')
    {
        return Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'channel' => $channel,
            'metadata' => $metadata,
        ]);
    }

    public static function sendToRole(string $role, string $title, string $message, string $type = 'ALERT', array $metadata = [])
    {
        $users = User::where('role', $role)->get();

        $notifications = [];
        foreach ($users as $user) {
            $notifications[] = self::sendToUser($user, $title, $message, $type, $metadata);
        }

        return $notifications;
    }

    public static function sendToAdmins(string $title, string $message, array $metadata = [])
    {
        return self::sendToRole(User::ROLE_ADMIN, $title, $message, 'ALERT', $metadata);
    }

    public static function reservationCreated($reservation)
    {
        self::sendToUser(
            $reservation->user,
            'Reservation Confirmed',
            "Your reservation for {$reservation->medication->name} (Qty: {$reservation->quantity}) has been created.",
            'RESERVATION',
            ['reservation_id' => $reservation->id]
        );
    }

    public static function reservationApproved($reservation)
    {
        self::sendToUser(
            $reservation->user,
            'Reservation Approved',
            "Your reservation for {$reservation->medication->name} has been approved.",
            'RESERVATION',
            ['reservation_id' => $reservation->id]
        );
    }

    public static function orderStatusChanged($order, string $oldStatus, string $newStatus)
    {
        $pharmacyUser = $order->pharmacy->user;
        $distributorUser = $order->distributor->user;

        if ($pharmacyUser) {
            self::sendToUser(
                $pharmacyUser,
                'Order Status Updated',
                "Order {$order->order_number} changed from {$oldStatus} to {$newStatus}.",
                'ORDER',
                ['order_id' => $order->id]
            );
        }

        if ($distributorUser) {
            self::sendToUser(
                $distributorUser,
                'Order Status Updated',
                "Order {$order->order_number} changed from {$oldStatus} to {$newStatus}.",
                'ORDER',
                ['order_id' => $order->id]
            );
        }
    }
}
