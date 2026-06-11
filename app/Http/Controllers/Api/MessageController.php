<?php

namespace App\Http\Controllers\Api;

use App\Models\Conversation;
use App\Models\Message;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function index(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $messages = $conversation->messages()
            ->with(['sender', 'attachments'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return MessageResource::collection($messages);
    }

    public function store(StoreMessageRequest $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $user = Auth::user();

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'body' => $request->body ?? '',
            'status' => 'sent',
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('message-attachments', 'public');

                $message->attachments()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        $conversation->touch();

        $message->load(['sender', 'attachments']);

        event(new \App\Events\MessageSent($message, $conversation));

        AuditService::logCreated($message);

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => new MessageResource($message),
        ], 201);
    }

    public function markAsRead(Message $message)
    {
        $this->authorize('update', $message);

        $message->markAsRead();

        return response()->json([
            'message' => 'Message marked as read',
            'data' => new MessageResource($message),
        ]);
    }

    public function destroy(Message $message)
    {
        $this->authorize('delete', $message);

        $message->delete();

        return response()->json([
            'message' => 'Message deleted successfully',
        ]);
    }
}
