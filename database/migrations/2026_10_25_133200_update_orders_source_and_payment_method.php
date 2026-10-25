<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN source VARCHAR(30) NOT NULL DEFAULT 'online'");
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method VARCHAR(30) NOT NULL DEFAULT 'tunai'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN source ENUM('online', 'langsung') NOT NULL DEFAULT 'online'");
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('tunai', 'transfer') NOT NULL DEFAULT 'tunai'");
    }
};
