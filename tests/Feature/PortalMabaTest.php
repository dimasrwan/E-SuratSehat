<?php

namespace Tests\Feature;

use App\Models\MabaData;
use App\Models\Pemeriksaan;
use App\Models\TahunMaba;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalMabaTest extends TestCase
{
    use RefreshDatabase;

    protected TahunMaba $activeYear;
    protected TahunMaba $inactiveYear;
    protected User $admin;
    protected User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        TahunMaba::query()->update(['is_active' => false]);

        $this->activeYear = TahunMaba::firstOrCreate(['tahun' => 2027], [
            'nama' => 'Maba 2027',
            'is_active' => true,
            'nomor_surat_mulai' => 172,
            'kode_unit' => 'Un.08',
            'kode_bagian' => 'PPKES',
            'tahun_surat' => 2027,
        ]);
        $this->activeYear->update(['is_active' => true]);

        $this->inactiveYear = TahunMaba::firstOrCreate(['tahun' => 2026], [
            'nama' => 'Maba 2026',
            'is_active' => false,
            'nomor_surat_mulai' => 100,
            'kode_unit' => 'Un.08',
            'kode_bagian' => 'PPKES',
            'tahun_surat' => 2026,
        ]);

        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->operator = User::create([
            'name' => 'Operator Test',
            'email' => 'operator@test.com',
            'password' => bcrypt('password'),
            'role' => 'operator',
        ]);
    }

    public function test_public_portal_can_be_accessed_without_authentication()
    {
        $response = $this->get('/portal-maba');
        $response->assertStatus(200);
        $response->assertSee('Portal Mahasiswa Baru');
        $response->assertSee('Maba 2027');
    }

    public function test_search_only_queries_active_year_data()
    {
        $activeMaba = MabaData::create([
            'tahun_maba_id' => $this->activeYear->id,
            'nama_biro' => 'Ahmad Fulan',
            'program_studi_biro' => 'Teknik Informatika',
            'status_biodata' => 'BELUM_MENGISI',
        ]);

        $inactiveMaba = MabaData::create([
            'tahun_maba_id' => $this->inactiveYear->id,
            'nama_biro' => 'Ahmad Fulan',
            'program_studi_biro' => 'Teknik Informatika',
            'status_biodata' => 'BELUM_MENGISI',
        ]);

        $response = $this->get('/portal-maba/search?nama=Ahmad&program_studi=Teknik+Informatika');
        $response->assertStatus(200);
        $response->assertSee('Ahmad Fulan');
        
        // Active Maba record should be found in active year context
        $results = $response->viewData('results');
        $this->assertCount(1, $results);
        $this->assertEquals($activeMaba->id, $results->first()->id);
    }

    public function test_search_does_not_leak_sensitive_fields_or_internal_biodata_status()
    {
        MabaData::create([
            'tahun_maba_id' => $this->activeYear->id,
            'nama_biro' => 'Siti Rahmah',
            'program_studi_biro' => 'Farmasi',
            'nik' => '1171012304990001',
            'email' => 'siti@mahasiswa.ac.id',
            'alamat' => 'Jl. Kebenaran No. 12',
            'status_biodata' => 'BELUM_MENGISI',
        ]);

        $response = $this->get('/portal-maba/search?nama=Siti');
        $response->assertStatus(200);
        $response->assertDontSee('1171012304990001');
        $response->assertDontSee('siti@mahasiswa.ac.id');
        $response->assertDontSee('Jl. Kebenaran');
        $response->assertDontSee('BELUM_MENGISI');
    }

    public function test_maba_can_claim_record_and_receive_session_claim()
    {
        $maba = MabaData::create([
            'tahun_maba_id' => $this->activeYear->id,
            'nama_biro' => 'Budi Santoso',
            'program_studi_biro' => 'Arsitektur',
            'status_biodata' => 'BELUM_MENGISI',
        ]);

        $response = $this->post('/portal-maba/claim', [
            'maba_id' => $maba->id,
        ]);

        $response->assertRedirect('/portal-maba/biodata');
        $this->assertNotNull(session('portal_claim'));
        $this->assertEquals($maba->id, session('portal_claim')['maba_data_id']);
    }

    public function test_direct_access_to_biodata_form_without_claim_session_is_rejected()
    {
        $response = $this->get('/portal-maba/biodata');
        $response->assertRedirect('/portal-maba');
        $response->assertSessionHas('error');
    }

    public function test_submitting_biodata_enforces_strict_field_ownership_and_derives_prodi_from_biro()
    {
        $maba = MabaData::create([
            'tahun_maba_id' => $this->activeYear->id,
            'nama_biro' => 'Budi Santoso',
            'program_studi_biro' => 'Arsitektur Biro',
            'status_biodata' => 'BELUM_MENGISI',
        ]);

        // Claim session
        $this->post('/portal-maba/claim', ['maba_id' => $maba->id]);

        // Attempt submit with manipulated prodi in request
        $response = $this->post('/portal-maba/biodata', [
            'nama_lengkap' => 'Budi Santoso Lengkap',
            'nik' => '1171012304990002',
            'email' => 'budi@mahasiswa.ac.id',
            'tempat_lahir' => 'Banda Aceh',
            'tanggal_lahir' => '2004-05-15',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'fakultas' => 'Fakultas Teknik',
            'program_studi' => 'MANIPULATED_PRODI_FROM_BROWSER',
            'pekerjaan' => 'Mahasiswa',
            'alamat' => 'Jl. Syiah Kuala',
            'konfirmasi' => '1',
        ]);

        $response->assertRedirect('/portal-maba/success');

        $maba->refresh();
        $this->assertEquals('MENUNGGU_VERIFIKASI', $maba->status_biodata);
        $this->assertEquals('Arsitektur Biro', $maba->program_studi); // Derived strictly from Biro!
        $this->assertNull($maba->verified_at);
        $this->assertNull($maba->verified_by);
    }

    public function test_operator_can_view_and_verify_maba_identity()
    {
        $maba = MabaData::create([
            'tahun_maba_id' => $this->activeYear->id,
            'nama_biro' => 'Dewi Lestari',
            'program_studi_biro' => 'Biologi',
            'nama_lengkap' => 'Dewi Lestari',
            'nik' => '1171012304990003',
            'email' => 'dewi@mahasiswa.ac.id',
            'tempat_lahir' => 'Aceh Besar',
            'tanggal_lahir' => '2003-08-20',
            'jenis_kelamin' => 'P',
            'agama' => 'Islam',
            'fakultas' => 'Fakultas Sains dan Teknologi',
            'program_studi' => 'Biologi',
            'alamat' => 'Darussalam',
            'status_biodata' => 'MENUNGGU_VERIFIKASI',
        ]);

        // Public users cannot access operator verification pages
        $this->get('/admin/maba-verifikasi')->assertRedirect('/login');

        // Operator logs in
        $response = $this->actingAs($this->operator)->get('/admin/maba-verifikasi');
        $response->assertStatus(200);
        $response->assertSee('Dewi Lestari');

        // Operator verifies identity
        $verifyResponse = $this->actingAs($this->operator)->post("/admin/maba-verifikasi/{$maba->id}/verify");
        $verifyResponse->assertRedirect('/admin/maba-verifikasi');

        $maba->refresh();
        $this->assertEquals('TERVERIFIKASI', $maba->status_biodata);
        $this->assertNotNull($maba->verified_at);
        $this->assertEquals($this->operator->id, $maba->verified_by);
    }

    public function test_operator_can_request_correction_for_maba()
    {
        $maba = MabaData::create([
            'tahun_maba_id' => $this->activeYear->id,
            'nama_biro' => 'Fajar Pratama',
            'program_studi_biro' => 'Fisika',
            'status_biodata' => 'MENUNGGU_VERIFIKASI',
        ]);

        $response = $this->actingAs($this->operator)->post("/admin/maba-verifikasi/{$maba->id}/request-correction", [
            'catatan_perbaikan' => 'Periksa kembali NIK.',
        ]);
        $response->assertRedirect('/admin/maba-verifikasi');

        $maba->refresh();
        $this->assertEquals('PERLU_PERBAIKAN', $maba->status_biodata);
    }

    public function test_one_maba_one_examination_constraint_is_strictly_enforced()
    {
        $maba = MabaData::create([
            'tahun_maba_id' => $this->activeYear->id,
            'nama_biro' => 'Eka Putra',
            'program_studi_biro' => 'Kimia',
            'nama_lengkap' => 'Eka Putra',
            'email' => 'eka@mahasiswa.ac.id',
            'status_biodata' => 'TERVERIFIKASI',
        ]);

        // Operator creates examination 1
        $storeResponse = $this->actingAs($this->operator)->post('/pemeriksaan', [
            'maba_data_id' => $maba->id,
            'nama' => 'Eka Putra',
            'email' => 'eka@mahasiswa.ac.id',
        ]);

        $maba->refresh();
        $this->assertEquals('PEMERIKSAAN_SELESAI', $maba->status_biodata);
        $this->assertEquals(1, $maba->pemeriksaan()->count());

        // Attempting to create examination 2 for the same maba should fail
        $secondResponse = $this->actingAs($this->operator)->post('/pemeriksaan', [
            'maba_data_id' => $maba->id,
            'nama' => 'Eka Putra',
            'email' => 'eka@mahasiswa.ac.id',
        ]);

        $secondResponse->assertSessionHasErrors('maba_data_id');
        $this->assertEquals(1, $maba->pemeriksaan()->count());
    }

    public function test_legacy_pemeriksaan_without_maba_data_id_remains_fully_functional()
    {
        $legacy = Pemeriksaan::create([
            'tahun_masuk' => 2025,
            'nomor_surat' => '0100/Un.08/PPKES/05/2025',
            'nama' => 'Legacy Student',
            'email' => 'legacy@example.com',
            'maba_data_id' => null,
        ]);

        $this->assertNull($legacy->maba_data_id);
        $this->assertEquals('0100/Un.08/PPKES/05/2025', $legacy->nomor_surat);
    }

    public function test_concurrent_claim_and_submit_prevents_overwrite_of_submitted_data()
    {
        $maba = MabaData::create([
            'tahun_maba_id' => $this->activeYear->id,
            'nama_biro' => 'Rahmat Hidayat',
            'program_studi_biro' => 'Informatika',
            'status_biodata' => 'BELUM_MENGISI',
        ]);

        // Browser A claims and submits
        $this->post('/portal-maba/claim', ['maba_id' => $maba->id]);
        $this->post('/portal-maba/biodata', [
            'nama_lengkap' => 'Rahmat Hidayat Original',
            'nik' => '1171012304990005',
            'email' => 'rahmat@mahasiswa.ac.id',
            'tempat_lahir' => 'Banda Aceh',
            'tanggal_lahir' => '2004-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'fakultas' => 'FST',
            'alamat' => 'Jl. Utama',
            'konfirmasi' => '1',
        ]);

        $maba->refresh();
        $this->assertEquals('MENUNGGU_VERIFIKASI', $maba->status_biodata);
        $this->assertEquals('Rahmat Hidayat Original', $maba->nama_lengkap);

        // Browser B trying to claim already submitted data should fail
        $responseB = $this->post('/portal-maba/claim', ['maba_id' => $maba->id]);
        $responseB->assertRedirect('/portal-maba');
        $responseB->assertSessionHas('error');
    }

    public function test_invalid_nik_and_future_birthdate_are_rejected()
    {
        $maba = MabaData::create([
            'tahun_maba_id' => $this->activeYear->id,
            'nama_biro' => 'Validation Test',
            'program_studi_biro' => 'Biologi',
            'status_biodata' => 'BELUM_MENGISI',
        ]);

        $this->post('/portal-maba/claim', ['maba_id' => $maba->id]);

        // Submit with invalid 12-digit NIK and future birth date
        $response = $this->post('/portal-maba/biodata', [
            'nama_lengkap' => 'Validation Test',
            'nik' => '12345', // Short NIK
            'email' => 'invalid-email',
            'tempat_lahir' => 'Banda Aceh',
            'tanggal_lahir' => '2050-01-01', // Future date
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'fakultas' => 'FST',
            'alamat' => 'Jl. Utama',
            'konfirmasi' => '1',
        ]);

        $response->assertSessionHasErrors(['nik', 'email', 'tanggal_lahir']);
    }

    public function test_duplicate_nik_does_not_leak_owner_identity_to_public()
    {
        MabaData::create([
            'tahun_maba_id' => $this->activeYear->id,
            'nama_biro' => 'Mahasiswa First',
            'program_studi_biro' => 'Biologi',
            'nik' => '1171012304990099',
            'status_biodata' => 'MENUNGGU_VERIFIKASI',
        ]);

        $mabaSecond = MabaData::create([
            'tahun_maba_id' => $this->activeYear->id,
            'nama_biro' => 'Mahasiswa Second',
            'program_studi_biro' => 'Biologi',
            'status_biodata' => 'BELUM_MENGISI',
        ]);

        $this->post('/portal-maba/claim', ['maba_id' => $mabaSecond->id]);

        $response = $this->post('/portal-maba/biodata', [
            'nama_lengkap' => 'Mahasiswa Second',
            'nik' => '1171012304990099', // Duplicate NIK
            'email' => 'second@mahasiswa.ac.id',
            'tempat_lahir' => 'Banda Aceh',
            'tanggal_lahir' => '2004-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'fakultas' => 'FST',
            'alamat' => 'Jl. Utama',
            'konfirmasi' => '1',
        ]);

        // Submission succeeds to MENUNGGU_VERIFIKASI without leaking Mahasiswa First info to public
        $response->assertRedirect('/portal-maba/success');
        $response->assertDontSee('Mahasiswa First');

        $mabaSecond->refresh();
        $this->assertEquals('MENUNGGU_VERIFIKASI', $mabaSecond->status_biodata);
    }

    public function test_full_end_to_end_flow_from_public_claim_to_pemeriksaan_completion()
    {
        \Illuminate\Support\Facades\Mail::fake();
        \Illuminate\Support\Facades\Queue::fake();

        // 1. Biro record created via Admin Import
        $maba = MabaData::create([
            'tahun_maba_id' => $this->activeYear->id,
            'nama_biro' => 'End To End Student',
            'program_studi_biro' => 'Teknik Elektro',
            'status_biodata' => 'BELUM_MENGISI',
        ]);

        // 2. Search & Claim
        $this->get('/portal-maba/search?nama=End+To+End')->assertStatus(200)->assertSee('End To End Student');
        $this->post('/portal-maba/claim', ['maba_id' => $maba->id])->assertRedirect('/portal-maba/biodata');

        // 3. Submit Biodata
        $this->post('/portal-maba/biodata', [
            'nama_lengkap' => 'End To End Student',
            'nik' => '1171012304997777',
            'email' => 'e2e@mahasiswa.ac.id',
            'tempat_lahir' => 'Banda Aceh',
            'tanggal_lahir' => '2003-11-11',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'fakultas' => 'Fakultas Sains dan Teknologi',
            'alamat' => 'Jl. Kebenaran',
            'konfirmasi' => '1',
        ])->assertRedirect('/portal-maba/success');

        $maba->refresh();
        $this->assertEquals('MENUNGGU_VERIFIKASI', $maba->status_biodata);

        // 4. Operator Verifies Identity
        $this->actingAs($this->operator)->post("/admin/maba-verifikasi/{$maba->id}/verify")->assertRedirect('/admin/maba-verifikasi');
        $maba->refresh();
        $this->assertEquals('TERVERIFIKASI', $maba->status_biodata);

        // 5. Operator Inputs Medical Examination
        $this->actingAs($this->operator)->post('/pemeriksaan', [
            'maba_data_id' => $maba->id,
            'nama' => $maba->nama_lengkap,
            'email' => $maba->email,
            'nik' => $maba->nik,
            'tempat_lahir' => $maba->tempat_lahir,
            'tanggal_lahir' => $maba->tanggal_lahir->format('Y-m-d'),
            'jenis_kelamin' => 'Laki-Laki',
            'agama' => $maba->agama,
            'fakultas' => $maba->fakultas,
            'alamat' => $maba->alamat,
            'kesimpulan' => 'SEHAT DAN TIDAK BUTA WARNA',
        ]);

        $maba->refresh();
        $this->assertEquals('PEMERIKSAAN_SELESAI', $maba->status_biodata);
        $this->assertNotNull($maba->pemeriksaan);
        $this->assertNotNull($maba->pemeriksaan->nomor_surat);
    }
}
