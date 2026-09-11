<?php

namespace Tests\Feature;

use App\Models\Pemeriksaan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MabaYearIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake(); // Prevent actual email jobs execution during test
    }

    public function test_existing_data_and_new_records_have_tahun_masuk()
    {
        $operator = User::factory()->create(['role' => 'operator', 'is_active' => true]);

        $record2026 = Pemeriksaan::create([
            'tahun_masuk' => 2026,
            'nama' => 'Maba 2026 Student',
            'email' => 'maba2026@uin.ac.id',
            'kesimpulan' => 'SEHAT',
            'nomor_surat' => '0172/Un.08/PPKES/09/2026'
        ]);

        $record2027 = Pemeriksaan::create([
            'tahun_masuk' => 2027,
            'nama' => 'Maba 2027 Student',
            'email' => 'maba2027@uin.ac.id',
            'kesimpulan' => 'SEHAT',
            'nomor_surat' => '0173/Un.08/PPKES/09/2027'
        ]);

        $this->assertEquals(2026, $record2026->tahun_masuk);
        $this->assertEquals(2027, $record2027->tahun_masuk);
    }

    public function test_filter_2026_only_returns_2026_data()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        Pemeriksaan::create(['tahun_masuk' => 2026, 'nama' => 'Student 2026 A', 'email' => 'a2026@uin.ac.id', 'nomor_surat' => '0172/Un.08/PPKES/09/2026']);
        Pemeriksaan::create(['tahun_masuk' => 2026, 'nama' => 'Student 2026 B', 'email' => 'b2026@uin.ac.id', 'nomor_surat' => '0173/Un.08/PPKES/09/2026']);
        Pemeriksaan::create(['tahun_masuk' => 2027, 'nama' => 'Student 2027 X', 'email' => 'x2027@uin.ac.id', 'nomor_surat' => '0174/Un.08/PPKES/09/2027']);

        $response = $this->actingAs($admin)->get('/pemeriksaan?tahun_masuk=2026');
        $response->assertStatus(200);
        $response->assertSee('Student 2026 A');
        $response->assertSee('Student 2026 B');
        $response->assertDontSee('Student 2027 X');
    }

    public function test_filter_2027_only_returns_2027_data()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        Pemeriksaan::create(['tahun_masuk' => 2026, 'nama' => 'Student 2026 A', 'email' => 'a2026@uin.ac.id', 'nomor_surat' => '0172/Un.08/PPKES/09/2026']);
        Pemeriksaan::create(['tahun_masuk' => 2027, 'nama' => 'Student 2027 X', 'email' => 'x2027@uin.ac.id', 'nomor_surat' => '0173/Un.08/PPKES/09/2027']);

        $response = $this->actingAs($admin)->get('/pemeriksaan?tahun_masuk=2027');
        $response->assertStatus(200);
        $response->assertSee('Student 2027 X');
        $response->assertDontSee('Student 2026 A');
    }

    public function test_create_from_context_uses_correct_tahun_masuk()
    {
        $operator = User::factory()->create(['role' => 'operator', 'is_active' => true]);
        \App\Models\TahunMaba::create(['tahun' => 2027, 'nama' => 'Maba 2027', 'is_active' => false]);
        \App\Services\TahunMabaService::activateYear(2027);

        $response = $this->actingAs($operator)->get('/pemeriksaan/create');
        $response->assertStatus(200);
        $response->assertSee('value="2027"', false);

        $postResponse = $this->actingAs($operator)->post('/pemeriksaan', [
            'nama' => 'New 2027 Student',
            'email' => 'new2027@uin.ac.id',
            'kesimpulan' => 'SEHAT DAN TIDAK BUTA WARNA'
        ]);

        $postResponse->assertRedirect();
        $this->assertDatabaseHas('pemeriksaans', [
            'nama' => 'New 2027 Student',
            'tahun_masuk' => 2027
        ]);
    }

    public function test_search_and_tahun_work_together()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        Pemeriksaan::create(['tahun_masuk' => 2026, 'nama' => 'Ahmad Syahputra', 'email' => 'ahmad2026@uin.ac.id', 'nomor_surat' => '0172/Un.08/PPKES/09/2026']);
        Pemeriksaan::create(['tahun_masuk' => 2027, 'nama' => 'Ahmad Dahlan', 'email' => 'ahmad2027@uin.ac.id', 'nomor_surat' => '0173/Un.08/PPKES/09/2027']);

        $response = $this->actingAs($admin)->get('/pemeriksaan?tahun_masuk=2027&search=Ahmad');
        $response->assertStatus(200);
        $response->assertSee('Ahmad Dahlan');
        $response->assertDontSee('Ahmad Syahputra');
    }

    public function test_export_excel_and_pdf_respect_tahun_masuk_filter()
    {
        $operator = User::factory()->create(['role' => 'operator', 'is_active' => true]);

        Pemeriksaan::create(['tahun_masuk' => 2026, 'nama' => 'Student 2026 A', 'email' => 'a2026@uin.ac.id', 'nomor_surat' => '0172/Un.08/PPKES/09/2026']);
        Pemeriksaan::create(['tahun_masuk' => 2027, 'nama' => 'Student 2027 X', 'email' => 'x2027@uin.ac.id', 'nomor_surat' => '0173/Un.08/PPKES/09/2027']);

        $responseExcel = $this->actingAs($operator)->get('/pemeriksaan/export/excel?tahun_masuk=2027');
        $responseExcel->assertStatus(200);

        $responsePdf = $this->actingAs($operator)->get('/pemeriksaan/export/pdf?tahun_masuk=2027');
        $responsePdf->assertStatus(200);
    }

    public function test_invalid_tahun_masuk_is_rejected_on_backend()
    {
        $operator = User::factory()->create(['role' => 'operator', 'is_active' => true]);

        $response = $this->actingAs($operator)->post('/pemeriksaan', [
            'tahun_masuk' => 1900, // Invalid year
            'nama' => 'Fake Year Student',
            'email' => 'fake@uin.ac.id'
        ]);

        $response->assertSessionHasErrors(['tahun_masuk']);
    }

    public function test_nomor_surat_preserved_and_unaffected_by_tahun_masuk()
    {
        $operator = User::factory()->create(['role' => 'operator', 'is_active' => true]);

        $record = Pemeriksaan::create([
            'tahun_masuk' => 2026,
            'nama' => 'Existing Maba 2026',
            'email' => 'existing2026@uin.ac.id',
            'nomor_surat' => '0172/Un.08/PPKES/09/2026',
            'kesimpulan' => 'SEHAT'
        ]);

        $this->assertEquals('0172/Un.08/PPKES/09/2026', $record->nomor_surat);
        $this->assertEquals(2026, $record->tahun_masuk);
    }
}
