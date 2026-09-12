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
            $table->string('kode_unit', 50)->default('Un.08')->after('nomor_surat_mulai');
            $table->string('kode_bagian', 50)->default('PPKES')->after('kode_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tahun_mabas', function (Blueprint $table) {
            $table->dropColumn(['kode_unit', 'kode_bagian']);
        });
    }
};
