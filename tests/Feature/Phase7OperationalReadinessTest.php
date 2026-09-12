<?php

namespace Tests\Feature;

use App\Models\MabaData;
use App\Models\Pemeriksaan;
use App\Models\TahunMaba;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class Phase7OperationalReadinessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Queue::fake();
    }

    private function createAdminUser(): User
    {
        return User::factory()->create([
            'role' => 'admin',
        ]);
    }

    private function createOperatorUser(): User
    {
        return User::factory()->create([
            'role' => 'operator',
        ]);
    }

    /** @test */
    public function test_1_dashboard_active_year_default_and_aggregated_statistics()
    {
        $admin = $this->createAdminUser();

        $activeYear = TahunMaba::where('is_active', true)->first();
        if (!$activeYear) {
            $activeYear = TahunMaba::create([
                'tahun' => 2026,
                'is_active' => true,
                'nomor_surat_mulai' => 1,
                'kode_unit' => 'Un.08',
                'kode_bagian' => 'Klinik',
                'tahun_surat' => '2026',
            ]);
        }

        // Create Maba entries for active year
        MabaData::create([
            'tahun_maba_id' => $activeYear->id,
            'nama_biro' => 'Maba Active 1',
            'nim_biro' => '990011221',
            'program_studi_biro' => 'Pendidikan Dokter',
            'jenis_kelamin' => 'L',
            'status_biodata' => 'MENUNGGU_VERIFIKASI',
            'tanggal_jadwal' => Carbon::today(),
            'sesi_jadwal' => '1',
        ]);

        MabaData::create([
            'tahun_maba_id' => $activeYear->id,
            'nama_biro' => 'Maba Active 2',
            'nim_biro' => '990011222',
            'program_studi_biro' => 'Farmasi',
            'jenis_kelamin' => 'P',
            'status_biodata' => 'TERVERIFIKASI',
            'tanggal_jadwal' => Carbon::today(),
            'sesi_jadwal' => '1',
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Operational Maba ' . $activeYear->tahun);
        $response->assertSee('Pemeriksaan Hari Ini');
        $response->assertSee('Quick Action Operator');
    }

    /** @test */
    public function test_2_dashboard_today_schedule_breakdown()
    {
        $operator = $this->createOperatorUser();

        $activeYear = TahunMaba::where('is_active', true)->first();
        if (!$activeYear) {
            $activeYear = TahunMaba::create([
                'tahun' => 2026,
                'is_active' => true,
                'nomor_surat_mulai' => 1,
                'kode_unit' => 'Un.08',
                'kode_bagian' => 'Klinik',
                'tahun_surat' => '2026',
            ]);
        }

        // Maba scheduled for today
        MabaData::create([
            'tahun_maba_id' => $activeYear->id,
            'nama_biro' => 'Maba Today Verified',
            'nim_biro' => '880011221',
            'program_studi_biro' => 'Biologi',
            'jenis_kelamin' => 'L',
            'status_biodata' => 'TERVERIFIKASI',
            'tanggal_jadwal' => Carbon::today()->format('Y-m-d'),
            'sesi_jadwal' => '1',
        ]);

        $response = $this->actingAs($operator)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Pemeriksaan Hari Ini');
        $response->assertSee('Total Terjadwal');
        $response->assertSee('Siap Diperiksa');
    }

    /** @test */
    public function test_3_pemeriksaan_queue_category_tabs()
    {
        $operator = $this->createOperatorUser();

        $activeYear = TahunMaba::where('is_active', true)->first();

        // Create verified Maba (Belum Diperiksa)
        $mabaVer = MabaData::create([
            'tahun_maba_id' => $activeYear->id,
            'nama_biro' => 'Maba Ready For Exam',
            'nim_biro' => '770011221',
            'program_studi_biro' => 'Teknik Informatika',
            'jenis_kelamin' => 'L',
            'status_biodata' => 'TERVERIFIKASI',
            'tanggal_jadwal' => Carbon::today(),
        ]);

        // Tab: Belum Diperiksa
        $response1 = $this->actingAs($operator)->get('/pemeriksaan?status_antrean=belum_diperiksa');
        $response1->assertStatus(200);
        $response1->assertSee('Maba Ready For Exam');
        $response1->assertSee('Mulai Pemeriksaan');

        // Tab: Pemeriksaan Selesai
        $response2 = $this->actingAs($operator)->get('/pemeriksaan?status_antrean=selesai');
        $response2->assertStatus(200);
        $response2->assertSee('Pemeriksaan Selesai');
    }

    /** @test */
    public function test_4_pemeriksaan_search_functionality()
    {
        $operator = $this->createOperatorUser();

        $activeYear = TahunMaba::where('is_active', true)->first();

        $mabaSearch = MabaData::create([
            'tahun_maba_id' => $activeYear->id,
            'nama_biro' => 'Zulfa Unique Name',
            'nim_biro' => '660011221',
            'program_studi_biro' => 'Arsitektur',
            'jenis_kelamin' => 'P',
            'nik' => '1171019988770001',
            'status_biodata' => 'TERVERIFIKASI',
        ]);

        // Search by Name
        $responseName = $this->actingAs($operator)->get('/pemeriksaan?status_antrean=belum_diperiksa&search=Zulfa');
        $responseName->assertStatus(200);
        $responseName->assertSee('Zulfa Unique Name');

        // Search by NIK
        $responseNik = $this->actingAs($operator)->get('/pemeriksaan?status_antrean=belum_diperiksa&search=1171019988770001');
        $responseNik->assertStatus(200);
        $responseNik->assertSee('Zulfa Unique Name');
    }

    /** @test */
    public function test_5_failed_email_resend_with_authorization()
    {
        $operator = $this->createOperatorUser();
        $activeYear = TahunMaba::where('is_active', true)->first();

        $maba = MabaData::create([
            'tahun_maba_id' => $activeYear->id,
            'nama_biro' => 'Email Failed Maba',
            'nim_biro' => '550011221',
            'program_studi_biro' => 'Hukum',
            'jenis_kelamin' => 'L',
            'email' => 'failedmaba@example.com',
            'status_biodata' => 'PEMERIKSAAN_SELESAI',
        ]);

        $pem = Pemeriksaan::create([
            'maba_data_id' => $maba->id,
            'tahun_masuk' => $activeYear->tahun,
            'nama' => 'Email Failed Maba',
            'nik' => '1171019988770002',
            'email' => 'failedmaba@example.com',
            'program_studi' => 'Hukum',
            'fakultas' => 'Fakultas Hukum',
            'jenis_kelamin' => 'Laki-Laki',
            'tanggal_lahir' => '2004-05-10',
            'nomor_surat' => 'Un.08/Klinik/0010/2026',
            'kesimpulan' => 'SEHAT',
            'status_pengiriman' => 'Gagal',
        ]);

        $resend = $this->actingAs($operator)->post("/pemeriksaan/{$pem->id}/send-email");
        $resend->assertRedirect();
        $resend->assertSessionHas('success');

        $this->assertEquals('Dalam antrean', $pem->fresh()->status_pengiriman);
    }

    /** @test */
    public function test_6_public_blocked_from_internal_pages()
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/maba')->assertRedirect('/login');
        $this->get('/pemeriksaan')->assertRedirect('/login');
        $this->get('/admin/import')->assertRedirect('/login');
        $this->get('/pemeriksaan/export-excel')->assertRedirect('/login');
    }

    /** @test */
    public function test_7_operator_blocked_from_admin_only_user_management()
    {
        $operator = $this->createOperatorUser();

        $this->actingAs($operator)->get('/admin/users')->assertStatus(403);
        $this->actingAs($operator)->get('/admin/import')->assertStatus(403);
    }

    /** @test */
    public function test_8_cross_year_isolation_enforced()
    {
        $operator = $this->createOperatorUser();

        $year2026 = TahunMaba::where('tahun', 2026)->first() ?? TahunMaba::create([
            'tahun' => 2026,
            'nama' => 'Tahun 2026',
            'is_active' => true,
            'nomor_surat_mulai' => 1,
            'kode_unit' => 'Un.08',
            'kode_bagian' => 'Klinik',
            'tahun_surat' => '2026',
        ]);

        $year2027 = TahunMaba::where('tahun', 2027)->first() ?? TahunMaba::create([
            'tahun' => 2027,
            'nama' => 'Tahun 2027',
            'is_active' => false,
            'nomor_surat_mulai' => 1,
            'kode_unit' => 'Un.08',
            'kode_bagian' => 'Klinik',
            'tahun_surat' => '2027',
        ]);

        $maba2027 = MabaData::create([
            'tahun_maba_id' => $year2027->id,
            'nama_biro' => 'Maba Year 2027',
            'nim_biro' => '440011221',
            'program_studi_biro' => 'Kimia',
            'jenis_kelamin' => 'P',
            'status_biodata' => 'TERVERIFIKASI',
        ]);

        // Attempting to access Maba 2027 while filtering year 2026
        $response = $this->actingAs($operator)->get("/maba/{$maba2027->id}?tahun_masuk=2026");
        $response->assertStatus(404);
    }

    /** @test */
    public function test_9_legacy_examination_records_remain_functional()
    {
        $operator = $this->createOperatorUser();

        // Legacy record with maba_data_id = NULL
        $legacyPem = Pemeriksaan::create([
            'maba_data_id' => null,
            'tahun_masuk' => 2024,
            'nama' => 'Legacy Student Record',
            'nik' => '1171019988779999',
            'email' => 'legacystudent@example.com',
            'program_studi' => 'Teknik Sipil',
            'fakultas' => 'Fakultas Teknik',
            'jenis_kelamin' => 'Laki-Laki',
            'tanggal_lahir' => '2002-01-01',
            'nomor_surat' => 'Un.08/Klinik/9999/2024',
            'kesimpulan' => 'SEHAT',
            'status_pengiriman' => 'Terkirim',
        ]);

        // Legacy detail viewable
        $responseDetail = $this->actingAs($operator)->get("/pemeriksaan/{$legacyPem->id}");
        $responseDetail->assertStatus(200);
        $responseDetail->assertSee('Legacy Student Record');

        // Legacy PDF downloadable
        $responsePdf = $this->actingAs($operator)->get("/pemeriksaan/{$legacyPem->id}/download");
        $responsePdf->assertStatus(200);
    }

    /** @test */
    public function test_10_duplicate_examination_creation_blocked()
    {
        $operator = $this->createOperatorUser();
        $activeYear = TahunMaba::where('is_active', true)->first();

        $maba = MabaData::create([
            'tahun_maba_id' => $activeYear->id,
            'nama_biro' => 'Maba Single Exam',
            'nim_biro' => '330011221',
            'program_studi_biro' => 'Biologi',
            'jenis_kelamin' => 'L',
            'status_biodata' => 'TERVERIFIKASI',
        ]);

        // Create first examination
        Pemeriksaan::create([
            'maba_data_id' => $maba->id,
            'tahun_masuk' => $activeYear->tahun,
            'nama' => 'Maba Single Exam',
            'nik' => '1171019988771234',
            'email' => 'singleexam@example.com',
            'program_studi' => 'Biologi',
            'fakultas' => 'Fakultas MIPA',
            'jenis_kelamin' => 'Laki-Laki',
            'tanggal_lahir' => '2004-01-01',
            'nomor_surat' => 'Un.08/Klinik/0020/2026',
            'kesimpulan' => 'SEHAT',
            'status_pengiriman' => 'Terkirim',
        ]);

        // Updating maba status to PEMERIKSAAN_SELESAI
        $maba->update(['status_biodata' => 'PEMERIKSAAN_SELESAI']);

        // Attempt second examination submission
        $examData = [
            'maba_data_id' => $maba->id,
            'tahun_masuk' => $activeYear->tahun,
            'nama' => 'Maba Single Exam',
            'nik' => '1171019988771234',
            'email' => 'singleexam@example.com',
            'program_studi' => 'Biologi',
            'fakultas' => 'Fakultas MIPA',
            'jenis_kelamin' => 'Laki-Laki',
            'tanggal_lahir' => '2004-01-01',
            'tinggi_badan' => 170,
            'berat_badan' => 60,
            'tekanan_darah' => '120/80',
            'denyut_nadi' => 80,
            'laju_pernapasan' => 18,
            'golongan_darah' => 'O',
            'buta_warna' => 'Tidak',
            'merokok' => 'Tidak',
            'alkohol' => 'Tidak',
            'tato' => 'Tidak',
            'tindik' => 'Tidak',
            'kesimpulan' => 'SEHAT',
        ];

        $response = $this->actingAs($operator)->post('/pemeriksaan', $examData);
        $response->assertRedirect();
        $response->assertSessionHasErrors(['maba_data_id']);
    }
}
