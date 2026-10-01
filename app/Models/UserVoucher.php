<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVoucher extends Model
{
    protected $fillable = [
        'user_id', 'voucher_id', 'status', 'used_at', 'order_id', 'period_start',
    ];

    protected function casts(): array
    {
        return [
            'used_at' => 'datetime',
            'period_start' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'aktif' => 'Belum Dipakai',
            'terpakai' => 'Sudah Dipakai',
            'hangus' => 'Hangus',
            default => ucfirst($this->status),
        };
    }
}
