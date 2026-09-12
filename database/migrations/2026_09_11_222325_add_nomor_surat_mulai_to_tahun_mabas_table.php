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
        Schema::table('tahun_mabas', function (Blueprint $table) {
            $table->unsignedInteger('nomor_surat_mulai')->default(172)->after('nama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tahun_mabas', function (Blueprint $table) {
            $table->dropColumn('nomor_surat_mulai');
        });
    }
};
