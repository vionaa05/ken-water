<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('order_date');
            $table->integer('gallon_qty');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->enum('delivery_method', ['antar', 'ambil_sendiri']);
            $table->text('delivery_address')->nullable();
            $table->enum('payment_method', ['tunai', 'transfer'])->default('tunai');
            $table->text('notes')->nullable();
            $table->enum('status', ['dipesan', 'diproses', 'diantar', 'siap_diambil', 'selesai', 'dibatalkan'])->default('dipesan');
            $table->enum('source', ['online', 'langsung'])->default('online'); // online=portal, langsung=input staf
            $table->timestamp('period_start')->nullable(); // snapshot current_period_start saat order dibuat
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
