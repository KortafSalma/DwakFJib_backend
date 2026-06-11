<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    const ROLE_ADMIN = 'ADMIN';
    const ROLE_PHARMACY = 'PHARMACY';
    const ROLE_DISTRIBUTOR = 'DISTRIBUTOR';
    const ROLE_USER = 'USER';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'banned_at',
        'ban_reason',
        'photo',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'banned_at' => 'datetime',
        ];
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function pharmacy()
    {
        return $this->hasOne(Pharmacy::class);
    }

    public function distributor()
    {
        return $this->hasOne(Distributor::class);
    }

    public function medicalCertificates()
    {
        return $this->hasMany(MedicalCertificate::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritePharmacies()
    {
        return $this->belongsToMany(Pharmacy::class, 'favorites');
    }

    public function notificationPreference()
    {
        return $this->hasOne(NotificationPreference::class);
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function conversationParticipants()
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isPharmacy(): bool
    {
        return $this->role === self::ROLE_PHARMACY;
    }

    public function isDistributor(): bool
    {
        return $this->role === self::ROLE_DISTRIBUTOR;
    }

    public function isBanned(): bool
    {
        return $this->banned_at !== null;
    }
}
