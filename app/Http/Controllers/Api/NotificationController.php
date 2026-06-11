<?php

namespace App\Http\Controllers\Api;

use App\Models\Notification;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Notification::where('user_id', $user->id)->latest();

        if (request()->has('type')) {
            $query->where('type', request('type'));
        }

        if (request()->has('unread')) {
            $query->unread();
        }

        return NotificationResource::collection($query->paginate(20));
    }

    public function store(StoreNotificationRequest $request)
    {
        $this->authorize('create', Notification::class);

        $notification = Notification::create([
            'user_id' => $request->user_id,
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type ?? 'ALERT',
            'channel' => $request->channel ?? 'in_app',
            'metadata' => $request->metadata,
        ]);

        AuditService::logCreated($notification);

        return response()->json([
            'message' => 'Notification sent successfully',
            'data' => new NotificationResource($notification)
        ], 201);
    }

    public function show(Notification $notification)
    {
        $this->authorize('view', $notification);

        return new NotificationResource($notification);
    }

    public function markAsRead(Notification $notification)
    {
        $this->authorize('update', $notification);

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read',
            'data' => new NotificationResource($notification)
        ]);
    }

    public function markAllAsRead()
    {
        $count = Auth::user()->notifications()->unread()->update(['read_at' => now()]);

        return response()->json([
            'message' => "{$count} notifications marked as read"
        ]);
    }

    public function unread()
    {
        $notifications = Auth::user()
            ->notifications()
            ->unread()
            ->latest()
            ->get();

        return NotificationResource::collection($notifications);
    }

    public function stats()
    {
        $user = Auth::user();

        return response()->json([
            'total' => $user->notifications()->count(),
            'unread' => $user->notifications()->unread()->count(),
            'by_type' => $user->notifications()
                ->selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->get()
                ->pluck('count', 'type'),
        ]);
    }

    public function destroy(Notification $notification)
    {
        $this->authorize('delete', $notification);

        $notification->delete();

        return response()->json([
            'message' => 'Notification deleted successfully'
        ]);
    }
}
