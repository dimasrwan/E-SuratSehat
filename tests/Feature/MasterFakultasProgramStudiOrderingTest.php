<?php

namespace Tests\Feature;

use App\Models\Fakultas;
use App\Models\MabaData;
use App\Models\Pemeriksaan;
use App\Models\ProgramStudi;
use App\Models\TahunMaba;
use App\Models\User;
use Database\Seeders\FakultasProdiSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterFakultasProgramStudiOrderingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->operator = User::factory()->create([
            'role' => 'operator',
            'is_active' => true,
        ]);

        $this->seed(FakultasProdiSeeder::class);
    }

    /** 1. test_urutan_10_fakultas_sesuai_daftar_resmi */
    public function test_urutan_10_fakultas_sesuai_daftar_resmi(): void
    {
        $expectedCodes = ['FTK', 'FSH', 'FUF', 'FDK', 'FAH', 'FEBI', 'SAINTEK', 'FISIP', 'FPSI', 'FK'];
        $actualCodes = Fakultas::orderBy('sort_order', 'asc')->pluck('kode')->toArray();

        $this->assertEquals($expectedCodes, $actualCodes);
    }

    /** 2. test_urutan_prodi_ftk */
    public function test_urutan_prodi_ftk(): void
    {
        $ftk = Fakultas::where('kode', 'FTK')->first();
        $expected = [
            'Pendidikan Agama Islam',
            'Pendidikan Bahasa Arab',
            'Pendidikan Bahasa Inggris',
            'Manajemen Pendidikan Islam',
            'Pendidikan Matematika',
            'Pendidikan Fisika',
            'Pendidikan Biologi',
            'Pendidikan Kimia',
            'Pendidikan Guru Madrasah Ibtidaiyah',
            'Pendidikan Islam Anak Usia Dini',
            'Pendidikan Teknik Elektro',
            'Pendidikan Teknologi Informasi',
            'Bimbingan dan Konseling',
            'Pendidikan Profesi Guru',
        ];
        $actual = $ftk->programStudi()->pluck('nama')->toArray();

        $this->assertEquals($expected, $actual);
    }

    /** 3. test_urutan_prodi_fsh */
    public function test_urutan_prodi_fsh(): void
    {
        $fsh = Fakultas::where('kode', 'FSH')->first();
        $expected = [
            'Hukum Keluarga',
            'Hukum Ekonomi Syariah',
            'Hukum Pidana Islam',
            'Hukum Tata Negara',
            'Perbandingan Mazhab',
            'Ilmu Hukum',
        ];
        $actual = $fsh->programStudi()->pluck('nama')->toArray();

        $this->assertEquals($expected, $actual);
    }

    /** 4. test_urutan_prodi_fuf */
    public function test_urutan_prodi_fuf(): void
    {
        $fuf = Fakultas::where('kode', 'FUF')->first();
        $expected = [
            'Aqidah dan Filsafat Islam',
            'Ilmu Al-Qur\'an dan Tafsir',
            'Ilmu Hadis',
            'Studi Agama-Agama',
            'Sosiologi Agama',
        ];
        $actual = $fuf->programStudi()->pluck('nama')->toArray();

        $this->assertEquals($expected, $actual);
    }

    /** 5. test_urutan_prodi_fdk */
    public function test_urutan_prodi_fdk(): void
    {
        $fdk = Fakultas::where('kode', 'FDK')->first();
        $expected = [
            'Komunikasi dan Penyiaran Islam',
            'Bimbingan dan Konseling Islam',
            'Manajemen Dakwah',
            'Pengembangan Masyarakat Islam',
            'Kesejahteraan Sosial',
            'Manajemen Haji dan Umrah',
        ];
        $actual = $fdk->programStudi()->pluck('nama')->toArray();

        $this->assertEquals($expected, $actual);
    }

    /** 6. test_urutan_prodi_fah */
    public function test_urutan_prodi_fah(): void
    {
        $fah = Fakultas::where('kode', 'FAH')->first();
        $expected = [
            'Sejarah dan Kebudayaan Islam',
            'Bahasa dan Sastra Arab',
            'Ilmu Perpustakaan',
        ];
        $actual = $fah->programStudi()->pluck('nama')->toArray();

        $this->assertEquals($expected, $actual);
    }

    /** 7. test_urutan_prodi_febi */
    public function test_urutan_prodi_febi(): void
    {
        $febi = Fakultas::where('kode', 'FEBI')->first();
        $expected = [
            'Ekonomi Syariah',
            'Perbankan Syariah',
            'Ilmu Ekonomi',
            'Manajemen Bisnis Syariah',
            'Manajemen Industri Halal',
        ];
        $actual = $febi->programStudi()->pluck('nama')->toArray();

        $this->assertEquals($expected, $actual);
    }

    /** 8. test_urutan_prodi_saintek */
    public function test_urutan_prodi_saintek(): void
    {
        $saintek = Fakultas::where('kode', 'SAINTEK')->first();
        $expected = [
            'Arsitektur',
            'Teknik Lingkungan',
            'Biologi',
            'Kimia',
            'Teknik Fisika',
            'Teknologi Informasi',
        ];
        $actual = $saintek->programStudi()->pluck('nama')->toArray();

        $this->assertEquals($expected, $actual);
    }

    /** 9. test_urutan_prodi_fisip */
    public function test_urutan_prodi_fisip(): void
    {
        $fisip = Fakultas::where('kode', 'FISIP')->first();
        $expected = [
            'Ilmu Administrasi Negara',
            'Ilmu Politik',
        ];
        $actual = $fisip->programStudi()->pluck('nama')->toArray();

        $this->assertEquals($expected, $actual);
    }

    /** 10. test_urutan_prodi_fpsi */
    public function test_urutan_prodi_fpsi(): void
    {
        $fpsi = Fakultas::where('kode', 'FPSI')->first();
        $expected = ['Psikologi'];
        $actual = $fpsi->programStudi()->pluck('nama')->toArray();

        $this->assertEquals($expected, $actual);
    }

    /** 11. test_urutan_prodi_fk */
    public function test_urutan_prodi_fk(): void
    {
        $fk = Fakultas::where('kode', 'FK')->first();
        $expected = [
            'Kedokteran',
            'Pendidikan Profesi Dokter',
        ];
        $actual = $fk->programStudi()->pluck('nama')->toArray();

        $this->assertEquals($expected, $actual);
    }

    /** 12. test_dropdown_hanya_menampilkan_fakultas_aktif */
    public function test_dropdown_hanya_menampilkan_fakultas_aktif(): void
    {
        $inactiveFakultas = Fakultas::create(['nama' => 'Fakultas Inaktif Custom', 'kode' => 'FIC', 'is_active' => false, 'sort_order' => 99]);

        $response = $this->actingAs($this->operator)->get(route('pemeriksaan.index'));
        $response->assertStatus(200);
        $response->assertDontSee('Fakultas Inaktif Custom');
    }

    /** 13. test_dropdown_hanya_menampilkan_prodi_aktif */
    public function test_dropdown_hanya_menampilkan_prodi_aktif(): void
    {
        $f = Fakultas::where('kode', 'FTK')->first();
        $inactiveProdi = ProgramStudi::create(['fakultas_id' => $f->id, 'nama' => 'Prodi Inaktif Custom', 'is_active' => false, 'sort_order' => 99]);

        $response = $this->actingAs($this->operator)->get(route('pemeriksaan.index'));
        $response->assertStatus(200);
        $response->assertDontSee('Prodi Inaktif Custom');
    }

    /** 14. test_existing_id_tidak_berubah */
    public function test_existing_id_tidak_berubah(): void
    {
        $ftkBefore = Fakultas::where('kode', 'FTK')->first();
        $prodiBefore = ProgramStudi::where('nama', 'Pendidikan Agama Islam')->first();

        // Run seeder again
        $this->seed(FakultasProdiSeeder::class);

        $ftkAfter = Fakultas::where('kode', 'FTK')->first();
        $prodiAfter = ProgramStudi::where('nama', 'Pendidikan Agama Islam')->first();

        $this->assertEquals($ftkBefore->id, $ftkAfter->id);
        $this->assertEquals($prodiBefore->id, $prodiAfter->id);
    }

    /** 15. test_existing_data_operasional_tetap_memiliki_relasi_yang_valid */
    public function test_existing_data_operasional_tetap_memiliki_relasi_yang_valid(): void
    {
        $tahun = TahunMaba::firstOrCreate(['tahun' => 2026], ['nama' => 'Maba 2026', 'is_active' => true]);
        $saintek = Fakultas::where('kode', 'SAINTEK')->first();
        $prodi = ProgramStudi::where('fakultas_id', $saintek->id)->where('nama', 'Teknologi Informasi')->first();

        $maba = MabaData::create([
            'tahun_maba_id' => $tahun->id,
            'nama_biro' => 'Maba Valid',
            'fakultas' => $saintek->nama,
            'program_studi' => $prodi->nama,
            'program_studi_biro' => $prodi->nama,
            'status_biodata' => 'PEMERIKSAAN_SELESAI',
        ]);

        $pemeriksaan = Pemeriksaan::create([
            'maba_data_id' => $maba->id,
            'nomor_surat' => '999/Un.08/PPKES/2026',
            'nama' => 'Maba Valid',
            'pekerjaan' => $prodi->nama,
            'kesimpulan' => 'Sehat',
            'tahun_masuk' => 2026,
        ]);

        $this->assertEquals($saintek->nama, $maba->fakultas);
        $this->assertEquals($prodi->nama, $maba->program_studi);
        $this->assertEquals($maba->id, $pemeriksaan->maba_data_id);
    }

    /** 16. test_seeder_idempotent */
    public function test_seeder_idempotent(): void
    {
        $fakultasCountBefore = Fakultas::count();
        $prodiCountBefore = ProgramStudi::count();

        // Seed 3 times consecutively
        $this->seed(FakultasProdiSeeder::class);
        $this->seed(FakultasProdiSeeder::class);
        $this->seed(FakultasProdiSeeder::class);

        $this->assertEquals($fakultasCountBefore, Fakultas::count());
        $this->assertEquals($prodiCountBefore, ProgramStudi::count());
    }

    /** 17. test_search_master_tetap_bekerja */
    public function test_search_master_tetap_bekerja(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.fakultas-prodi.index', ['search' => 'Arsitektur']));
        $response->assertStatus(200);
        $response->assertSee('Fakultas Sains dan Teknologi');
        $response->assertSee('Arsitektur');
    }

    /** 18. test_filter_status_tetap_bekerja */
    public function test_filter_status_tetap_bekerja(): void
    {
        $inactiveFakultas = Fakultas::create(['nama' => 'Fakultas Khusus Nonaktif', 'kode' => 'FKN', 'is_active' => false, 'sort_order' => 99]);

        $responseActive = $this->actingAs($this->admin)->get(route('admin.fakultas-prodi.index', ['status' => 'active']));
        $responseActive->assertStatus(200);
        $responseActive->assertSee('Fakultas Tarbiyah dan Keguruan');

        $responseInactive = $this->actingAs($this->admin)->get(route('admin.fakultas-prodi.index', ['status' => 'inactive']));
        $responseInactive->assertStatus(200);
        $responseInactive->assertSee('Fakultas Khusus Nonaktif');
        $responseInactive->assertDontSee('Pendidikan Agama Islam');
    }

    /** 19. test_dropdown_program_studi_data_pemeriksaan_tetap_bekerja */
    public function test_dropdown_program_studi_data_pemeriksaan_tetap_bekerja(): void
    {
        $response = $this->actingAs($this->operator)->get(route('pemeriksaan.index'));
        $response->assertStatus(200);
        $response->assertSee('Semua Program Studi');
        $response->assertSee('Fakultas Tarbiyah dan Keguruan');
        $response->assertSee('Fakultas Kedokteran');
    }

    /** 20. test_program_studi_dengan_nama_sama_pada_fakultas_berbeda_tetap_terisolasi */
    public function test_program_studi_dengan_nama_sama_pada_fakultas_berbeda_tetap_terisolasi(): void
    {
        $pti = ProgramStudi::where('nama', 'Pendidikan Teknologi Informasi')->first();
        $ti = ProgramStudi::where('nama', 'Teknologi Informasi')->first();

        $this->assertNotEquals($pti->fakultas_id, $ti->fakultas_id);
        $this->assertNotEquals($pti->id, $ti->id);
    }
}
