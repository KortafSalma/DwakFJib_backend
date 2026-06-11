<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalCertificate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'file_path',
        'issue_date',
        'expiry_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'datetime',
            'expiry_date' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isValid(): bool
    {
        return $this->status === 'VERIFIED'
            && $this->expiry_date
            && $this->expiry_date->isFuture();
    }
}
