<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Voucher extends Model
{
    protected $fillable = [
        'code', 'name', 'description', 'discount_type', 'discount_value',
        'min_purchase', 'valid_from', 'valid_until', 'usage_type',
        'max_usage', 'used_count', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'is_active' => 'boolean',
            'discount_value' => 'float',
            'min_purchase' => 'float',
        ];
    }

    public function userVouchers()
    {
        return $this->hasMany(UserVoucher::class);
    }

    public function isValid(): bool
    {
        $now = now();
        return $this->is_active
            && $now->between($this->valid_from, $this->valid_until)
            && ($this->max_usage === null || $this->used_count < $this->max_usage);
    }

    public function getDiscountLabelAttribute(): string
    {
        if ($this->discount_type === 'nominal') {
            return 'Rp ' . number_format($this->discount_value, 0, ',', '.');
        }
        return $this->discount_value . '%';
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->discount_type === 'nominal') {
            return min($this->discount_value, $subtotal);
        }
        return round($subtotal * ($this->discount_value / 100));
    }
}
