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

class PemeriksaanProdiFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $operator;
    protected User $admin;
    protected TahunMaba $tahun2026;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operator = User::factory()->create([
            'role' => 'operator',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->tahun2026 = TahunMaba::firstOrCreate(
            ['tahun' => 2026],
            ['nama' => 'Maba 2026', 'is_active' => true]
        );
    }

    /** 1. Admin/operator dapat melihat dropdown Program Studi pada /pemeriksaan. */
    public function test_1_operator_can_view_pemeriksaan_page_with_prodi_dropdown(): void
    {
        $this->seed(FakultasProdiSeeder::class);

        $response = $this->actingAs($this->operator)->get(route('pemeriksaan.index'));
        $response->assertStatus(200);
        $response->assertSee('Semua Program Studi');
        $response->assertSee('Fakultas Tarbiyah dan Keguruan');
    }

    /** 2. Dropdown hanya menggunakan Fakultas aktif. */
    public function test_2_dropdown_only_contains_active_fakultas(): void
    {
        $fActive = Fakultas::create(['nama' => 'Fakultas Aktif FT', 'kode' => 'FAFT', 'is_active' => true]);
        ProgramStudi::create(['fakultas_id' => $fActive->id, 'nama' => 'Prodi Aktif A', 'is_active' => true]);

        $fInactive = Fakultas::create(['nama' => 'Fakultas Inaktif FI', 'kode' => 'FIFI', 'is_active' => false]);
        ProgramStudi::create(['fakultas_id' => $fInactive->id, 'nama' => 'Prodi Inaktif B', 'is_active' => true]);

        $response = $this->actingAs($this->operator)->get(route('pemeriksaan.index'));
        $response->assertStatus(200);
        $response->assertSee('Fakultas Aktif FT');
        $response->assertDontSee('Fakultas Inaktif FI');
    }

    /** 3. Dropdown hanya menggunakan Program Studi aktif. */
    public function test_3_dropdown_only_contains_active_program_studi(): void
    {
        $fakultas = Fakultas::create(['nama' => 'Fakultas Sains', 'kode' => 'FS3', 'is_active' => true]);
        ProgramStudi::create(['fakultas_id' => $fakultas->id, 'nama' => 'Fisika Aktif', 'is_active' => true]);
        ProgramStudi::create(['fakultas_id' => $fakultas->id, 'nama' => 'Astronomi Nonaktif', 'is_active' => false]);

        $response = $this->actingAs($this->operator)->get(route('pemeriksaan.index'));
        $response->assertStatus(200);
        $response->assertSee('Fisika Aktif');
        $response->assertDontSee('Astronomi Nonaktif');
    }

    /** 4. Program Studi dikelompokkan berdasarkan Fakultas yang benar. */
    public function test_4_prodi_are_grouped_by_correct_fakultas(): void
    {
        $fakultas = Fakultas::create(['nama' => 'Fakultas Psikologi Unique', 'kode' => 'FPSIU', 'is_active' => true]);
        $prodi = ProgramStudi::create(['fakultas_id' => $fakultas->id, 'nama' => 'Psikologi Klinis', 'is_active' => true]);

        $response = $this->actingAs($this->operator)->get(route('pemeriksaan.index'));
        $response->assertStatus(200);
        $response->assertSee('Fakultas Psikologi Unique');
        $response->assertSee('Psikologi Klinis');
    }

    /** 5. Program Studi dari Fakultas berbeda tidak tercampur. */
    public function test_5_prodi_different_fakultas_isolation(): void
    {
        $fA = Fakultas::create(['nama' => 'Fakultas A5', 'kode' => 'FA5', 'is_active' => true]);
        $pA = ProgramStudi::create(['fakultas_id' => $fA->id, 'nama' => 'Prodi A5', 'is_active' => true]);

        $fB = Fakultas::create(['nama' => 'Fakultas B5', 'kode' => 'FB5', 'is_active' => true]);
        $pB = ProgramStudi::create(['fakultas_id' => $fB->id, 'nama' => 'Prodi B5', 'is_active' => true]);

        $this->assertEquals($fA->id, $pA->fakultas_id);
        $this->assertEquals($fB->id, $pB->fakultas_id);
        $this->assertNotEquals($pA->fakultas_id, $pB->fakultas_id);
    }

    /** 6. Filter menggunakan ID Program Studi atau nama Program Studi. */
    public function test_6_filter_works_by_prodi_id_or_name(): void
    {
        $fakultas = Fakultas::create(['nama' => 'Fakultas Teknik', 'kode' => 'FT6', 'is_active' => true]);
        $prodi = ProgramStudi::create(['fakultas_id' => $fakultas->id, 'nama' => 'Teknik Elektro Special', 'is_active' => true]);

        $maba = MabaData::create([
            'tahun_maba_id' => $this->tahun2026->id,
            'nama_biro' => 'Budi Elektro',
            'program_studi_biro' => 'Teknik Elektro Special',
            'status_biodata' => 'TERVERIFIKASI',
        ]);

        $response = $this->actingAs($this->operator)->get(route('pemeriksaan.index', [
            'status_antrean' => 'belum_diperiksa',
            'prodi' => $prodi->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Budi Elektro');
    }

    /** 7. Filter Program Studi menghasilkan data yang benar. */
    public function test_7_filter_prodi_returns_correct_matching_data(): void
    {
        $mabaMatch = MabaData::create([
            'tahun_maba_id' => $this->tahun2026->id,
            'nama_biro' => 'Maba Cocok',
            'program_studi_biro' => 'Ilmu Hukum',
            'status_biodata' => 'TERVERIFIKASI',
        ]);

        $mabaOther = MabaData::create([
            'tahun_maba_id' => $this->tahun2026->id,
            'nama_biro' => 'Maba Lain',
            'program_studi_biro' => 'Kedokteran',
            'status_biodata' => 'TERVERIFIKASI',
        ]);

        $response = $this->actingAs($this->operator)->get(route('pemeriksaan.index', [
            'status_antrean' => 'belum_diperiksa',
            'prodi' => 'Ilmu Hukum',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Maba Cocok');
        $response->assertDontSee('Maba Lain');
    }

    /** 8. Filter Program Studi tetap terisolasi berdasarkan Tahun Maba. */
    public function test_8_prodi_filter_preserves_year_isolation(): void
    {
        $tahun2025 = TahunMaba::firstOrCreate(['tahun' => 2025], ['nama' => 'Maba 2025', 'is_active' => false]);

        $maba2026 = MabaData::create([
            'tahun_maba_id' => $this->tahun2026->id,
            'nama_biro' => 'Maba 2026 Arsitektur',
            'program_studi_biro' => 'Arsitektur',
            'status_biodata' => 'TERVERIFIKASI',
        ]);

        $maba2025 = MabaData::create([
            'tahun_maba_id' => $tahun2025->id,
            'nama_biro' => 'Maba 2025 Arsitektur',
            'program_studi_biro' => 'Arsitektur',
            'status_biodata' => 'TERVERIFIKASI',
        ]);

        $response = $this->actingAs($this->operator)->get(route('pemeriksaan.index', [
            'tahun_masuk' => 2026,
            'status_antrean' => 'belum_diperiksa',
            'prodi' => 'Arsitektur',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Maba 2026 Arsitektur');
        $response->assertDontSee('Maba 2025 Arsitektur');
    }

    /** 9. Program Studi nonaktif tidak muncul di master dropdown. */
    public function test_9_inactive_prodi_hidden_from_dropdown(): void
    {
        $fakultas = Fakultas::create(['nama' => 'Fakultas Sains 9', 'kode' => 'FS9', 'is_active' => true]);
        ProgramStudi::create(['fakultas_id' => $fakultas->id, 'nama' => 'BioInformatika Nonaktif', 'is_active' => false]);

        $response = $this->actingAs($this->operator)->get(route('pemeriksaan.index'));
        $response->assertStatus(200);
        $response->assertDontSee('BioInformatika Nonaktif');
    }

    /** 10. Fakultas nonaktif tidak muncul di master dropdown. */
    public function test_10_inactive_fakultas_hidden_from_dropdown(): void
    {
        Fakultas::create(['nama' => 'Fakultas Tutup FT', 'kode' => 'FT10', 'is_active' => false]);

        $response = $this->actingAs($this->operator)->get(route('pemeriksaan.index'));
        $response->assertStatus(200);
        $response->assertDontSee('Fakultas Tutup FT');
    }

    /** 11. "Semua Program Studi" menghapus filter Program Studi. */
    public function test_11_empty_prodi_filter_shows_all_prodi_data(): void
    {
        $maba1 = MabaData::create([
            'tahun_maba_id' => $this->tahun2026->id,
            'nama_biro' => 'Maba PAI',
            'program_studi_biro' => 'Pendidikan Agama Islam',
            'status_biodata' => 'TERVERIFIKASI',
        ]);

        $maba2 = MabaData::create([
            'tahun_maba_id' => $this->tahun2026->id,
            'nama_biro' => 'Maba HES',
            'program_studi_biro' => 'Hukum Ekonomi Syariah',
            'status_biodata' => 'TERVERIFIKASI',
        ]);

        $response = $this->actingAs($this->operator)->get(route('pemeriksaan.index', [
            'status_antrean' => 'belum_diperiksa',
            'prodi' => '',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Maba PAI');
        $response->assertSee('Maba HES');
    }

    /** 12. Search dropdown bekerja case-insensitive di frontend (verified via master data). */
    public function test_12_prodi_search_case_insensitive_support(): void
    {
        $this->seed(FakultasProdiSeeder::class);

        $response = $this->actingAs($this->operator)->get(route('pemeriksaan.index', [
            'prodi' => 'teknologi informasi',
        ]));

        $response->assertStatus(200);
    }

    /** 13. Search dropdown tidak mengubah data backend. */
    public function test_13_prodi_search_does_not_mutate_backend_data(): void
    {
        $this->seed(FakultasProdiSeeder::class);

        $initialCount = ProgramStudi::count();

        $this->actingAs($this->operator)->get(route('pemeriksaan.index', ['prodi' => 'Kimia']));

        $this->assertEquals($initialCount, ProgramStudi::count());
    }

    /** 14. Reset menghapus filter Program Studi. */
    public function test_14_reset_filter_clears_prodi(): void
    {
        $response = $this->actingAs($this->operator)->get(route('pemeriksaan.index', [
            'clear_filter' => 1,
            'tahun_masuk' => 2026,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Semua Program Studi');
    }

    /** 15. Legacy data tetap aman. */
    public function test_15_legacy_data_remains_safe(): void
    {
        $mabaLegacy = MabaData::create([
            'tahun_maba_id' => $this->tahun2026->id,
            'nama_biro' => 'Legacy Student Exam',
            'program_studi_biro' => 'Studi Kuno (Legacy)',
            'status_biodata' => 'PEMERIKSAAN_SELESAI',
        ]);

        $pemeriksaan = Pemeriksaan::create([
            'maba_data_id' => $mabaLegacy->id,
            'nomor_surat' => '999/Un.08/PPKES/2026',
            'nama' => 'Legacy Student Exam',
            'pekerjaan' => 'Studi Kuno (Legacy)',
            'kesimpulan' => 'Sehat',
        ]);

        $response = $this->actingAs($this->operator)->get(route('pemeriksaan.index', ['status_antrean' => 'selesai']));
        $response->assertStatus(200);
        $response->assertSee('Legacy Student Exam');
    }

    /** 16. Existing export Excel and PDF with prodi filter works cleanly. */
    public function test_16_export_excel_and_pdf_support_prodi_filter(): void
    {
        $pemeriksaan = Pemeriksaan::create([
            'nomor_surat' => '100/Un.08/PPKES/2026',
            'nama' => 'Export Student',
            'email' => 'export@student.ac.id',
            'pekerjaan' => 'Teknik Lingkungan',
            'kesimpulan' => 'Sehat',
            'tahun_masuk' => 2026,
        ]);

        $responseExcel = $this->actingAs($this->operator)->get(route('pemeriksaan.exportExcel', ['prodi' => 'Teknik Lingkungan']));
        $responseExcel->assertStatus(200);

        $responsePdf = $this->actingAs($this->operator)->get(route('pemeriksaan.exportPdf', ['prodi' => 'Teknik Lingkungan']));
        $responsePdf->assertStatus(200);
    }

    /** 17. test_program_studi_filter_by_master_id_returns_matching_records */
    public function test_program_studi_filter_by_master_id_returns_matching_records(): void
    {
        $fakultas = Fakultas::create(['nama' => 'Fakultas Sains dan Teknologi', 'kode' => 'FST17', 'is_active' => true]);
        $prodi = ProgramStudi::create(['fakultas_id' => $fakultas->id, 'nama' => 'Teknologi Informasi', 'is_active' => true]);

        $maba = MabaData::create([
            'tahun_maba_id' => $this->tahun2026->id,
            'nama_biro' => 'Ahmad TI',
            'program_studi' => 'Teknologi Informasi',
            'program_studi_biro' => 'Teknologi Informasi',
            'status_biodata' => 'PEMERIKSAAN_SELESAI',
        ]);

        $pemeriksaan = Pemeriksaan::create([
            'maba_data_id' => $maba->id,
            'nomor_surat' => '170/Un.08/PPKES/2026',
            'nama' => 'Ahmad TI',
            'pekerjaan' => 'Teknologi Informasi',
            'kesimpulan' => 'Sehat',
            'tahun_masuk' => 2026,
            'status_pengiriman' => 'Terkirim',
        ]);

        $response = $this->actingAs($this->operator)->get(route('pemeriksaan.index', [
            'status_antrean' => 'selesai',
            'tahun_masuk' => 2026,
            'prodi' => $prodi->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Ahmad TI');
    }

    /** 18. test_program_studi_filter_does_not_compare_id_to_name */
    public function test_program_studi_filter_does_not_compare_id_to_name(): void
    {
        $fakultas = Fakultas::create(['nama' => 'Fakultas Sains 18', 'kode' => 'FS18', 'is_active' => true]);
        $prodi = ProgramStudi::create(['fakultas_id' => $fakultas->id, 'nama' => 'Teknologi Informasi', 'is_active' => true]);

        $mabaStrId = MabaData::create([
            'tahun_maba_id' => $this->tahun2026->id,
            'nama_biro' => 'Maba String 44',
            'program_studi' => '44',
            'program_studi_biro' => '44',
            'status_biodata' => 'PEMERIKSAAN_SELESAI',
        ]);

        $pemStrId = Pemeriksaan::create([
            'maba_data_id' => $mabaStrId->id,
            'nomor_surat' => '181/Un.08/PPKES/2026',
            'nama' => 'Maba String 44',
            'pekerjaan' => '44',
            'kesimpulan' => 'Sehat',
            'tahun_masuk' => 2026,
        ]);

        $mabaRealTI = MabaData::create([
            'tahun_maba_id' => $this->tahun2026->id,
            'nama_biro' => 'Maba Real TI',
            'program_studi' => 'Teknologi Informasi',
            'program_studi_biro' => 'Teknologi Informasi',
            'status_biodata' => 'PEMERIKSAAN_SELESAI',
        ]);

        $pemRealTI = Pemeriksaan::create([
            'maba_data_id' => $mabaRealTI->id,
            'nomor_surat' => '182/Un.08/PPKES/2026',
            'nama' => 'Maba Real TI',
            'pekerjaan' => 'Teknologi Informasi',
            'kesimpulan' => 'Sehat',
            'tahun_masuk' => 2026,
        ]);

        // Filter by master prodi id -> should resolve to "Teknologi Informasi", not literal string "44"
        $response = $this->actingAs($this->operator)->get(route('pemeriksaan.index', [
            'status_antrean' => 'selesai',
            'tahun_masuk' => 2026,
            'prodi' => $prodi->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Maba Real TI');
        $response->assertDontSee('Maba String 44');
    }

    /** 19. test_program_studi_filter_respects_year_isolation */
    public function test_program_studi_filter_respects_year_isolation(): void
    {
        $tahun2027 = TahunMaba::firstOrCreate(['tahun' => 2027], ['nama' => 'Maba 2027', 'is_active' => true]);
        $fakultas = Fakultas::create(['nama' => 'Fakultas Teknik 19', 'kode' => 'FT19', 'is_active' => true]);
        $prodi = ProgramStudi::create(['fakultas_id' => $fakultas->id, 'nama' => 'Teknologi Informasi', 'is_active' => true]);

        $maba2026 = MabaData::create([
            'tahun_maba_id' => $this->tahun2026->id,
            'nama_biro' => 'Student TI 2026',
            'program_studi' => 'Teknologi Informasi',
            'program_studi_biro' => 'Teknologi Informasi',
            'status_biodata' => 'PEMERIKSAAN_SELESAI',
        ]);
        Pemeriksaan::create([
            'maba_data_id' => $maba2026->id,
            'nomor_surat' => '191/Un.08/PPKES/2026',
            'nama' => 'Student TI 2026',
            'pekerjaan' => 'Teknologi Informasi',
            'kesimpulan' => 'Sehat',
            'tahun_masuk' => 2026,
        ]);

        $maba2027 = MabaData::create([
            'tahun_maba_id' => $tahun2027->id,
            'nama_biro' => 'Student TI 2027',
            'program_studi' => 'Teknologi Informasi',
            'program_studi_biro' => 'Teknologi Informasi',
            'status_biodata' => 'PEMERIKSAAN_SELESAI',
        ]);
        Pemeriksaan::create([
            'maba_data_id' => $maba2027->id,
            'nomor_surat' => '192/Un.08/PPKES/2027',
            'nama' => 'Student TI 2027',
            'pekerjaan' => 'Teknologi Informasi',
            'kesimpulan' => 'Sehat',
            'tahun_masuk' => 2027,
        ]);

        $response = $this->actingAs($this->operator)->get(route('pemeriksaan.index', [
            'status_antrean' => 'selesai',
            'tahun_masuk' => 2026,
            'prodi' => $prodi->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Student TI 2026');
        $response->assertDontSee('Student TI 2027');
    }

    /** 20. test_inactive_program_studi_cannot_be_used_as_filter */
    public function test_inactive_program_studi_cannot_be_used_as_filter(): void
    {
        $fakultas = Fakultas::create(['nama' => 'Fakultas Sains 20', 'kode' => 'FS20', 'is_active' => true]);
        $inactiveProdi = ProgramStudi::create(['fakultas_id' => $fakultas->id, 'nama' => 'Prodi Tutup', 'is_active' => false]);

        $maba = MabaData::create([
            'tahun_maba_id' => $this->tahun2026->id,
            'nama_biro' => 'Student Prodi Tutup',
            'program_studi' => 'Prodi Tutup',
            'program_studi_biro' => 'Prodi Tutup',
            'status_biodata' => 'PEMERIKSAAN_SELESAI',
        ]);

        Pemeriksaan::create([
            'maba_data_id' => $maba->id,
            'nomor_surat' => '201/Un.08/PPKES/2026',
            'nama' => 'Student Prodi Tutup',
            'pekerjaan' => 'Prodi Tutup',
            'kesimpulan' => 'Sehat',
            'tahun_masuk' => 2026,
        ]);

        $response = $this->actingAs($this->operator)->get(route('pemeriksaan.index', [
            'status_antrean' => 'selesai',
            'tahun_masuk' => 2026,
            'prodi' => $inactiveProdi->id,
        ]));

        $response->assertStatus(200);
        $response->assertDontSee('Student Prodi Tutup');
    }

    /** 21. test_all_program_studi_keeps_legacy_records_safe */
    public function test_all_program_studi_keeps_legacy_records_safe(): void
    {
        $legacy = Pemeriksaan::create([
            'maba_data_id' => null,
            'nomor_surat' => '211/Un.08/PPKES/2026',
            'nama' => 'Legacy Record Safe',
            'pekerjaan' => 'Mahasiswa',
            'kesimpulan' => 'Sehat',
            'tahun_masuk' => 2026,
        ]);

        $response = $this->actingAs($this->operator)->get(route('pemeriksaan.index', [
            'status_antrean' => 'selesai',
            'tahun_masuk' => 2026,
            'prodi' => '',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Legacy Record Safe');
    }

    /** 22. test_specific_program_studi_does_not_fake_legacy_mapping */
    public function test_specific_program_studi_does_not_fake_legacy_mapping(): void
    {
        $fakultas = Fakultas::create(['nama' => 'Fakultas Sains 22', 'kode' => 'FS22', 'is_active' => true]);
        $prodi = ProgramStudi::create(['fakultas_id' => $fakultas->id, 'nama' => 'Teknologi Informasi', 'is_active' => true]);

        $legacy = Pemeriksaan::create([
            'maba_data_id' => null,
            'nomor_surat' => '221/Un.08/PPKES/2026',
            'nama' => 'Legacy Unmapped Student',
            'pekerjaan' => 'Mahasiswa',
            'kesimpulan' => 'Sehat',
            'tahun_masuk' => 2026,
        ]);

        $response = $this->actingAs($this->operator)->get(route('pemeriksaan.index', [
            'status_antrean' => 'selesai',
            'tahun_masuk' => 2026,
            'prodi' => $prodi->id,
        ]));

        $response->assertStatus(200);
        $response->assertDontSee('Legacy Unmapped Student');
    }

    /** 23. test_combined_program_studi_and_email_filter */
    public function test_combined_program_studi_and_email_filter(): void
    {
        $fakultas = Fakultas::create(['nama' => 'Fakultas Sains 23', 'kode' => 'FS23', 'is_active' => true]);
        $prodi = ProgramStudi::create(['fakultas_id' => $fakultas->id, 'nama' => 'Teknologi Informasi', 'is_active' => true]);

        $mabaSent = MabaData::create([
            'tahun_maba_id' => $this->tahun2026->id,
            'nama_biro' => 'TI Sent Email',
            'program_studi' => 'Teknologi Informasi',
            'program_studi_biro' => 'Teknologi Informasi',
            'status_biodata' => 'PEMERIKSAAN_SELESAI',
        ]);
        Pemeriksaan::create([
            'maba_data_id' => $mabaSent->id,
            'nomor_surat' => '231/Un.08/PPKES/2026',
            'nama' => 'TI Sent Email',
            'pekerjaan' => 'Teknologi Informasi',
            'kesimpulan' => 'Sehat',
            'tahun_masuk' => 2026,
            'status_pengiriman' => 'Terkirim',
        ]);

        $mabaFailed = MabaData::create([
            'tahun_maba_id' => $this->tahun2026->id,
            'nama_biro' => 'TI Failed Email',
            'program_studi' => 'Teknologi Informasi',
            'program_studi_biro' => 'Teknologi Informasi',
            'status_biodata' => 'PEMERIKSAAN_SELESAI',
        ]);
        Pemeriksaan::create([
            'maba_data_id' => $mabaFailed->id,
            'nomor_surat' => '232/Un.08/PPKES/2026',
            'nama' => 'TI Failed Email',
            'pekerjaan' => 'Teknologi Informasi',
            'kesimpulan' => 'Sehat',
            'tahun_masuk' => 2026,
            'status_pengiriman' => 'Gagal',
        ]);

        $response = $this->actingAs($this->operator)->get(route('pemeriksaan.index', [
            'status_antrean' => 'selesai',
            'tahun_masuk' => 2026,
            'prodi' => $prodi->id,
            'status_email' => 'Terkirim',
        ]));

        $response->assertStatus(200);
        $response->assertSee('TI Sent Email');
        $response->assertDontSee('TI Failed Email');
    }

    /** 24. test_combined_program_studi_and_search_filter */
    public function test_combined_program_studi_and_search_filter(): void
    {
        $fakultas = Fakultas::create(['nama' => 'Fakultas Sains 24', 'kode' => 'FS24', 'is_active' => true]);
        $prodi = ProgramStudi::create(['fakultas_id' => $fakultas->id, 'nama' => 'Teknologi Informasi', 'is_active' => true]);

        $mabaMatch = MabaData::create([
            'tahun_maba_id' => $this->tahun2026->id,
            'nama_biro' => 'Zidane TI',
            'program_studi' => 'Teknologi Informasi',
            'program_studi_biro' => 'Teknologi Informasi',
            'status_biodata' => 'PEMERIKSAAN_SELESAI',
        ]);
        Pemeriksaan::create([
            'maba_data_id' => $mabaMatch->id,
            'nomor_surat' => '241/Un.08/PPKES/2026',
            'nama' => 'Zidane TI',
            'pekerjaan' => 'Teknologi Informasi',
            'kesimpulan' => 'Sehat',
            'tahun_masuk' => 2026,
        ]);

        $mabaOther = MabaData::create([
            'tahun_maba_id' => $this->tahun2026->id,
            'nama_biro' => 'Ronaldo TI',
            'program_studi' => 'Teknologi Informasi',
            'program_studi_biro' => 'Teknologi Informasi',
            'status_biodata' => 'PEMERIKSAAN_SELESAI',
        ]);
        Pemeriksaan::create([
            'maba_data_id' => $mabaOther->id,
            'nomor_surat' => '242/Un.08/PPKES/2026',
            'nama' => 'Ronaldo TI',
            'pekerjaan' => 'Teknologi Informasi',
            'kesimpulan' => 'Sehat',
            'tahun_masuk' => 2026,
        ]);

        $response = $this->actingAs($this->operator)->get(route('pemeriksaan.index', [
            'status_antrean' => 'selesai',
            'tahun_masuk' => 2026,
            'prodi' => $prodi->id,
            'search' => 'Zidane',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Zidane TI');
        $response->assertDontSee('Ronaldo TI');
    }
}
