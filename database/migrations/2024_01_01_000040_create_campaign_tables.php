<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['promo_baru', 'promo_loyal', 'ulang_tahun', 'ajakan_kembali']);
            $table->json('target_segment')->nullable(); // segmen target: ['baru','reguler', dll]
            $table->timestamp('period_start');
            $table->timestamp('period_end');
            $table->text('message_template'); // template pesan WhatsApp
            $table->foreignId('voucher_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->integer('target_count')->default(0); // jumlah pelanggan yang ditargetkan
            $table->timestamps();
        });

        Schema::create('reactivation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('reactivated_at');
            $table->integer('points_before')->default(0);
            $table->enum('level_before', ['bronze', 'silver', 'gold'])->default('bronze');
            $table->integer('transactions_count_before')->default(0);
            $table->integer('vouchers_expired_count')->default(0);
            $table->integer('promos_expired_count')->default(0);
            $table->integer('redemptions_cancelled_count')->default(0);
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('body');
            $table->enum('type', ['order_update', 'complaint_update', 'points_update', 'voucher', 'promo', 'system']);
            $table->string('reference_id')->nullable(); // ID order/complaint/dll
            $table->string('reference_type')->nullable(); // 'order', 'complaint', dll
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('reactivation_logs');
        Schema::dropIfExists('campaigns');
    }
};
