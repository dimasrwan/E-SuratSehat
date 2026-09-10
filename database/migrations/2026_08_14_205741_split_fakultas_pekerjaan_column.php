<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemeriksaans', function (Blueprint $table) {
            $table->renameColumn('fakultas_pekerjaan', 'fakultas');
        });
        
        Schema::table('pemeriksaans', function (Blueprint $table) {
            $table->string('pekerjaan')->nullable()->after('fakultas');
        });
    }

    public function down(): void
    {
        Schema::table('pemeriksaans', function (Blueprint $table) {
            $table->dropColumn('pekerjaan');
        });
        
        Schema::table('pemeriksaans', function (Blueprint $table) {
            $table->renameColumn('fakultas', 'fakultas_pekerjaan');
        });
    }
};
