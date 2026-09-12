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
        Schema::create('import_batch_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_batch_id')->constrained('import_batches')->onDelete('cascade');
            $table->integer('row_number');
            $table->enum('classification', ['NEW', 'EXACT_DUPLICATE', 'POSSIBLE_DUPLICATE', 'ERROR']);
            $table->string('nama_biro')->nullable();
            $table->string('program_studi_biro')->nullable();
            $table->date('tanggal_jadwal')->nullable();
            $table->string('sesi_jadwal')->nullable();
            $table->string('waktu_jadwal')->nullable();
            $table->foreignId('existing_maba_data_id')->nullable()->constrained('maba_datas')->nullOnDelete();
            $table->text('error_messages')->nullable();
            $table->enum('selected_action', ['CREATE_NEW', 'UPDATE_JADWAL', 'SKIP'])->nullable();
            $table->timestamps();

            $table->index('import_batch_id');
            $table->index('classification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_batch_rows');
    }
};
