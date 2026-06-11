<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pharmacy extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'phone',
        'address',
        'city',
        'latitude',
        'longitude',
        'is_verified',
        'rating',
        'total_reviews',
        'opening_time',
        'closing_time',
        'operating_days',
        'logo',
        'authorization_type',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'rating' => 'decimal:2',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'opening_time' => 'datetime:H:i',
            'closing_time' => 'datetime:H:i',
            'operating_days' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function medications()
    {
        return $this->hasMany(Medication::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function getDistanceAttribute($latitude, $longitude)
    {
        $earthRadius = 6371;

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeNearby($query, $latitude, $longitude, $radius = 50)
    {
        return $query
            ->selectRaw("*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) as distance",
                [$latitude, $longitude, $latitude])
            ->having('distance', '<=', $radius)
            ->orderBy('distance');
    }

    public function isOpenNow(): bool
    {
        if (!$this->opening_time || !$this->closing_time) {
            return true;
        }

        $now = now()->format('H:i');
        return $now >= $this->opening_time && $now <= $this->closing_time;
    }
}
