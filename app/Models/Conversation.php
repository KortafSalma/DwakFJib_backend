<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subject',
        'created_by',
        'type',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants()
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latest();
    }

    public function unreadCountForUser(User $user)
    {
        $participant = $this->participants()->where('user_id', $user->id)->first();

        if (!$participant || !$participant->last_read_at) {
            return $this->messages()->count();
        }

        return $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('created_at', '>', $participant->last_read_at)
            ->count();
    }
}
