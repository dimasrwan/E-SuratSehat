<?php

namespace Tests\Feature;

use App\Models\MabaData;
use App\Models\Pemeriksaan;
use App\Models\TahunMaba;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MabaEditTest extends TestCase
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

    public function test_guest_cannot_access_maba_edit_form_or_update()
    {
        $maba = MabaData::create([
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Guest Block Maba',
            'program_studi_biro' => 'Informatika',
            'status_biodata' => 'BELUM_MENGISI',
        ]);

        $this->get(route('maba.edit', $maba))->assertRedirect(route('login'));
        $this->put(route('maba.update', $maba), ['nama_lengkap' => 'Hacked'])->assertRedirect(route('login'));
    }

    public function test_admin_and_operator_can_open_maba_edit_form()
    {
        $maba = MabaData::create([
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Maba Edit View Test',
            'program_studi_biro' => 'Biologi',
            'status_biodata' => 'MENUNGGU_VERIFIKASI',
        ]);

        $resAdmin = $this->actingAs($this->admin)->get(route('maba.edit', $maba));
        $resAdmin->assertStatus(200);
        $resAdmin->assertSee('Edit Data Maba');

        $resOperator = $this->actingAs($this->operator)->get(route('maba.edit', $maba));
        $resOperator->assertStatus(200);
        $resOperator->assertSee('Edit Data Maba');
    }

    public function test_admin_and_operator_can_update_maba_biodata()
    {
        $maba = MabaData::create([
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Maba Update Test',
            'program_studi_biro' => 'Farmasi',
            'status_biodata' => 'MENUNGGU_VERIFIKASI',
        ]);

        $payload = [
            'nama_lengkap' => 'Maba Update Validated',
            'nik' => '1171012345678888',
            'email' => 'mabaupdated@example.com',
            'tempat_lahir' => 'Banda Aceh',
            'tanggal_lahir' => '2005-08-20',
            'jenis_kelamin' => 'Perempuan',
            'agama' => 'Islam',
            'fakultas' => 'Fakultas Kedokteran dan Ilmu Kesehatan',
            'pekerjaan' => 'Mahasiswa',
            'alamat' => 'Jl. Darussalam No. 12',
        ];

        $response = $this->actingAs($this->operator)->put(route('maba.update', $maba), $payload);
        $response->assertRedirect(route('maba.show', $maba));

        $maba->refresh();
        $this->assertEquals('Maba Update Validated', $maba->nama_lengkap);
        $this->assertEquals('1171012345678888', $maba->nik);
        $this->assertEquals('mabaupdated@example.com', $maba->email);
        $this->assertEquals('P', $maba->jenis_kelamin);
    }

    public function test_nik_validation_rules()
    {
        $maba = MabaData::create([
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Maba NIK Test',
            'program_studi_biro' => 'Kimia',
            'status_biodata' => 'BELUM_MENGISI',
        ]);

        $basePayload = [
            'nama_lengkap' => 'Maba NIK Test',
            'email' => 'niktest@example.com',
            'tempat_lahir' => 'Banda Aceh',
            'tanggal_lahir' => '2005-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'fakultas' => 'Fakultas Sains dan Teknologi',
            'pekerjaan' => 'Mahasiswa',
            'alamat' => 'Jl. Kebun',
        ];

        // 15 digits (short)
        $resShort = $this->actingAs($this->operator)->put(route('maba.update', $maba), array_merge($basePayload, ['nik' => '123456789012345']));
        $resShort->assertSessionHasErrors(['nik']);

        // Non-numeric digits
        $resAlpha = $this->actingAs($this->operator)->put(route('maba.update', $maba), array_merge($basePayload, ['nik' => '123456789012345a']));
        $resAlpha->assertSessionHasErrors(['nik']);
    }

    public function test_future_birthdate_rejected()
    {
        $maba = MabaData::create([
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Maba Future Date Test',
            'program_studi_biro' => 'Fisika',
            'status_biodata' => 'BELUM_MENGISI',
        ]);

        $payload = [
            'nama_lengkap' => 'Maba Future Date',
            'nik' => '1171012345677777',
            'email' => 'futuredate@example.com',
            'tempat_lahir' => 'Banda Aceh',
            'tanggal_lahir' => now()->addDays(5)->format('Y-m-d'),
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'fakultas' => 'Fakultas Sains dan Teknologi',
            'pekerjaan' => 'Mahasiswa',
            'alamat' => 'Jl. Kebun',
        ];

        $response = $this->actingAs($this->operator)->put(route('maba.update', $maba), $payload);
        $response->assertSessionHasErrors(['tanggal_lahir']);
    }

    public function test_mass_assignment_protection_server_owned_fields()
    {
        $maba = MabaData::create([
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Maba Biro Original',
            'program_studi_biro' => 'Arsitektur Biro',
            'status_biodata' => 'BELUM_MENGISI',
        ]);

        $maliciousPayload = [
            'nama_lengkap' => 'Maba Mass Test',
            'nik' => '1171012345679999',
            'email' => 'masstest@example.com',
            'tempat_lahir' => 'Banda Aceh',
            'tanggal_lahir' => '2005-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'fakultas' => 'Fakultas Sains dan Teknologi',
            'pekerjaan' => 'Mahasiswa',
            'alamat' => 'Jl. Kebun',
            // Attempted server-owned field overrides:
            'tahun_maba_id' => $this->tahun2026->id,
            'nama_biro' => 'HACKED_NAME_BIRO',
            'program_studi_biro' => 'HACKED_PRODI',
            'status_biodata' => 'PEMERIKSAAN_SELESAI',
            'catatan_perbaikan' => 'HACKED_NOTE',
        ];

        $this->actingAs($this->admin)->put(route('maba.update', $maba), $maliciousPayload);

        $maba->refresh();
        $this->assertEquals($this->tahun2027->id, $maba->tahun_maba_id);
        $this->assertEquals('Maba Biro Original', $maba->nama_biro);
        $this->assertEquals('Arsitektur Biro', $maba->program_studi_biro);
        $this->assertEquals('MENUNGGU_VERIFIKASI', $maba->status_biodata);
        $this->assertNull($maba->catatan_perbaikan);
    }

    public function test_edit_perlu_perbaikan_resets_to_menunggu_verifikasi_and_clears_note()
    {
        $maba = MabaData::create([
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Maba Correction Workflow',
            'program_studi_biro' => 'Informatika',
            'status_biodata' => 'PERLU_PERBAIKAN',
            'catatan_perbaikan' => 'Alamat kurang lengkap',
        ]);

        $payload = [
            'nama_lengkap' => 'Maba Correction Fixed',
            'nik' => '1171012345671111',
            'email' => 'fixed@example.com',
            'tempat_lahir' => 'Banda Aceh',
            'tanggal_lahir' => '2005-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'fakultas' => 'Fakultas Sains dan Teknologi',
            'pekerjaan' => 'Mahasiswa',
            'alamat' => 'Jl. Syiah Kuala No. 20 Banda Aceh',
        ];

        $this->actingAs($this->operator)->put(route('maba.update', $maba), $payload);

        $maba->refresh();
        $this->assertEquals('MENUNGGU_VERIFIKASI', $maba->status_biodata);
        $this->assertNull($maba->catatan_perbaikan);
        $this->assertNull($maba->verified_at);
        $this->assertNull($maba->verified_by);
    }

    public function test_edit_verified_maba_resets_verification_status_without_deleting_examination()
    {
        $maba = MabaData::create([
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Maba Exam Owner',
            'program_studi_biro' => 'Kedokteran',
            'status_biodata' => 'PEMERIKSAAN_SELESAI',
            'verified_at' => now(),
            'verified_by' => $this->operator->id,
        ]);

        $pemeriksaan = Pemeriksaan::create([
            'maba_data_id' => $maba->id,
            'tahun_masuk' => 2027,
            'nomor_surat' => '001/Un.08/PPKES/09/2027',
            'nama' => 'Maba Exam Owner',
            'email' => 'examowner@example.com',
            'kesimpulan' => 'Sehat',
            'status_pengiriman' => 'Terkirim',
        ]);

        $payload = [
            'nama_lengkap' => 'Maba Exam Owner Correction',
            'nik' => '1171012345672222',
            'email' => 'examowner@example.com',
            'tempat_lahir' => 'Banda Aceh',
            'tanggal_lahir' => '2005-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'fakultas' => 'Fakultas Kedokteran dan Ilmu Kesehatan',
            'pekerjaan' => 'Mahasiswa',
            'alamat' => 'Jl. Lampineung',
        ];

        $this->actingAs($this->admin)->put(route('maba.update', $maba), $payload);

        $maba->refresh();
        $this->assertEquals('MENUNGGU_VERIFIKASI', $maba->status_biodata);
        $this->assertNull($maba->verified_at);
        $this->assertNull($maba->verified_by);

        // Crucial Safety Assertions: Examination record & metadata MUST REMAIN UNTOUCHED
        $this->assertDatabaseHas('pemeriksaans', [
            'id' => $pemeriksaan->id,
            'maba_data_id' => $maba->id,
            'nomor_surat' => '001/Un.08/PPKES/09/2027',
            'kesimpulan' => 'Sehat',
            'status_pengiriman' => 'Terkirim',
        ]);
    }

    public function test_cross_year_edit_attempt_returns_404()
    {
        $maba2026 = MabaData::create([
            'tahun_maba_id' => $this->tahun2026->id,
            'nama_biro' => 'Maba 2026 Cross Year',
            'program_studi_biro' => 'Teknik',
            'status_biodata' => 'BELUM_MENGISI',
        ]);

        $payload = [
            'nama_lengkap' => 'Cross Year Attack',
            'nik' => '1171012345673333',
            'email' => 'cross@example.com',
            'tempat_lahir' => 'Banda Aceh',
            'tanggal_lahir' => '2005-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'fakultas' => 'Fakultas Sains dan Teknologi',
            'pekerjaan' => 'Mahasiswa',
            'alamat' => 'Jl. Kebun',
        ];

        // Accessing edit form under context year 2027 must return 404
        $resForm = $this->actingAs($this->operator)->get(route('maba.edit', ['mabaData' => $maba2026->id, 'tahun' => 2027]));
        $resForm->assertStatus(404);

        // PUT request under context year 2027 must return 404
        $resPut = $this->actingAs($this->operator)->put(route('maba.update', ['mabaData' => $maba2026->id, 'tahun' => 2027]), $payload);
        $resPut->assertStatus(404);
    }
}
