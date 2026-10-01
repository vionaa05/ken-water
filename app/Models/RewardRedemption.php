<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardRedemption extends Model
{
    protected $fillable = [
        'user_id', 'reward_id', 'points_used', 'redemption_code',
        'status', 'validated_by', 'validated_at', 'period_start',
    ];

    protected function casts(): array
    {
        return [
            'validated_at' => 'datetime',
            'period_start' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Menunggu Validasi',
            'validated' => 'Sudah Divalidasi',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }
}
