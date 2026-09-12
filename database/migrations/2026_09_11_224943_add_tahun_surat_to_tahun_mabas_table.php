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
            $table->unsignedInteger('tahun_surat')->nullable()->after('kode_bagian');
        });

        // Backfill existing rows so tahun_surat matches their tahun
        \Illuminate\Support\Facades\DB::table('tahun_mabas')->get()->each(function ($row) {
            \Illuminate\Support\Facades\DB::table('tahun_mabas')
                ->where('id', $row->id)
                ->update(['tahun_surat' => $row->tahun]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tahun_mabas', function (Blueprint $table) {
            $table->dropColumn('tahun_surat');
        });
    }
};
