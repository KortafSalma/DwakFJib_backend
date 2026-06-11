<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'type' => $this->type,
            'created_by' => $this->created_by,
            'participants' => UserResource::collection($this->whenLoaded('users')),
            'last_message' => new MessageResource($this->whenLoaded('lastMessage')),
            'unread_count' => $this->when($request->user(), function () use ($request) {
                return $this->unreadCountForUser($request->user());
            }, 0),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
