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
        Schema::table('pemeriksaans', function (Blueprint $table) {
            $table->string('nomor_surat')->nullable()->after('id');
            $table->integer('umur')->nullable()->after('nik');
            $table->string('agama')->nullable()->after('jenis_kelamin');
            $table->string('fakultas_pekerjaan')->nullable()->after('agama');
            $table->text('alamat')->nullable()->after('fakultas_pekerjaan');
            $table->string('keperluan')->nullable()->after('kesimpulan');
            $table->string('riwayat_penyakit_kronis')->nullable()->after('buta_warna');
            $table->string('riwayat_penggunaan_obat')->nullable()->after('riwayat_penyakit_kronis');
            $table->string('riwayat_alergi')->nullable()->after('riwayat_penggunaan_obat');
            $table->string('dokter_nama')->nullable()->after('keperluan');
            $table->dropColumn('riwayat_penyakit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pemeriksaans', function (Blueprint $table) {
            $table->dropColumn([
                'nomor_surat', 'umur', 'agama', 'fakultas_pekerjaan', 'alamat', 
                'keperluan', 'riwayat_penyakit_kronis', 'riwayat_penggunaan_obat', 
                'riwayat_alergi', 'dokter_nama'
            ]);
            $table->text('riwayat_penyakit')->nullable();
        });
    }
};
