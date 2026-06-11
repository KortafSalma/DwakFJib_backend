<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'channel',
        'read_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function markAsRead()
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }
}
