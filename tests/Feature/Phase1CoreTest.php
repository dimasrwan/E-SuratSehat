<?php

namespace Tests\Feature;

use App\Models\MabaData;
use App\Models\Pemeriksaan;
use App\Models\TahunMaba;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\TestCase;

class Phase1CoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed active year if needed
        TahunMaba::firstOrCreate(
            ['tahun' => 2026],
            [
                'nama' => 'Maba 2026',
                'nomor_surat_mulai' => 172,
                'kode_unit' => 'Un.08',
                'kode_bagian' => 'PPKES',
                'tahun_surat' => 2026,
                'is_active' => true,
            ]
        );
    }

    public function test_maba_data_dapat_dibuat_dengan_status_default_belum_mengisi()
    {
        $tahunMaba = TahunMaba::where('tahun', 2026)->first();

        $maba = MabaData::create([
            'tahun_maba_id' => $tahunMaba->id,
            'nama_biro' => 'Ahmad Fauzan',
            'program_studi_biro' => 'Teknik Informatika',
            'tanggal_jadwal' => '2026-09-15',
            'sesi_jadwal' => 'Sesi 1',
            'waktu_jadwal' => '08:00 - 10:00 WIB',
        ]);

        $this->assertDatabaseHas('maba_datas', [
            'id' => $maba->id,
            'nama_biro' => 'Ahmad Fauzan',
            'status_biodata' => 'BELUM_MENGISI',
        ]);
        $this->assertEquals('BELUM_MENGISI', $maba->status_biodata);
    }

    public function test_maba_data_terhubung_ke_tahun_maba()
    {
        $tahunMaba = TahunMaba::where('tahun', 2026)->first();

        $maba = MabaData::create([
            'tahun_maba_id' => $tahunMaba->id,
            'nama_biro' => 'Budi Santoso',
            'program_studi_biro' => 'Sistem Informasi',
        ]);

        $this->assertNotNull($maba->tahunMaba);
        $this->assertEquals(2026, $maba->tahunMaba->tahun);
        $this->assertTrue($tahunMaba->mabaDatas->contains($maba));
    }

    public function test_maba_data_dan_pemeriksaan_dapat_saling_terhubung()
    {
        $tahunMaba = TahunMaba::where('tahun', 2026)->first();

        $maba = MabaData::create([
            'tahun_maba_id' => $tahunMaba->id,
            'nama_biro' => 'Citra Dewi',
            'program_studi_biro' => 'Kedokteran',
            'nama_lengkap' => 'Citra Dewi Lestari',
            'email' => 'citra@example.com',
            'status_biodata' => 'TERVERIFIKASI',
        ]);

        $pemeriksaan = Pemeriksaan::create([
            'maba_data_id' => $maba->id,
            'tahun_masuk' => 2026,
            'nomor_surat' => '0172/Un.08/PPKES/09/2026',
            'nama' => $maba->nama_lengkap,
            'email' => $maba->email,
            'golongan_darah' => 'O',
            'tinggi_badan' => '165',
            'berat_badan' => '55',
            'tekanan_darah' => '120/80',
            'buta_warna' => 'Tidak',
            'kesimpulan' => 'Sehat',
            'keperluan' => 'Pendaftaran Ulang',
            'dokter_nama' => 'dr. John Doe',
        ]);

        $this->assertNotNull($pemeriksaan->mabaData);
        $this->assertEquals('Citra Dewi Lestari', $pemeriksaan->nama);
        $this->assertEquals('citra@example.com', $pemeriksaan->email);

        $this->assertNotNull($maba->pemeriksaan);
        $this->assertEquals('0172/Un.08/PPKES/09/2026', $maba->pemeriksaan->nomor_surat);
    }

    public function test_pemeriksaan_legacy_dengan_maba_data_id_null_tetap_valid_dan_berfungsi()
    {
        $pemeriksaanLegacy = Pemeriksaan::create([
            'maba_data_id' => null,
            'tahun_masuk' => 2026,
            'nomor_surat' => '0173/Un.08/PPKES/09/2026',
            'nama' => 'Mahasiswa Legacy',
            'email' => 'legacy@example.com',
            'nik' => '3201123456789012',
            'golongan_darah' => 'A',
            'tinggi_badan' => '170',
            'berat_badan' => '60',
            'tekanan_darah' => '110/70',
            'buta_warna' => 'Tidak',
            'kesimpulan' => 'Sehat',
            'keperluan' => 'Registrasi',
            'dokter_nama' => 'dr. Jane Doe',
        ]);

        $this->assertNull($pemeriksaanLegacy->maba_data_id);
        $this->assertNull($pemeriksaanLegacy->mabaData);
        $this->assertEquals('Mahasiswa Legacy', $pemeriksaanLegacy->nama);
        $this->assertEquals('legacy@example.com', $pemeriksaanLegacy->email);
        $this->assertEquals('3201123456789012', $pemeriksaanLegacy->nik);
    }

    public function test_seluruh_status_lifecycle_biodata_maba_valid()
    {
        $tahunMaba = TahunMaba::where('tahun', 2026)->first();
        $operator = User::factory()->create(['role' => 'operator']);

        $statuses = [
            'BELUM_MENGISI',
            'MENUNGGU_VERIFIKASI',
            'PERLU_PERBAIKAN',
            'TERVERIFIKASI',
            'PEMERIKSAAN_SELESAI',
        ];

        foreach ($statuses as $status) {
            $maba = MabaData::create([
                'tahun_maba_id' => $tahunMaba->id,
                'nama_biro' => 'Test Status ' . $status,
                'program_studi_biro' => 'Informatika',
                'status_biodata' => $status,
                'verified_at' => $status === 'TERVERIFIKASI' ? now() : null,
                'verified_by' => $status === 'TERVERIFIKASI' ? $operator->id : null,
            ]);

            $this->assertEquals($status, $maba->status_biodata);
        }
    }

    public function test_dua_maba_dengan_nama_dan_prodi_sama_dapat_dibuat_tanpa_unique_constraint_error()
    {
        $tahunMaba = TahunMaba::where('tahun', 2026)->first();

        $maba1 = MabaData::create([
            'tahun_maba_id' => $tahunMaba->id,
            'nama_biro' => 'Ahmad Fauzan',
            'program_studi_biro' => 'Teknik Informatika',
            'tanggal_jadwal' => '2026-09-15',
            'sesi_jadwal' => 'Sesi 1',
        ]);

        $maba2 = MabaData::create([
            'tahun_maba_id' => $tahunMaba->id,
            'nama_biro' => 'Ahmad Fauzan',
            'program_studi_biro' => 'Teknik Informatika',
            'tanggal_jadwal' => '2026-09-16',
            'sesi_jadwal' => 'Sesi 2',
        ]);

        $this->assertNotEquals($maba1->id, $maba2->id);
        $this->assertEquals('Ahmad Fauzan', $maba1->nama_biro);
        $this->assertEquals('Ahmad Fauzan', $maba2->nama_biro);
        $this->assertEquals(2, MabaData::where('nama_biro', 'Ahmad Fauzan')->count());
    }

    public function test_menghapus_tahun_maba_terproteksi_oleh_foreign_key_restrict()
    {
        $tahunMaba = TahunMaba::create([
            'tahun' => 2028,
            'nama' => 'Maba 2028',
            'is_active' => false,
        ]);

        MabaData::create([
            'tahun_maba_id' => $tahunMaba->id,
            'nama_biro' => 'Protected Maba',
            'program_studi_biro' => 'Teknik Elektro',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $tahunMaba->delete();
    }
}
