<?php

namespace App\Http\Controllers\Api;

use App\Models\Conversation;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConversationRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = $user->conversations()
            ->with(['participants.user', 'lastMessage.sender'])
            ->withCount(['messages as unread_count' => function ($q) use ($user) {
                $q->where('sender_id', '!=', $user->id)
                  ->whereNull('read_at');
            }])
            ->orderBy('updated_at', 'desc');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhereHas('participants.user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('messages', function ($mq) use ($search) {
                      $mq->where('body', 'like', "%{$search}%");
                  });
            });
        }

        return ConversationResource::collection($query->paginate(20));
    }

    public function store(StoreConversationRequest $request)
    {
        $user = Auth::user();

        $conversation = Conversation::create([
            'subject' => $request->subject,
            'created_by' => $user->id,
            'type' => 'direct',
        ]);

        $participantIds = array_merge(
            [$user->id],
            $request->participants
        );

        foreach (array_unique($participantIds) as $userId) {
            $conversation->participants()->create(['user_id' => $userId]);
        }

        if ($request->filled('message')) {
            $message = $conversation->messages()->create([
                'sender_id' => $user->id,
                'body' => $request->message,
                'status' => 'sent',
            ]);

            event(new \App\Events\MessageSent($message, $conversation));
        }

        $conversation->load(['participants.user', 'lastMessage.sender']);

        AuditService::logCreated($conversation);

        return response()->json([
            'message' => 'Conversation created successfully',
            'data' => new ConversationResource($conversation),
        ], 201);
    }

    public function show(Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $conversation->load(['participants.user', 'messages.sender', 'messages.attachments']);

        return new ConversationResource($conversation);
    }

    public function markAsRead(Conversation $conversation)
    {
        $user = Auth::user();

        $participant = $conversation->participants()
            ->where('user_id', $user->id)
            ->firstOrFail();

        $participant->update(['last_read_at' => now()]);

        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'status' => 'read']);

        return response()->json([
            'message' => 'Conversation marked as read',
        ]);
    }

    public function unreadCount()
    {
        $user = Auth::user();

        $count = $user->conversations()
            ->whereHas('messages', function ($q) use ($user) {
                $q->where('sender_id', '!=', $user->id)
                  ->whereNull('read_at');
            })
            ->count();

        return response()->json([
            'unread_count' => $count,
        ]);
    }
}
