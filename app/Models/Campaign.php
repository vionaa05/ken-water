<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = [
        'name', 'type', 'target_segment', 'period_start', 'period_end',
        'message_template', 'voucher_id', 'created_by', 'target_count',
    ];

    protected function casts(): array
    {
        return [
            'target_segment' => 'array',
            'period_start' => 'datetime',
            'period_end' => 'datetime',
        ];
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'promo_baru' => 'Promo Pelanggan Baru',
            'promo_loyal' => 'Promo Pelanggan Loyal',
            'ulang_tahun' => 'Promo Ulang Tahun',
            'ajakan_kembali' => 'Ajakan Kembali',
            default => ucfirst($this->type),
        ];
    }

    public function isActive(): bool
    {
        return now()->between($this->period_start, $this->period_end);
    }
}
