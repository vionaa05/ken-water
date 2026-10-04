<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'phone', 'address', 'birth_date', 'password',
        'role', 'customer_id', 'status', 'loyalty_level', 'points',
        'current_period_start', 'reactivation_count', 'last_reactivation_at', 'registered_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date' => 'date',
            'current_period_start' => 'datetime',
            'last_reactivation_at' => 'datetime',
            'registered_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // =====================
    // RELATIONSHIPS
    // =====================

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function currentPeriodOrders()
    {
        return $this->hasMany(Order::class)
            ->when($this->current_period_start, function ($q, $start) {
                $q->where('period_start', '>=', $start);
            })
            ->where('status', '!=', 'dibatalkan');
    }

    public function completedOrders()
    {
        return $this->hasMany(Order::class)
            ->when($this->current_period_start, function ($q, $start) {
                $q->where('period_start', '>=', $start);
            })
            ->where('status', 'selesai');
    }

    public function pointMutations()
    {
        return $this->hasMany(PointMutation::class);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function rewardRedemptions()
    {
        return $this->hasMany(RewardRedemption::class);
    }

    public function userVouchers()
    {
        return $this->hasMany(UserVoucher::class);
    }

    public function personalPromos()
    {
        return $this->hasMany(PersonalPromo::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function reactivationLogs()
    {
        return $this->hasMany(ReactivationLog::class);
    }

    // =====================
    // SCOPES
    // =====================

    public function scopeCustomers($query)
    {
        return $query->where('role', 'pelanggan');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'tidak_aktif');
    }

    public function scopeLoyalCustomers($query)
    {
        return $query->where('role', 'pelanggan')
            ->where('status', 'aktif')
            ->whereIn('loyalty_level', ['silver', 'gold']);
    }

    // =====================
    // HELPERS & BUSINESS LOGIC
    // =====================

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaf(): bool
    {
        return $this->role === 'staf';
    }

    public function isPelanggan(): bool
    {
        return $this->role === 'pelanggan';
    }

    public function isActive(): bool
    {
        return $this->status === 'aktif';
    }

    public function isInactive(): bool
    {
        return $this->status === 'tidak_aktif';
    }

    public function isLoyal(): bool
    {
        return $this->status === 'aktif' && in_array($this->loyalty_level, ['silver', 'gold']);
    }

    public function getLoyaltyLabelAttribute(): string
    {
        return match($this->loyalty_level) {
            'bronze' => 'Bronze',
            'silver' => 'Silver',
            'gold' => 'Gold',
            default => 'Bronze',
        };
    }

    public function getLoyaltyColorAttribute(): string
    {
        return match($this->loyalty_level) {
            'bronze' => '#CD7F32',
            'silver' => '#C0C0C0',
            'gold' => '#FFD700',
            default => '#CD7F32',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'aktif' ? 'Aktif' : 'Tidak Aktif';
    }

    /**
     * Hitung segmentasi berdasarkan perilaku (hanya periode akun saat ini)
     */
    public function getSegmentAttribute(): string
    {
        $daysSinceLastOrder = $this->getDaysSinceLastOrder();
        $orderCount = $this->completedOrders()->count();

        $inactiveDays = (int) setting('inactive_days', 90);
        $almostInactiveDays = (int) ($inactiveDays * 0.67); // 2/3 dari batas nonaktif

        if ($orderCount === 0) {
            return 'Baru';
        } elseif ($daysSinceLastOrder >= $almostInactiveDays) {
            return 'Hampir Hilang';
        } elseif ($orderCount >= 10) {
            return 'Sering Beli';
        } else {
            return 'Reguler';
        }
    }

    public function getDaysSinceLastOrder(): int
    {
        $lastOrder = $this->completedOrders()
            ->latest('completed_at')
            ->first();

        if (!$lastOrder) {
            return $this->current_period_start
                ? (int) Carbon::parse($this->current_period_start)->diffInDays(now())
                : 0;
        }

        return (int) Carbon::parse($lastOrder->completed_at)->diffInDays(now());
    }

    /**
     * Hitung RFM (hanya periode akun saat ini)
     */
    public function getCustomerValueAttribute(): string
    {
        $orders = $this->completedOrders();
        $totalSpend = $orders->sum('total_price');
        $frequency = $orders->count();
        $daysSinceLast = $this->getDaysSinceLastOrder();

        $recencyScore = $daysSinceLast <= 7 ? 3 : ($daysSinceLast <= 30 ? 2 : 1);
        $frequencyScore = $frequency >= 10 ? 3 : ($frequency >= 4 ? 2 : 1);
        $monetaryScore = $totalSpend >= 500000 ? 3 : ($totalSpend >= 200000 ? 2 : 1);

        $totalScore = $recencyScore + $frequencyScore + $monetaryScore;

        return $totalScore >= 8 ? 'Tinggi' : ($totalScore >= 5 ? 'Sedang' : 'Rendah');
    }

    public function getTotalSpendCurrentPeriodAttribute(): float
    {
        return (float) $this->completedOrders()->sum('total_price');
    }

    public function getOrderCountCurrentPeriodAttribute(): int
    {
        return $this->completedOrders()->count();
    }

    /**
     * Auto-generate customer ID: KW-XXXXXX
     */
    public static function generateCustomerId(): string
    {
        do {
            $id = 'KW-' . strtoupper(substr(md5(uniqid()), 0, 6));
        } while (self::where('customer_id', $id)->exists());

        return $id;
    }

    /**
     * Cek apakah perlu dinon-aktifkan (tidak ada transaksi selama N hari)
     */
    public function shouldBeDeactivated(): bool
    {
        if (!$this->isPelanggan() || $this->isInactive()) return false;

        $inactiveDays = (int) setting('inactive_days', 90);
        return $this->getDaysSinceLastOrder() >= $inactiveDays;
    }
}
