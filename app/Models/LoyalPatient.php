<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoyalPatient extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_id',
        'user_id',
        'total_visits',
        'total_spent',
        'loyalty_points',
        'tier',
        'last_purchase_at',
    ];

    protected function casts(): array
    {
        return [
            'total_spent' => 'decimal:2',
            'last_purchase_at' => 'datetime',
        ];
    }

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByTier($query, $tier)
    {
        return $query->where('tier', $tier);
    }

    public function scopeTopSpenders($query, $limit = 10)
    {
        return $query->orderBy('total_spent', 'desc')->limit($limit);
    }

    public static function calculateTier(int $points): string
    {
        return match (true) {
            $points >= 5000 => 'Platine',
            $points >= 2000 => 'Or',
            $points >= 1000 => 'Argent',
            default => 'Bronze',
        };
    }
}
