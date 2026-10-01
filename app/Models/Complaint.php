<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = [
        'user_id', 'category', 'description', 'status',
        'response', 'action_taken', 'handled_by', 'resolved_at',
    ];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'baru' => 'Baru',
            'diproses' => 'Sedang Diproses',
            'selesai' => 'Selesai',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'baru' => 'red',
            'diproses' => 'yellow',
            'selesai' => 'green',
            default => 'gray',
        };
    }
}
