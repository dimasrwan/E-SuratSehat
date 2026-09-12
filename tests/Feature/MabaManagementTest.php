<?php

namespace Tests\Feature;

use App\Models\MabaData;
use App\Models\Pemeriksaan;
use App\Models\TahunMaba;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MabaManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected TahunMaba $tahun2026;
    protected TahunMaba $tahun2027;

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

        $this->tahun2026 = TahunMaba::firstOrCreate(
            ['tahun' => 2026],
            [
                'nama' => 'Tahun 2026',
                'is_active' => false,
                'format_nomor_surat' => '445/{no}/SKBS/2026',
            ]
        );

        $this->tahun2027 = TahunMaba::firstOrCreate(
            ['tahun' => 2027],
            [
                'nama' => 'Tahun 2027',
                'is_active' => true,
                'format_nomor_surat' => '445/{no}/SKBS/2027',
            ]
        );

        \App\Models\TahunMaba::query()->update(['is_active' => false]);
        \App\Models\TahunMaba::where('id', $this->tahun2027->id)->update(['is_active' => true]);
        $this->tahun2027->refresh();
    }

    public function test_guest_cannot_access_maba_management()
    {
        $response = $this->get(route('maba.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_and_operator_can_access_maba_index()
    {
        $responseAdmin = $this->actingAs($this->admin)->get(route('maba.index'));
        $responseAdmin->assertStatus(200);

        $responseOperator = $this->actingAs($this->operator)->get(route('maba.index'));
        $responseOperator->assertStatus(200);
    }

    public function test_maba_index_defaults_to_active_year()
    {
        $maba2026 = MabaData::create([
            'tahun_maba_id' => $this->tahun2026->id,
            'nama_biro' => 'Ahmad 2026',
            'program_studi_biro' => 'Informatika',
            'status_biodata' => 'BELUM_MENGISI',
        ]);

        $maba2027 = MabaData::create([
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Budi 2027',
            'program_studi_biro' => 'Informatika',
            'status_biodata' => 'BELUM_MENGISI',
        ]);

        $response = $this->actingAs($this->admin)->get(route('maba.index'));
        $response->assertStatus(200);
        $response->assertSee('Budi 2027');
        $response->assertDontSee('Ahmad 2026');
    }

    public function test_maba_index_search_and_filters()
    {
        $maba1 = MabaData::create([
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Ahmad Dahlan',
            'program_studi_biro' => 'Teknik Informatika',
            'status_biodata' => 'MENUNGGU_VERIFIKASI',
        ]);

        $maba2 = MabaData::create([
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Siti Badriah',
            'program_studi_biro' => 'Arsitektur',
            'status_biodata' => 'TERVERIFIKASI',
        ]);

        // Search test
        $resSearch = $this->actingAs($this->admin)->get(route('maba.index', ['search' => 'Ahmad']));
        $resSearch->assertSee('Ahmad Dahlan');
        $resSearch->assertDontSee('Siti Badriah');

        // Filter status test
        $resStatus = $this->actingAs($this->admin)->get(route('maba.index', ['status' => 'TERVERIFIKASI']));
        $resStatus->assertSee('Siti Badriah');
        $resStatus->assertDontSee('Ahmad Dahlan');

        // Filter prodi test
        $resProdi = $this->actingAs($this->admin)->get(route('maba.index', ['prodi' => 'Arsitektur']));
        $resProdi->assertSee('Siti Badriah');
        $resProdi->assertDontSee('Ahmad Dahlan');
    }

    public function test_maba_detail_page_access_and_timeline()
    {
        $maba = MabaData::create([
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Cut Nyak Dhien',
            'program_studi_biro' => 'Farmasi',
            'status_biodata' => 'TERVERIFIKASI',
            'nik' => '1171012345670001',
            'email' => 'cutnyak@example.com',
        ]);

        $response = $this->actingAs($this->operator)->get(route('maba.show', $maba));
        $response->assertStatus(200);
        $response->assertSee('Cut Nyak Dhien');
        $response->assertSee('Farmasi');
        $response->assertSee('1171012345670001');
        $response->assertSee('Status Timeline');
    }

    public function test_rekap_jadwal_and_rekap_prodi_page()
    {
        MabaData::create([
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Maba Test 1',
            'program_studi_biro' => 'Informatika',
            'tanggal_jadwal' => '2027-09-15',
            'sesi_jadwal' => 'Sesi 1',
            'status_biodata' => 'TERVERIFIKASI',
        ]);

        $resJadwal = $this->actingAs($this->admin)->get(route('maba.rekapJadwal'));
        $resJadwal->assertStatus(200);
        $resJadwal->assertSee('Sesi 1');

        $resProdi = $this->actingAs($this->admin)->get(route('maba.rekapProdi'));
        $resProdi->assertStatus(200);
        $resProdi->assertSee('Informatika');
    }

    public function test_export_excel_with_filters()
    {
        MabaData::create([
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Export Maba 1',
            'program_studi_biro' => 'Kedokteran',
            'status_biodata' => 'TERVERIFIKASI',
        ]);

        $response = $this->actingAs($this->admin)->get(route('maba.exportExcel', ['tahun_masuk' => '2027', 'prodi' => 'Kedokteran']));
        $response->assertStatus(200);
        $this->assertTrue(str_contains(strtolower($response->headers->get('content-disposition')), 'data_maba_2027'));
    }

    public function test_catatan_perbaikan_workflow()
    {
        $maba = MabaData::create([
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Maba Perbaikan',
            'program_studi_biro' => 'Biologi',
            'status_biodata' => 'MENUNGGU_VERIFIKASI',
        ]);

        // Operator requests correction with note
        $resCorrection = $this->actingAs($this->operator)->post(route('admin.maba-verifikasi.request-correction', $maba), [
            'catatan_perbaikan' => 'NIK kurang 1 digit, harap perbaiki.',
        ]);

        $resCorrection->assertRedirect(route('admin.maba-verifikasi.index'));

        $maba->refresh();
        $this->assertEquals('PERLU_PERBAIKAN', $maba->status_biodata);
        $this->assertEquals('NIK kurang 1 digit, harap perbaiki.', $maba->catatan_perbaikan);

        // Logout internal user to simulate public Maba accessing portal
        auth()->logout();

        // Maba claims record via portal claim endpoint
        $resClaim = $this->post(route('portal.claim'), ['maba_id' => $maba->id]);
        $resClaim->assertRedirect(route('portal.biodata'));
        
        $resForm = $this->get(route('portal.biodata'));
        $resForm->assertStatus(200);
        $resForm->assertSee('NIK kurang 1 digit, harap perbaiki.');
    }

    public function test_maba_cannot_edit_biodata_once_verified()
    {
        $maba = MabaData::create([
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Maba Verified',
            'program_studi_biro' => 'Biologi',
            'status_biodata' => 'TERVERIFIKASI',
        ]);

        $resClaim = $this->post(route('portal.claim'), ['maba_id' => $maba->id]);
        $resClaim->assertRedirect(route('portal.index'));
        $resClaim->assertSessionHas('error');
    }

    public function test_request_correction_requires_catatan_perbaikan()
    {
        $maba = MabaData::create([
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Maba Validation Test',
            'program_studi_biro' => 'Fisika',
            'status_biodata' => 'MENUNGGU_VERIFIKASI',
        ]);

        $resCorrection = $this->actingAs($this->operator)->post(route('admin.maba-verifikasi.request-correction', $maba), [
            'catatan_perbaikan' => '',
        ]);

        $resCorrection->assertSessionHasErrors(['catatan_perbaikan']);
        $maba->refresh();
        $this->assertEquals('MENUNGGU_VERIFIKASI', $maba->status_biodata);
    }

    public function test_resubmit_clears_active_catatan_perbaikan()
    {
        $maba = MabaData::create([
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Maba Resubmit',
            'program_studi_biro' => 'Informatika',
            'status_biodata' => 'PERLU_PERBAIKAN',
            'catatan_perbaikan' => 'Lengkapi alamat Anda.',
        ]);

        auth()->logout();
        $this->post(route('portal.claim'), ['maba_id' => $maba->id]);

        $resSubmit = $this->post(route('portal.biodata.submit'), [
            'nama_lengkap' => 'Maba Resubmit Fixed',
            'nik' => '1171012345670088',
            'email' => 'mabaresubmit@example.com',
            'tempat_lahir' => 'Banda Aceh',
            'tanggal_lahir' => '2005-05-15',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'fakultas' => 'Fakultas Sains dan Teknologi',
            'alamat' => 'Jl. Syiah Kuala No. 10',
            'konfirmasi' => '1',
        ]);

        $resSubmit->assertRedirect(route('portal.success'));
        $maba->refresh();
        $this->assertEquals('MENUNGGU_VERIFIKASI', $maba->status_biodata);
        $this->assertNull($maba->catatan_perbaikan);
    }

    public function test_antrean_pemeriksaan_entry_rules()
    {
        $mabaBelum = MabaData::create([
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Maba Belum Mengisi',
            'program_studi_biro' => 'Kimia',
            'status_biodata' => 'BELUM_MENGISI',
        ]);

        $mabaVerified = MabaData::create([
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Maba Ready Periksa',
            'program_studi_biro' => 'Kimia',
            'status_biodata' => 'TERVERIFIKASI',
        ]);

        // Attempt to open examination creation for BELUM_MENGISI -> blocked
        $resBelum = $this->actingAs($this->operator)->get(route('pemeriksaan.create', ['maba_id' => $mabaBelum->id]));
        $resBelum->assertRedirect(route('admin.maba-verifikasi.index'));

        // Attempt to open examination creation for TERVERIFIKASI -> allowed
        $resVerified = $this->actingAs($this->operator)->get(route('pemeriksaan.create', ['maba_id' => $mabaVerified->id]));
        $resVerified->assertStatus(200);
        $resVerified->assertSee('Maba Ready Periksa');
    }

    public function test_legacy_pemeriksaan_records_remain_safe()
    {
        $legacy = Pemeriksaan::create([
            'maba_data_id' => null,
            'tahun_masuk' => 2026,
            'nomor_surat' => '172/Un.08/PPKES/09/2026',
            'nama' => 'Pasien Legacy 2026',
            'email' => 'legacy@example.com',
            'nik' => '1171000000000001',
            'kesimpulan' => 'Sehat',
            'status_pengiriman' => 'Belum dikirim',
        ]);

        $resIndex = $this->actingAs($this->operator)->get(route('pemeriksaan.index', ['tahun_masuk' => '2026']));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Pasien Legacy 2026');

        $resDetail = $this->actingAs($this->operator)->get(route('pemeriksaan.show', $legacy));
        $resDetail->assertStatus(200);
        $resDetail->assertSee('172/Un.08/PPKES/09/2026');
    }

    public function test_idor_detail_maba_cross_year_mismatch_returns_404()
    {
        $maba2026 = MabaData::create([
            'tahun_maba_id' => $this->tahun2026->id,
            'nama_biro' => 'Maba Cross 2026',
            'program_studi_biro' => 'Biologi',
            'status_biodata' => 'BELUM_MENGISI',
        ]);

        $maba2027 = MabaData::create([
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Maba Cross 2027',
            'program_studi_biro' => 'Biologi',
            'status_biodata' => 'BELUM_MENGISI',
        ]);

        // Attempting to access Maba 2026 under context year 2027 must return 404
        $res1 = $this->actingAs($this->operator)->get(route('maba.show', ['mabaData' => $maba2026->id, 'tahun' => 2027]));
        $res1->assertStatus(404);

        // Attempting to access Maba 2027 under context year 2026 must return 404
        $res2 = $this->actingAs($this->operator)->get(route('maba.show', ['mabaData' => $maba2027->id, 'tahun' => 2026]));
        $res2->assertStatus(404);
    }

    public function test_export_year_bypass_is_strictly_enforced()
    {
        MabaData::create([
            'tahun_maba_id' => $this->tahun2026->id,
            'nama_biro' => 'Maba Secret 2026',
            'program_studi_biro' => 'Teknik',
            'status_biodata' => 'TERVERIFIKASI',
        ]);

        MabaData::create([
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Maba Target 2027',
            'program_studi_biro' => 'Teknik',
            'status_biodata' => 'TERVERIFIKASI',
        ]);

        // Export under context 2027 must not contain 2026 records
        $response = $this->actingAs($this->admin)->get(route('maba.exportExcel', ['tahun' => 2027]));
        $response->assertStatus(200);
        $this->assertTrue(str_contains(strtolower($response->headers->get('content-disposition')), 'data_maba_2027'));
    }

    public function test_public_portal_cross_session_isolation()
    {
        $mabaA = MabaData::create([
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Maba Session A',
            'program_studi_biro' => 'Informatika',
            'status_biodata' => 'BELUM_MENGISI',
        ]);

        $mabaB = MabaData::create([
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Maba Session B',
            'program_studi_biro' => 'Informatika',
            'status_biodata' => 'PERLU_PERBAIKAN',
            'catatan_perbaikan' => 'Catatan Rahasia Maba B',
        ]);

        auth()->logout();

        // Session A claims Maba A
        $this->post(route('portal.claim'), ['maba_id' => $mabaA->id]);

        // Session A attempts to view form -> must see Maba A data, NOT Maba B secret note
        $resForm = $this->get(route('portal.biodata'));
        $resForm->assertStatus(200);
        $resForm->assertDontSee('Catatan Rahasia Maba B');
    }

    public function test_public_portal_mass_assignment_protection()
    {
        $maba = MabaData::create([
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Maba Mass Test',
            'program_studi_biro' => 'Biologi Biro',
            'status_biodata' => 'BELUM_MENGISI',
        ]);

        auth()->logout();
        $this->post(route('portal.claim'), ['maba_id' => $maba->id]);

        // Malicious client payload trying to overwrite server-owned fields
        $payload = [
            'nama_lengkap' => 'Maba Mass Test Validated',
            'nik' => '1171012345679999',
            'email' => 'masstest@example.com',
            'tempat_lahir' => 'Banda Aceh',
            'tanggal_lahir' => '2005-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'fakultas' => 'Fakultas Sains',
            'alamat' => 'Jl. Kebun',
            'konfirmasi' => '1',
            // Attempted overrides:
            'program_studi_biro' => 'HACKED_PRODI',
            'status_biodata' => 'TERVERIFIKASI',
            'catatan_perbaikan' => 'HACKED_NOTE',
        ];

        $this->post(route('portal.biodata.submit'), $payload);

        $maba->refresh();
        $this->assertEquals('Biologi Biro', $maba->program_studi_biro);
        $this->assertEquals('MENUNGGU_VERIFIKASI', $maba->status_biodata);
        $this->assertNull($maba->catatan_perbaikan);
    }
}
