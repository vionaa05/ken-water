<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('discount_type', ['nominal', 'persen']);
            $table->decimal('discount_value', 10, 2);
            $table->decimal('min_purchase', 10, 2)->nullable();
            $table->timestamp('valid_from');
            $table->timestamp('valid_until');
            $table->enum('usage_type', ['sekali', 'banyak'])->default('sekali');
            $table->integer('max_usage')->nullable();
            $table->integer('used_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('user_vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('voucher_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['aktif', 'terpakai', 'hangus'])->default('aktif');
            $table->timestamp('used_at')->nullable();
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('period_start')->nullable(); // snapshot period saat voucher diberikan
            $table->timestamps();
        });

        Schema::create('personal_promos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('discount_type', ['nominal', 'persen']);
            $table->decimal('discount_value', 10, 2);
            $table->timestamp('valid_from');
            $table->timestamp('valid_until');
            $table->boolean('is_used')->default(false);
            $table->boolean('is_expired')->default(false); // akan di-set true saat reaktivasi
            $table->timestamp('period_start')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_promos');
        Schema::dropIfExists('user_vouchers');
        Schema::dropIfExists('vouchers');
    }
};
