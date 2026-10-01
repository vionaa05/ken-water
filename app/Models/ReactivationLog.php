<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReactivationLog extends Model
{
    protected $fillable = [
        'user_id', 'reactivated_at', 'points_before', 'level_before',
        'transactions_count_before', 'vouchers_expired_count',
        'promos_expired_count', 'redemptions_cancelled_count',
    ];

    protected function casts(): array
    {
        return ['reactivated_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
