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
        if (!Schema::hasIndex('pemeriksaans', 'pemeriksaans_nomor_surat_unique')) {
            Schema::table('pemeriksaans', function (Blueprint $table) {
                $table->unique('nomor_surat', 'pemeriksaans_nomor_surat_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasIndex('pemeriksaans', 'pemeriksaans_nomor_surat_unique')) {
            Schema::table('pemeriksaans', function (Blueprint $table) {
                $table->dropUnique('pemeriksaans_nomor_surat_unique');
            });
        }
    }
};
