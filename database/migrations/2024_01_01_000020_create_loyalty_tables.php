<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['tambah', 'kurang', 'reset']);
            $table->integer('amount'); // selalu positif, tipe menentukan arah
            $table->integer('balance_after'); // saldo poin setelah mutasi (audit trail)
            $table->string('description');
            $table->nullableMorphs('reference'); // bisa FK ke order, reward_redemption, dll
            $table->timestamps();
        });

        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('category'); // galon bocor, telat antar, kualitas air, dll
            $table->text('description');
            $table->enum('status', ['baru', 'diproses', 'selesai'])->default('baru');
            $table->text('response')->nullable(); // tanggapan staf untuk pelanggan
            $table->text('action_taken')->nullable(); // tindakan internal staf
            $table->foreignId('handled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('points_cost');
            $table->integer('stock')->nullable(); // null = unlimited
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('reward_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('reward_id')->constrained()->onDelete('cascade');
            $table->integer('points_used');
            $table->string('redemption_code', 20)->unique();
            $table->enum('status', ['pending', 'validated', 'cancelled'])->default('pending');
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('period_start')->nullable(); // snapshot period saat penukaran
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_redemptions');
        Schema::dropIfExists('rewards');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('point_mutations');
    }
};
