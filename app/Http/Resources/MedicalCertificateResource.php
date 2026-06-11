<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalCertificateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'file_path' => $this->file_path,
            'issue_date' => $this->issue_date,
            'expiry_date' => $this->expiry_date,
            'status' => $this->status,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
