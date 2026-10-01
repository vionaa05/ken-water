<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointMutation extends Model
{
    protected $fillable = [
        'user_id', 'type', 'amount', 'balance_after', 'description',
        'reference_id', 'reference_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'tambah' => '+ Poin',
            'kurang' => '- Poin',
            'reset' => 'Reset',
            default => $this->type,
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'tambah' => 'green',
            'kurang' => 'red',
            'reset' => 'gray',
            default => 'gray',
        };
    }
}
