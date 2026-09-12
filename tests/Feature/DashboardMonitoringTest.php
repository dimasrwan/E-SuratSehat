<?php

namespace Tests\Feature;

use App\Models\MabaData;
use App\Models\Pemeriksaan;
use App\Models\TahunMaba;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardMonitoringTest extends TestCase
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
            'name' => 'Admin Dashboard',
            'email' => 'admin.dash@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->operator = User::create([
            'name' => 'Operator Dashboard',
            'email' => 'operator.dash@test.com',
            'password' => bcrypt('password'),
            'role' => 'operator',
        ]);
    }

    public function test_guest_cannot_access_dashboard()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_admin_can_access_dashboard_with_active_year_default()
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard Operational Maba 2027');
        $response->assertSee('Admin Dashboard');
    }

    public function test_operator_can_access_dashboard()
    {
        $response = $this->actingAs($this->operator)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard Operational Maba 2027');
        $response->assertSee('Operator Dashboard');
    }

    public function test_dashboard_accurately_aggregates_maba_lifecycle_statistics()
    {
        // Active Year 2027 Maba Data
        MabaData::create(['tahun_maba_id' => $this->activeYear->id, 'nama_biro' => 'A1', 'program_studi_biro' => 'Informatika', 'status_biodata' => 'BELUM_MENGISI']);
        MabaData::create(['tahun_maba_id' => $this->activeYear->id, 'nama_biro' => 'A2', 'program_studi_biro' => 'Informatika', 'status_biodata' => 'MENUNGGU_VERIFIKASI']);
        MabaData::create(['tahun_maba_id' => $this->activeYear->id, 'nama_biro' => 'A3', 'program_studi_biro' => 'Biologi', 'status_biodata' => 'PERLU_PERBAIKAN']);
        MabaData::create(['tahun_maba_id' => $this->activeYear->id, 'nama_biro' => 'A4', 'program_studi_biro' => 'Biologi', 'status_biodata' => 'TERVERIFIKASI']);
        MabaData::create(['tahun_maba_id' => $this->activeYear->id, 'nama_biro' => 'A5', 'program_studi_biro' => 'Kimia', 'status_biodata' => 'PEMERIKSAAN_SELESAI']);

        // Inactive Year 2026 Maba Data (Should be isolated)
        MabaData::create(['tahun_maba_id' => $this->inactiveYear->id, 'nama_biro' => 'B1', 'program_studi_biro' => 'Informatika', 'status_biodata' => 'BELUM_MENGISI']);

        $response = $this->actingAs($this->admin)->get('/dashboard');
        $response->assertStatus(200);

        $response->assertViewHas('totalMaba', 5);
        $response->assertViewHas('belumMengisi', 1);
        $response->assertViewHas('menungguVerifikasi', 1);
        $response->assertViewHas('perluPerbaikan', 1);
        $response->assertViewHas('terverifikasi', 1);
        $response->assertViewHas('pemeriksaanSelesai', 1);
        $response->assertViewHas('progressPercentage', 20.0);
    }

    public function test_year_filter_switches_dashboard_context()
    {
        MabaData::create(['tahun_maba_id' => $this->inactiveYear->id, 'nama_biro' => 'Old Maba', 'program_studi_biro' => 'Fisika', 'status_biodata' => 'PEMERIKSAAN_SELESAI']);

        $response = $this->actingAs($this->operator)->get('/dashboard?tahun_masuk=2026');
        $response->assertStatus(200);
        $response->assertSee('Dashboard Operational Maba 2026');
        $response->assertViewHas('totalMaba', 1);
        $response->assertViewHas('pemeriksaanSelesai', 1);
    }

    public function test_legacy_pemeriksaan_without_maba_data_does_not_cause_dashboard_errors()
    {
        Pemeriksaan::create([
            'tahun_masuk' => 2025,
            'nomor_surat' => '0099/Un.08/PPKES/05/2025',
            'nama' => 'Legacy Student',
            'email' => 'legacy@example.com',
            'status_pengiriman' => 'Terkirim',
            'maba_data_id' => null,
        ]);

        $response = $this->actingAs($this->admin)->get('/dashboard?tahun_masuk=2025');
        $response->assertStatus(200);
        $response->assertViewHas('totalPemeriksaan', 1);
        $response->assertViewHas('emailTerkirim', 1);
    }

    public function test_empty_state_is_rendered_safely_when_no_maba_exists()
    {
        MabaData::query()->delete();

        $response = $this->actingAs($this->admin)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertViewHas('totalMaba', 0);
        $response->assertViewHas('progressPercentage', 0);
        $response->assertSee('Tidak ada Maba yang menunggu verifikasi');
    }
}
