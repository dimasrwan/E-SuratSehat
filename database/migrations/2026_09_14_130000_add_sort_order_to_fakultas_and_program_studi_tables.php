<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('fakultas', 'sort_order')) {
            Schema::table('fakultas', function (Blueprint $table) {
                $table->integer('sort_order')->default(0)->after('is_active');
            });
        }

        if (!Schema::hasColumn('program_studi', 'sort_order')) {
            Schema::table('program_studi', function (Blueprint $table) {
                $table->integer('sort_order')->default(0)->after('is_active');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('fakultas', 'sort_order')) {
            Schema::table('fakultas', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }

        if (Schema::hasColumn('program_studi', 'sort_order')) {
            Schema::table('program_studi', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};
