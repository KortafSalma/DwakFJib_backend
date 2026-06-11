<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MessageAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
