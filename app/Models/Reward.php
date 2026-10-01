<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    protected $fillable = ['name', 'description', 'points_cost', 'stock', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function redemptions()
    {
        return $this->hasMany(RewardRedemption::class);
    }

    public function isAvailable(): bool
    {
        return $this->is_active && ($this->stock === null || $this->stock > 0);
    }
}
