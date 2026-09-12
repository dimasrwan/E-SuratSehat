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
        Schema::create('maba_datas', function (Blueprint $table) {
            $table->id();
            
            // Relasi Ke Master Tahun Maba (Restrict delete agar tidak menghapus ribuan data Maba)
            $table->foreignId('tahun_maba_id')->constrained('tahun_mabas')->onDelete('restrict');
            
            // Identitas Data Biro (Read-only dari Biro)
            $table->string('nama_biro');
            $table->string('program_studi_biro');
            $table->date('tanggal_jadwal')->nullable();
            $table->string('sesi_jadwal')->nullable();
            $table->string('waktu_jadwal')->nullable();

            // Biodata Maba (Diisi Maba / Dikonfirmasi)
            $table->string('nama_lengkap')->nullable();
            $table->string('nik', 16)->nullable();
            $table->string('email')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('agama')->nullable();
            $table->string('fakultas')->nullable();
            $table->string('program_studi')->nullable();
            $table->string('pekerjaan')->nullable()->default('Mahasiswa');
            $table->text('alamat')->nullable();

            // Status Lifecycle Biodata Maba
            $table->enum('status_biodata', [
                'BELUM_MENGISI',
                'MENUNGGU_VERIFIKASI',
                'PERLU_PERBAIKAN',
                'TERVERIFIKASI',
                'PEMERIKSAAN_SELESAI'
            ])->default('BELUM_MENGISI');

            // Verification Timestamp & Verifier Audit Trail
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Indexes untuk kinerja pencarian
            $table->index('tahun_maba_id');
            $table->index('status_biodata');
            $table->index('program_studi_biro');
            $table->index('nama_biro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maba_datas');
    }
};
