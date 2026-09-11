<?php

namespace Tests\Feature;

use App\Models\Pemeriksaan;
use App\Models\TahunMaba;
use App\Models\User;
use App\Services\TahunMabaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OperatorYearSelectionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->operator = User::factory()->create([
            'role' => 'operator',
            'is_active' => true,
        ]);

        TahunMaba::firstOrCreate(['tahun' => 2026], ['nama' => 'Maba 2026', 'is_active' => true]);
        TahunMaba::firstOrCreate(['tahun' => 2027], ['nama' => 'Maba 2027', 'is_active' => false]);
        TahunMaba::firstOrCreate(['tahun' => 2028], ['nama' => 'Maba 2028', 'is_active' => false]);
    }

    /** 1, 2, 3. Operator dapat melihat tahun 2026, 2027, 2028 */
    public function test_operator_can_view_data_for_any_year(): void
    {
        Pemeriksaan::create(['nama' => 'Student 2026', 'email' => 's2026@test.com', 'tahun_masuk' => 2026]);
        Pemeriksaan::create(['nama' => 'Student 2027', 'email' => 's2027@test.com', 'tahun_masuk' => 2027]);
        Pemeriksaan::create(['nama' => 'Student 2028', 'email' => 's2028@test.com', 'tahun_masuk' => 2028]);

        // View 2026
        $res2026 = $this->actingAs($this->operator)->get('/pemeriksaan?tahun_masuk=2026');
        $res2026->assertStatus(200);
        $res2026->assertSee('Student 2026');
        $res2026->assertDontSee('Student 2027');

        // View 2027
        $res2027 = $this->actingAs($this->operator)->get('/pemeriksaan?tahun_masuk=2027');
        $res2027->assertStatus(200);
        $res2027->assertSee('Student 2027');
        $res2027->assertDontSee('Student 2026');

        // View 2028
        $res2028 = $this->actingAs($this->operator)->get('/pemeriksaan?tahun_masuk=2028');
        $res2028->assertStatus(200);
        $res2028->assertSee('Student 2028');
        $res2028->assertDontSee('Student 2026');
    }

    /** 4. Selected year menggunakan numeric value */
    public function test_selected_year_uses_numeric_value(): void
    {
        $response = $this->actingAs($this->operator)->get('/pemeriksaan?tahun_masuk=2027');
        $response->assertStatus(200);
        $response->assertViewHas('selectedTahun', '2027');
    }

    /** 5. Search tetap terisolasi berdasarkan selected year */
    public function test_search_isolated_by_selected_year(): void
    {
        Pemeriksaan::create(['nama' => 'Ahmad 2026', 'email' => 'a26@test.com', 'tahun_masuk' => 2026]);
        Pemeriksaan::create(['nama' => 'Ahmad 2027', 'email' => 'a27@test.com', 'tahun_masuk' => 2027]);

        $response = $this->actingAs($this->operator)->get('/pemeriksaan?tahun_masuk=2026&search=Ahmad');
        $response->assertSee('Ahmad 2026');
        $response->assertDontSee('Ahmad 2027');
    }

    /** 6. Pagination mempertahankan selected year */
    public function test_pagination_retains_selected_year(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            Pemeriksaan::create(['nama' => "Student 2026 {$i}", 'email' => "s26_{$i}@test.com", 'tahun_masuk' => 2026]);
        }

        $response = $this->actingAs($this->operator)->get('/pemeriksaan?tahun_masuk=2026&page=2');
        $response->assertStatus(200);
        $response->assertSee('tahun_masuk=2026');
    }

    /** 7. Detail record historis dapat dibuka */
    public function test_historical_detail_can_be_viewed(): void
    {
        TahunMabaService::activateYear(2027);
        $rec2026 = Pemeriksaan::create(['nama' => 'Historical 2026', 'email' => 'h26@test.com', 'tahun_masuk' => 2026]);

        $response = $this->actingAs($this->operator)->get("/pemeriksaan/{$rec2026->id}");
        $response->assertStatus(200);
        $response->assertSee('Historical 2026');
    }

    /** 8 & 9. Edit record historis dapat dilakukan, namun tahun_masuk tidak diubah */
    public function test_historical_edit_allowed_but_tahun_masuk_cannot_be_changed(): void
    {
        TahunMabaService::activateYear(2027);
        $rec2026 = Pemeriksaan::create(['nama' => 'Original Name', 'email' => 'orig@test.com', 'tahun_masuk' => 2026]);

        $editRes = $this->actingAs($this->operator)->get("/pemeriksaan/{$rec2026->id}/edit");
        $editRes->assertStatus(200);

        $updateRes = $this->actingAs($this->operator)->put("/pemeriksaan/{$rec2026->id}", [
            'nama' => 'Updated Name',
            'email' => 'orig@test.com',
            'tahun_masuk' => 2028, // Client payload manipulation attempt
        ]);

        $updateRes->assertRedirect();
        $this->assertDatabaseHas('pemeriksaans', [
            'id' => $rec2026->id,
            'nama' => 'Updated Name',
            'tahun_masuk' => 2026, // Must remain 2026
        ]);
    }

    /** 10, 11, 12. Operator tidak dapat membuat/mengaktifkan/mengubah active year */
    public function test_operator_cannot_manage_years(): void
    {
        $resStore = $this->actingAs($this->operator)->post('/admin/tahun-maba', ['tahun' => 2029]);
        $resStore->assertStatus(403);

        $tahun2027 = TahunMaba::where('tahun', 2027)->first();
        $resActivate = $this->actingAs($this->operator)->post("/admin/tahun-maba/{$tahun2027->id}/activate");
        $resActivate->assertStatus(403);
    }

    /** 13, 14, 15, 16. Create data selalu menggunakan active year (ditentukan Admin) */
    public function test_create_data_always_uses_active_year_regardless_of_selected_view_year(): void
    {
        // Case 1: Active year = 2027, Selected view year = 2026 -> New record = 2027
        TahunMabaService::activateYear(2027);
        $res1 = $this->actingAs($this->operator)->post('/pemeriksaan', [
            'nama' => 'Maba Test 1',
            'email' => 'm1@test.com',
            'tahun_masuk' => 2026, // Client request manipulation attempt
        ]);
        $res1->assertRedirect();
        $this->assertDatabaseHas('pemeriksaans', ['nama' => 'Maba Test 1', 'tahun_masuk' => 2027]);

        // Case 2: Active year = 2028, Selected view year = 2026 -> New record = 2028
        TahunMabaService::activateYear(2028);
        $res2 = $this->actingAs($this->operator)->post('/pemeriksaan', [
            'nama' => 'Maba Test 2',
            'email' => 'm2@test.com',
            'tahun_masuk' => 2026,
        ]);
        $res2->assertRedirect();
        $this->assertDatabaseHas('pemeriksaans', ['nama' => 'Maba Test 2', 'tahun_masuk' => 2028]);

        // Case 3: Active year = 2028, Selected view year = 2027 -> New record = 2028
        $res3 = $this->actingAs($this->operator)->post('/pemeriksaan', [
            'nama' => 'Maba Test 3',
            'email' => 'm3@test.com',
            'tahun_masuk' => 2027,
        ]);
        $res3->assertRedirect();
        $this->assertDatabaseHas('pemeriksaans', ['nama' => 'Maba Test 3', 'tahun_masuk' => 2028]);
    }

    /** 17 & 18. Export Excel & PDF menggunakan selected year */
    public function test_export_uses_selected_view_year(): void
    {
        Pemeriksaan::create(['nama' => 'Ex 2026', 'email' => 'ex26@test.com', 'tahun_masuk' => 2026]);
        Pemeriksaan::create(['nama' => 'Ex 2027', 'email' => 'ex27@test.com', 'tahun_masuk' => 2027]);

        TahunMabaService::activateYear(2028);

        $resExcel = $this->actingAs($this->operator)->get('/pemeriksaan/export/excel?tahun_masuk=2026');
        $resExcel->assertStatus(200);

        $resPdf = $this->actingAs($this->operator)->get('/pemeriksaan/export/pdf?tahun_masuk=2026');
        $resPdf->assertStatus(200);
    }

    /** 19. Pengiriman menggunakan selected year */
    public function test_pengiriman_uses_selected_view_year(): void
    {
        Pemeriksaan::create(['nama' => 'Send 2026', 'email' => 'p26@test.com', 'tahun_masuk' => 2026]);
        $rec2027 = Pemeriksaan::create(['nama' => 'Send 2027', 'email' => 'p27@test.com', 'tahun_masuk' => 2027]);

        TahunMabaService::activateYear(2028);

        $response = $this->actingAs($this->operator)->get('/pengiriman?tahun_masuk=2027');
        $response->assertStatus(200);
        $response->assertViewHas('pengiriman', function ($paginator) use ($rec2027) {
            return $paginator->contains('id', $rec2027->id) && $paginator->total() === 1;
        });
    }

    /** 20. Queue tetap menggunakan record ID dan tidak terpengaruh perubahan active year */
    public function test_queue_retains_record_id_unaffected_by_active_year_switch(): void
    {
        $rec2026 = Pemeriksaan::create(['nama' => 'Queue Test', 'email' => 'q26@test.com', 'tahun_masuk' => 2026]);

        $this->actingAs($this->operator)->post("/pemeriksaan/{$rec2026->id}/send-email");

        // Switch active year to 2028
        TahunMabaService::activateYear(2028);

        Queue::assertPushed(\App\Jobs\SendSuratSehatJob::class, function ($job) use ($rec2026) {
            return $job->pemeriksaan->id === $rec2026->id && $job->pemeriksaan->tahun_masuk === 2026;
        });
    }
}
