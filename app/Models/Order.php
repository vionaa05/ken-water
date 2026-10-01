<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'order_date', 'gallon_qty', 'unit_price', 'total_price',
        'delivery_method', 'delivery_address', 'payment_method', 'notes',
        'status', 'source', 'period_start', 'processed_by', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'datetime',
            'period_start' => 'datetime',
            'completed_at' => 'datetime',
            'unit_price' => 'float',
            'total_price' => 'float',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'dipesan' => 'Dipesan',
            'diproses' => 'Diproses',
            'diantar' => 'Sedang Diantar',
            'siap_diambil' => 'Siap Diambil',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'dipesan' => 'yellow',
            'diproses' => 'blue',
            'diantar' => 'indigo',
            'siap_diambil' => 'purple',
            'selesai' => 'green',
            'dibatalkan' => 'red',
            default => 'gray',
        };
    }

    public function getDeliveryMethodLabelAttribute(): string
    {
        return $this->delivery_method === 'antar' ? 'Antar ke Rumah' : 'Ambil Sendiri';
    }

    public function isActive(): bool
    {
        return !in_array($this->status, ['selesai', 'dibatalkan']);
    }
}
