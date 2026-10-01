<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable(); // untuk admin/staf
            $table->string('phone')->unique()->nullable(); // untuk pelanggan (WhatsApp)
            $table->string('address')->nullable();
            $table->date('birth_date')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'staf', 'pelanggan'])->default('pelanggan');
            $table->string('customer_id', 20)->unique()->nullable(); // KW-XXXXXX
            $table->enum('status', ['aktif', 'tidak_aktif'])->default('aktif');
            $table->enum('loyalty_level', ['bronze', 'silver', 'gold'])->default('bronze');
            $table->integer('points')->default(0);
            $table->timestamp('current_period_start')->nullable(); // tgl registrasi / reaktivasi terakhir
            $table->integer('reactivation_count')->default(0);
            $table->timestamp('last_reactivation_at')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
