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
        if (!Schema::hasTable('fakultas')) {
            Schema::create('fakultas', function (Blueprint $table) {
                $table->id();
                $table->string('nama');
                $table->string('kode', 20)->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('program_studi')) {
            Schema::create('program_studi', function (Blueprint $table) {
                $table->id();
                $table->foreignId('fakultas_id')->constrained('fakultas')->onDelete('restrict');
                $table->string('nama');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['fakultas_id', 'nama']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_studi');
        Schema::dropIfExists('fakultas');
    }
};
