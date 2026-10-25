<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalPromo extends Model
{
    protected $fillable = [
        'user_id', 'name', 'description', 'discount_type', 'discount_value',
        'valid_from', 'valid_until', 'is_used', 'is_expired', 'period_start',
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'is_used' => 'boolean',
            'is_expired' => 'boolean',
            'period_start' => 'datetime',
            'discount_value' => 'float',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        if ($this->is_used || $this->is_expired) {
            return false;
        }

        $today = now()->format('Y-m-d');
        $start = $this->valid_from ? $this->valid_from->format('Y-m-d') : null;
        $end = $this->valid_until ? $this->valid_until->format('Y-m-d') : null;

        if ($start && $today < $start) {
            return false;
        }
        if ($end && $today > $end) {
            return false;
        }

        return true;
    }
}
