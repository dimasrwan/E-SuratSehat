<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pemeriksaans', function (Blueprint $table) {
            $table->string('email')->nullable()->after('nama');
            $table->string('status_pengiriman')->default('Belum dikirim')->after('kesimpulan');
            $table->timestamp('waktu_pengiriman')->nullable()->after('status_pengiriman');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pemeriksaans', function (Blueprint $table) {
            $table->dropColumn(['email', 'status_pengiriman', 'waktu_pengiriman']);
        });
    }
};
