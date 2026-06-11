<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender' => new UserResource($this->whenLoaded('sender')),
            'sender_id' => $this->sender_id,
            'body' => $this->body,
            'status' => $this->status,
            'read_at' => $this->read_at,
            'is_read' => $this->read_at !== null,
            'attachments' => MessageAttachmentResource::collection($this->whenLoaded('attachments')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
