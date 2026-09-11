<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add nullable column with index
        Schema::table('pemeriksaans', function (Blueprint $table) {
            $table->unsignedSmallInteger('tahun_masuk')->nullable()->after('id')->index();
        });

        // Step 2: Safe backfill existing data to 2026
        DB::table('pemeriksaans')->whereNull('tahun_masuk')->update(['tahun_masuk' => 2026]);

        // Step 3: Change column to NOT NULL
        Schema::table('pemeriksaans', function (Blueprint $table) {
            $table->unsignedSmallInteger('tahun_masuk')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pemeriksaans', function (Blueprint $table) {
            $table->dropIndex(['tahun_masuk']);
            $table->dropColumn('tahun_masuk');
        });
    }
};
