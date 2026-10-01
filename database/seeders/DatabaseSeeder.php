<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Order;
use App\Models\PointMutation;
use App\Models\Complaint;
use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Models\Voucher;
use App\Models\UserVoucher;
use App\Models\PersonalPromo;
use App\Models\Campaign;
use App\Models\Setting;
use App\Models\Notification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // =====================
        // 1. PENGATURAN SISTEM
        // =====================
        $settings = [
            ['key' => 'inactive_days', 'value' => '90', 'description' => 'Jumlah hari tanpa transaksi sebelum pelanggan dianggap tidak aktif'],
            ['key' => 'points_per_gallon', 'value' => '1', 'description' => 'Jumlah poin per galon yang dibeli'],
            ['key' => 'price_per_gallon', 'value' => '5000', 'description' => 'Harga per galon (Rupiah)'],
            ['key' => 'silver_threshold', 'value' => '50', 'description' => 'Minimum poin untuk level Silver'],
            ['key' => 'gold_threshold', 'value' => '150', 'description' => 'Minimum poin untuk level Gold'],
        ];

        foreach ($settings as $s) {
            Setting::updateOrCreate(['key' => $s['key']], $s);
        }

        // =====================
        // 2. ADMIN & STAF
        // =====================
        $admin = User::create([
            'name' => 'Admin Ken Water',
            'email' => 'admin@kenwater.id',
            'phone' => '081200000001',
            'address' => 'Jl. Depot Utama No. 1',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'aktif',
            'registered_at' => Carbon::now()->subYears(2),
        ]);

        $staf = User::create([
            'name' => 'Budi Santoso',
            'email' => 'staf@kenwater.id',
            'phone' => '081200000002',
            'address' => 'Jl. Karyawan No. 5',
            'password' => Hash::make('password'),
            'role' => 'staf',
            'status' => 'aktif',
            'registered_at' => Carbon::now()->subYear(),
        ]);

        // =====================
        // 3. PELANGGAN AKTIF GOLD (banyak transaksi)
        // =====================
        $now = Carbon::now();
        $periodStart = Carbon::now()->subMonths(8);

        $pelanggan1 = User::create([
            'name' => 'Siti Rahayu',
            'email' => null,
            'phone' => '08123456789',
            'address' => 'Jl. Merdeka No. 10, RT 03/RW 02',
            'birth_date' => '1990-10-15',
            'password' => Hash::make('password'),
            'role' => 'pelanggan',
            'customer_id' => 'KW-001',
            'status' => 'aktif',
            'loyalty_level' => 'gold',
            'points' => 180,
            'current_period_start' => $periodStart,
            'registered_at' => $periodStart,
        ]);

        // Buat order historis untuk pelanggan 1
        $this->createOrders($pelanggan1, $staf->id, 18, $periodStart, 5000);

        // =====================
        // 4. PELANGGAN AKTIF SILVER
        // =====================
        $periodStart2 = Carbon::now()->subMonths(4);
        $pelanggan2 = User::create([
            'name' => 'Andi Wijaya',
            'email' => null,
            'phone' => '08234567890',
            'address' => 'Jl. Sudirman No. 25',
            'birth_date' => '1985-03-22',
            'password' => Hash::make('password'),
            'role' => 'pelanggan',
            'customer_id' => 'KW-002',
            'status' => 'aktif',
            'loyalty_level' => 'silver',
            'points' => 65,
            'current_period_start' => $periodStart2,
            'registered_at' => $periodStart2,
        ]);

        $this->createOrders($pelanggan2, $staf->id, 7, $periodStart2, 5000);

        // =====================
        // 5. PELANGGAN AKTIF BRONZE (baru)
        // =====================
        $periodStart3 = Carbon::now()->subDays(15);
        $pelanggan3 = User::create([
            'name' => 'Dewi Lestari',
            'email' => null,
            'phone' => '08345678901',
            'address' => 'Jl. Kenanga No. 7',
            'birth_date' => '1995-07-08',
            'password' => Hash::make('password'),
            'role' => 'pelanggan',
            'customer_id' => 'KW-003',
            'status' => 'aktif',
            'loyalty_level' => 'bronze',
            'points' => 3,
            'current_period_start' => $periodStart3,
            'registered_at' => $periodStart3,
        ]);

        $this->createOrders($pelanggan3, $staf->id, 1, $periodStart3, 5000);

        // =====================
        // 6. PELANGGAN TIDAK AKTIF (untuk uji reaktivasi)
        // =====================
        $periodStartInactive = Carbon::now()->subMonths(5);
        $pelangganInaktif = User::create([
            'name' => 'Roni Susanto',
            'email' => null,
            'phone' => '08456789012',
            'address' => 'Jl. Pahlawan No. 3',
            'birth_date' => '1988-12-01',
            'password' => Hash::make('password'),
            'role' => 'pelanggan',
            'customer_id' => 'KW-004',
            'status' => 'tidak_aktif',
            'loyalty_level' => 'bronze',
            'points' => 25,
            'current_period_start' => $periodStartInactive,
            'last_reactivation_at' => null,
            'reactivation_count' => 0,
            'registered_at' => $periodStartInactive,
        ]);

        // Order terakhir 100 hari lalu (sudah melewati batas 90 hari)
        Order::create([
            'user_id' => $pelangganInaktif->id,
            'order_date' => Carbon::now()->subDays(100),
            'gallon_qty' => 3,
            'unit_price' => 5000,
            'total_price' => 15000,
            'delivery_method' => 'ambil_sendiri',
            'payment_method' => 'tunai',
            'status' => 'selesai',
            'source' => 'langsung',
            'period_start' => $periodStartInactive,
            'processed_by' => $staf->id,
            'completed_at' => Carbon::now()->subDays(100),
        ]);

        // =====================
        // 7. PELANGGAN HAMPIR HILANG (60 hari tidak beli)
        // =====================
        $periodStart5 = Carbon::now()->subMonths(3);
        $pelanggan5 = User::create([
            'name' => 'Hendra Kusuma',
            'email' => null,
            'phone' => '08567890123',
            'address' => 'Jl. Melati No. 12',
            'password' => Hash::make('password'),
            'role' => 'pelanggan',
            'customer_id' => 'KW-005',
            'status' => 'aktif',
            'loyalty_level' => 'bronze',
            'points' => 8,
            'current_period_start' => $periodStart5,
            'registered_at' => $periodStart5,
        ]);

        Order::create([
            'user_id' => $pelanggan5->id,
            'order_date' => Carbon::now()->subDays(62),
            'gallon_qty' => 2,
            'unit_price' => 5000,
            'total_price' => 10000,
            'delivery_method' => 'antar',
            'delivery_address' => 'Jl. Melati No. 12',
            'payment_method' => 'tunai',
            'status' => 'selesai',
            'source' => 'langsung',
            'period_start' => $periodStart5,
            'processed_by' => $staf->id,
            'completed_at' => Carbon::now()->subDays(62),
        ]);

        // =====================
        // 8. REWARD KATALOG
        // =====================
        $reward1 = Reward::create([
            'name' => 'Gratis 1 Galon',
            'description' => 'Tukarkan 30 poin untuk mendapatkan 1 galon air minum gratis',
            'points_cost' => 30,
            'stock' => null,
            'is_active' => true,
        ]);

        $reward2 = Reward::create([
            'name' => 'Diskon 5 Galon',
            'description' => 'Tukarkan 100 poin untuk diskon Rp 10.000 pada pembelian minimum 5 galon',
            'points_cost' => 100,
            'stock' => 50,
            'is_active' => true,
        ]);

        // =====================
        // 9. VOUCHER
        // =====================
        $voucher1 = Voucher::create([
            'code' => 'WELCOME10',
            'name' => 'Welcome Discount 10%',
            'description' => 'Diskon 10% untuk pelanggan baru',
            'discount_type' => 'persen',
            'discount_value' => 10,
            'valid_from' => Carbon::now()->subMonth(),
            'valid_until' => Carbon::now()->addMonths(3),
            'usage_type' => 'sekali',
            'is_active' => true,
        ]);

        $voucher2 = Voucher::create([
            'code' => 'LOYAL5K',
            'name' => 'Diskon Pelanggan Loyal Rp 5.000',
            'description' => 'Potongan Rp 5.000 untuk pelanggan Silver ke atas',
            'discount_type' => 'nominal',
            'discount_value' => 5000,
            'min_purchase' => 25000,
            'valid_from' => Carbon::now()->subDays(10),
            'valid_until' => Carbon::now()->addMonths(2),
            'usage_type' => 'sekali',
            'is_active' => true,
        ]);

        // Berikan voucher ke pelanggan 1 (gold)
        UserVoucher::create([
            'user_id' => $pelanggan1->id,
            'voucher_id' => $voucher2->id,
            'status' => 'aktif',
            'period_start' => $pelanggan1->current_period_start,
        ]);

        // =====================
        // 10. PROMO PERSONAL
        // =====================
        PersonalPromo::create([
            'user_id' => $pelanggan1->id,
            'name' => 'Diskon Ulang Tahun Oktober',
            'description' => 'Selamat ulang tahun! Nikmati diskon 15% untuk pembelian bulan ini.',
            'discount_type' => 'persen',
            'discount_value' => 15,
            'valid_from' => Carbon::now()->startOfMonth(),
            'valid_until' => Carbon::now()->endOfMonth(),
            'is_used' => false,
            'is_expired' => false,
            'period_start' => $pelanggan1->current_period_start,
        ]);

        // =====================
        // 11. KELUHAN CONTOH
        // =====================
        Complaint::create([
            'user_id' => $pelanggan2->id,
            'category' => 'Kualitas Air',
            'description' => 'Air terasa sedikit berbau setelah diisi ulang kemarin.',
            'status' => 'diproses',
            'response' => null,
            'action_taken' => 'Sedang dicek kualitas filter mesin.',
            'handled_by' => $staf->id,
        ]);

        Complaint::create([
            'user_id' => $pelanggan3->id,
            'category' => 'Telat Antar',
            'description' => 'Pesanan sudah 3 jam belum diantar padahal sudah konfirmasi.',
            'status' => 'baru',
        ]);

        Complaint::create([
            'user_id' => $pelanggan1->id,
            'category' => 'Galon Bocor',
            'description' => 'Galon yang diantar kondisinya bocor di bagian bawah.',
            'status' => 'selesai',
            'response' => 'Mohon maaf atas ketidaknyamanannya. Kami telah mengganti galon baru dan memastikan pemeriksaan lebih ketat.',
            'action_taken' => 'Galon diganti baru dan pelanggan mendapat 2 galon gratis sebagai kompensasi.',
            'handled_by' => $staf->id,
            'resolved_at' => Carbon::now()->subDays(5),
        ]);

        // =====================
        // 12. PESANAN MASUK (ONLINE - belum diproses)
        // =====================
        Order::create([
            'user_id' => $pelanggan2->id,
            'order_date' => Carbon::now()->subHours(2),
            'gallon_qty' => 5,
            'unit_price' => 5000,
            'total_price' => 25000,
            'delivery_method' => 'antar',
            'delivery_address' => 'Jl. Sudirman No. 25',
            'payment_method' => 'transfer',
            'notes' => 'Tolong diantar sebelum jam 12 siang ya',
            'status' => 'dipesan',
            'source' => 'online',
            'period_start' => $pelanggan2->current_period_start,
        ]);

        Order::create([
            'user_id' => $pelanggan3->id,
            'order_date' => Carbon::now()->subHour(),
            'gallon_qty' => 3,
            'unit_price' => 5000,
            'total_price' => 15000,
            'delivery_method' => 'ambil_sendiri',
            'payment_method' => 'tunai',
            'status' => 'dipesan',
            'source' => 'online',
            'period_start' => $pelanggan3->current_period_start,
        ]);

        // =====================
        // 13. KAMPANYE CONTOH
        // =====================
        Campaign::create([
            'name' => 'Promo Ultah Oktober 2026',
            'type' => 'ulang_tahun',
            'target_segment' => ['semua'],
            'period_start' => Carbon::now()->startOfMonth(),
            'period_end' => Carbon::now()->endOfMonth(),
            'message_template' => "Halo {nama}! 🎂\n\nSelamat ulang tahun dari Ken Water!\n\nSebagai hadiah spesial, kami memberikan diskon 15% untuk pembelian air minum bulan ini.\n\nGunakan kode promo: ULTAH15\n\nTerima kasih sudah menjadi pelanggan setia kami! 💧",
            'created_by' => $admin->id,
            'target_count' => 3,
        ]);

        Campaign::create([
            'name' => 'Ajakan Kembali - Tidak Aktif',
            'type' => 'ajakan_kembali',
            'target_segment' => ['tidak_aktif'],
            'period_start' => Carbon::now(),
            'period_end' => Carbon::now()->addMonths(1),
            'message_template' => "Halo {nama}! 👋\n\nKami kangen kamu di Ken Water! 😊\n\nSudah lama kamu tidak memesan air minum. Yuk kembali dan nikmati air segar berkualitas dari kami!\n\nReaktivasi akun kamu sekarang dan dapatkan GRATIS 1 galon untuk pembelian pertama!\n\nKunjungi: {link_portal}\n\nSalam hangat,\nTim Ken Water 💧",
            'voucher_id' => $voucher1->id,
            'created_by' => $admin->id,
            'target_count' => 1,
        ]);

        // =====================
        // 14. NOTIFIKASI CONTOH
        // =====================
        Notification::create([
            'user_id' => $pelanggan1->id,
            'title' => '🥇 Selamat! Level Anda Gold!',
            'body' => 'Anda telah mencapai level Gold. Nikmati berbagai keuntungan eksklusif!',
            'type' => 'points_update',
            'is_read' => true,
        ]);

        Notification::create([
            'user_id' => $pelanggan2->id,
            'title' => 'Status Pesanan Diperbarui',
            'body' => 'Pesanan Anda sedang dalam proses pengiriman.',
            'type' => 'order_update',
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $pelanggan3->id,
            'title' => 'Keluhan Diterima',
            'body' => 'Keluhan Anda telah diterima dan sedang ditangani oleh tim kami.',
            'type' => 'complaint_update',
            'is_read' => false,
        ]);
    }

    /**
     * Buat sejumlah order selesai untuk pelanggan (untuk mengisi data historis)
     */
    private function createOrders(User $user, int $staffId, int $count, Carbon $periodStart, float $unitPrice): void
    {
        for ($i = 0; $i < $count; $i++) {
            $daysAgo = (int)(($count - $i) * (180 / $count));
            $orderDate = Carbon::now()->subDays($daysAgo);
            $gallons = rand(2, 5);

            Order::create([
                'user_id' => $user->id,
                'order_date' => $orderDate,
                'gallon_qty' => $gallons,
                'unit_price' => $unitPrice,
                'total_price' => $gallons * $unitPrice,
                'delivery_method' => $i % 2 === 0 ? 'antar' : 'ambil_sendiri',
                'delivery_address' => $i % 2 === 0 ? $user->address : null,
                'payment_method' => $i % 3 === 0 ? 'transfer' : 'tunai',
                'status' => 'selesai',
                'source' => 'langsung',
                'period_start' => $periodStart,
                'processed_by' => $staffId,
                'completed_at' => $orderDate->copy()->addHours(2),
            ]);
        }
    }
}
