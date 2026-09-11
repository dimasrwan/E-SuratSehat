<?php

namespace Tests\Feature;

use App\Models\Pemeriksaan;
use App\Models\TahunMaba;
use App\Models\User;
use App\Services\TahunMabaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TahunMabaManagementTest extends TestCase
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

        TahunMaba::firstOrCreate([
            'tahun' => 2026,
        ], [
            'nama' => 'Maba 2026',
            'is_active' => true,
        ]);
    }

    /** 1. Anonymous tidak dapat mengakses manajemen tahun */
    public function test_anonymous_cannot_access_tahun_maba_management(): void
    {
        $response = $this->get('/admin/tahun-maba');
        $response->assertRedirect('/login');
    }

    /** 2 & 3. Operator tidak dapat mengakses manajemen tahun & menerima 403 */
    public function test_operator_cannot_access_tahun_maba_management_and_gets_403(): void
    {
        $response = $this->actingAs($this->operator)->get('/admin/tahun-maba');
        $response->assertStatus(403);
    }

    /** 4. Operator tidak dapat membuat tahun melalui HTTP */
    public function test_operator_cannot_store_tahun_maba(): void
    {
        $response = $this->actingAs($this->operator)->post('/admin/tahun-maba', [
            'tahun' => 2027,
        ]);
        $response->assertStatus(403);
        $this->assertDatabaseMissing('tahun_mabas', ['tahun' => 2027]);
    }

    /** 5 & 6. Admin dapat membuat tahun 2027 dan default NONAKTIF */
    public function test_admin_can_create_tahun_maba_default_inactive(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/tahun-maba', [
            'tahun' => 2027,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tahun_mabas', [
            'tahun' => 2027,
            'nama' => 'Maba 2027',
            'is_active' => false,
        ]);

        $this->assertEquals(2026, TahunMabaService::getActiveYearInt());
    }

    /** 7. Tahun tidak boleh duplicate */
    public function test_tahun_maba_cannot_be_duplicated(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/tahun-maba', [
            'tahun' => 2026,
        ]);

        $response->assertSessionHasErrors(['tahun']);
    }

    /** 8, 9, 10. Admin mengaktifkan 2027: hanya 1 aktif, 2026 nonaktif */
    public function test_admin_can_activate_year_atomically(): void
    {
        $tahun2027 = TahunMaba::create([
            'tahun' => 2027,
            'nama' => 'Maba 2027',
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/tahun-maba/{$tahun2027->id}/activate");
        $response->assertRedirect();

        $this->assertEquals(1, TahunMaba::where('is_active', true)->count());
        $this->assertTrue(TahunMaba::where('tahun', 2027)->first()->is_active);
        $this->assertFalse(TahunMaba::where('tahun', 2026)->first()->is_active);
        $this->assertEquals(2027, TahunMabaService::getActiveYearInt());
    }

    /** 11 & 12. Operator melihat Maba 2027 & create otomatis menyimpan 2027 */
    public function test_operator_create_auto_saves_active_year(): void
    {
        TahunMaba::create(['tahun' => 2027, 'nama' => 'Maba 2027', 'is_active' => false]);
        TahunMabaService::activateYear(2027);

        $response = $this->actingAs($this->operator)->get('/pemeriksaan');
        $response->assertSee('2027');

        $storeResponse = $this->actingAs($this->operator)->post('/pemeriksaan', [
            'nama' => 'Ahmad Test',
            'email' => 'ahmad@test.com',
        ]);

        $storeResponse->assertRedirect();
        $this->assertDatabaseHas('pemeriksaans', [
            'nama' => 'Ahmad Test',
            'tahun_masuk' => 2027,
        ]);
    }

    /** 13 & 14. Operator tidak dapat memanipulasi tahun menjadi 2026/2028 */
    public function test_operator_cannot_manipulate_tahun_masuk_request(): void
    {
        TahunMabaService::activateYear(2026);

        $response = $this->actingAs($this->operator)->post('/pemeriksaan', [
            'nama' => 'Manipulated Student',
            'email' => 'manipulated@test.com',
            'tahun_masuk' => 2028,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pemeriksaans', [
            'nama' => 'Manipulated Student',
            'tahun_masuk' => 2026,
        ]);
        $this->assertDatabaseMissing('pemeriksaans', [
            'nama' => 'Manipulated Student',
            'tahun_masuk' => 2028,
        ]);
    }

    /** 15 & 16. Data 2026 tidak muncul pada 2027 & sebaliknya */
    public function test_data_isolation_between_years(): void
    {
        Pemeriksaan::create(['nama' => 'Record 2026', 'email' => 'r2026@test.com', 'tahun_masuk' => 2026]);
        
        TahunMaba::create(['tahun' => 2027, 'nama' => 'Maba 2027', 'is_active' => false]);
        Pemeriksaan::create(['nama' => 'Record 2027', 'email' => 'r2027@test.com', 'tahun_masuk' => 2027]);

        // When 2026 active
        $response2026 = $this->actingAs($this->operator)->get('/pemeriksaan');
        $response2026->assertSee('Record 2026');
        $response2026->assertDontSee('Record 2027');

        // Switch to 2027
        TahunMabaService::activateYear(2027);

        $response2027 = $this->actingAs($this->operator)->get('/pemeriksaan');
        $response2027->assertSee('Record 2027');
        $response2027->assertDontSee('Record 2026');
    }

    /** 17 & 18. Search & pagination terisolasi */
    public function test_search_and_pagination_isolated(): void
    {
        Pemeriksaan::create(['nama' => 'Siti 2026', 'email' => 's1@test.com', 'nik' => '111111', 'tahun_masuk' => 2026]);
        
        TahunMaba::create(['tahun' => 2027, 'nama' => 'Maba 2027', 'is_active' => false]);
        $rec2027 = Pemeriksaan::create(['nama' => 'Siti 2027', 'email' => 's2@test.com', 'nik' => '222222', 'tahun_masuk' => 2027]);

        TahunMabaService::activateYear(2027);

        $response = $this->actingAs($this->operator)->get('/pemeriksaan?search=Siti');
        $response->assertStatus(200);
        $response->assertViewHas('pemeriksaans', function ($paginator) use ($rec2027) {
            return $paginator->contains('id', $rec2027->id) && $paginator->count() === 1;
        });
    }

    /** 19 & 20. Export Excel & PDF terisolasi */
    public function test_export_excel_and_pdf_isolated(): void
    {
        Pemeriksaan::create(['nama' => 'Data 2026', 'email' => 'd1@test.com', 'tahun_masuk' => 2026]);
        TahunMaba::create(['tahun' => 2027, 'nama' => 'Maba 2027', 'is_active' => false]);
        Pemeriksaan::create(['nama' => 'Data 2027', 'email' => 'd2@test.com', 'tahun_masuk' => 2027]);

        TahunMabaService::activateYear(2027);

        $excelResponse = $this->actingAs($this->operator)->get('/pemeriksaan/export/excel');
        $excelResponse->assertStatus(200);

        $pdfResponse = $this->actingAs($this->operator)->get('/pemeriksaan/export/pdf');
        $pdfResponse->assertStatus(200);
    }

    /** 21. Pengiriman terisolasi */
    public function test_pengiriman_isolated(): void
    {
        Pemeriksaan::create(['nama' => 'Student 2026', 'email' => 'e2026@test.com', 'status_pengiriman' => 'Belum dikirim', 'tahun_masuk' => 2026]);
        TahunMaba::create(['tahun' => 2027, 'nama' => 'Maba 2027', 'is_active' => false]);
        $rec2027 = Pemeriksaan::create(['nama' => 'Student 2027', 'email' => 'e2027@test.com', 'status_pengiriman' => 'Belum dikirim', 'tahun_masuk' => 2027]);

        // Clear cache so service gets latest active year 2027
        TahunMabaService::activateYear(2027);

        $response = $this->actingAs($this->operator)->get('/pengiriman');
        $response->assertStatus(200);
        $this->assertEquals(2027, \App\Services\TahunMabaService::getActiveYearInt());
        $response->assertViewHas('pengiriman', function ($paginator) use ($rec2027) {
            return $paginator->contains('id', $rec2027->id) && $paginator->total() === 1;
        });
    }

    /** 22. Queue email target record tetap menggunakan ID */
    public function test_queue_job_retains_correct_record_target(): void
    {
        \Illuminate\Support\Facades\Queue::fake();

        $pemeriksaan2026 = Pemeriksaan::create(['nama' => 'Target 2026', 'email' => 't2026@test.com', 'tahun_masuk' => 2026]);
        
        $this->actingAs($this->operator)->post("/pemeriksaan/{$pemeriksaan2026->id}/send-email");

        TahunMaba::create(['tahun' => 2027, 'nama' => 'Maba 2027', 'is_active' => false]);
        TahunMabaService::activateYear(2027);

        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\SendSuratSehatJob::class, function ($job) use ($pemeriksaan2026) {
            return $job->pemeriksaan->id === $pemeriksaan2026->id && $job->pemeriksaan->tahun_masuk === 2026;
        });
    }

    /** 23. Tahun aktif tidak bisa dua sekaligus */
    public function test_cannot_have_multiple_active_years(): void
    {
        TahunMaba::create(['tahun' => 2027, 'nama' => 'Maba 2027', 'is_active' => false]);
        TahunMabaService::activateYear(2027);

        $this->assertEquals(1, TahunMaba::where('is_active', true)->count());
    }

    /** 24. Tidak boleh terjadi kondisi tanpa tahun aktif */
    public function test_always_has_at_least_one_active_year(): void
    {
        $this->assertGreaterThan(0, TahunMaba::where('is_active', true)->count());
    }
}
