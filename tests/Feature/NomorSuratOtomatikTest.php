<?php

namespace Tests\Feature;

use App\Models\Pemeriksaan;
use App\Models\TahunMaba;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NomorSuratOtomatikTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        TahunMaba::firstOrCreate(
            ['tahun' => 2026],
            ['nama' => 'Maba 2026', 'is_active' => true, 'nomor_surat_mulai' => 172]
        );
    }

    private function createAdmin(): User
    {
        $user = User::create([
            'name' => 'Admin Test',
            'email' => 'admin_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
        ]);
        $user->role = 'admin';
        $user->is_active = true;
        $user->save();
        return $user;
    }

    private function createOperator(): User
    {
        $user = User::create([
            'name' => 'Operator Test',
            'email' => 'operator_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
        ]);
        $user->role = 'operator';
        $user->is_active = true;
        $user->save();
        return $user;
    }

    public function test_admin_can_view_and_update_starting_certificate_number_and_format()
    {
        $admin = $this->createAdmin();
        $tahun2027 = TahunMaba::create([
            'tahun' => 2027,
            'nama' => 'Maba 2027',
            'is_active' => false,
            'nomor_surat_mulai' => 172,
            'kode_unit' => 'Un.08',
            'kode_bagian' => 'PPKES',
            'tahun_surat' => 2027,
        ]);

        $response = $this->actingAs($admin)->get('/admin/tahun-maba');
        $response->assertStatus(200);
        $response->assertSee('Nomor Surat Mulai');
        $response->assertSee('Kode Unit');
        $response->assertSee('Kode Bagian');
        $response->assertSee('Tahun Surat');

        $updateResponse = $this->actingAs($admin)->put("/admin/tahun-maba/{$tahun2027->id}/format", [
            'nomor_surat_mulai' => 5000,
            'kode_unit' => 'Un.09',
            'kode_bagian' => 'KLINIK',
            'tahun_surat' => 2028,
        ]);

        $updateResponse->assertRedirect('/admin/tahun-maba');
        $updateResponse->assertSessionHas('success');

        $this->assertDatabaseHas('tahun_mabas', [
            'id' => $tahun2027->id,
            'nomor_surat_mulai' => 5000,
            'kode_unit' => 'Un.09',
            'kode_bagian' => 'KLINIK',
            'tahun_surat' => 2028,
        ]);
    }

    public function test_operator_cannot_access_or_modify_starting_certificate_number_or_format()
    {
        $operator = $this->createOperator();
        $tahun2027 = TahunMaba::create([
            'tahun' => 2027,
            'nama' => 'Maba 2027',
            'is_active' => false,
            'nomor_surat_mulai' => 172,
            'kode_unit' => 'Un.08',
            'kode_bagian' => 'PPKES',
            'tahun_surat' => 2027,
        ]);

        $response = $this->actingAs($operator)->get('/admin/tahun-maba');
        $response->assertStatus(403);

        $updateResponse = $this->actingAs($operator)->put("/admin/tahun-maba/{$tahun2027->id}/format", [
            'nomor_surat_mulai' => 5000,
            'kode_unit' => 'HACK',
            'kode_bagian' => 'HACK',
            'tahun_surat' => 2099,
        ]);
        $updateResponse->assertStatus(403);

        $this->assertDatabaseHas('tahun_mabas', [
            'id' => $tahun2027->id,
            'nomor_surat_mulai' => 172,
            'kode_unit' => 'Un.08',
            'kode_bagian' => 'PPKES',
            'tahun_surat' => 2027,
        ]);
    }

    public function test_validation_for_starting_certificate_number_and_kode_unit_bagian_tahun_surat()
    {
        $admin = $this->createAdmin();
        $tahun2027 = TahunMaba::create([
            'tahun' => 2027,
            'nama' => 'Maba 2027',
            'is_active' => false,
            'nomor_surat_mulai' => 172,
            'tahun_surat' => 2027,
        ]);

        // Invalid number
        $res1 = $this->actingAs($admin)->put("/admin/tahun-maba/{$tahun2027->id}/format", [
            'nomor_surat_mulai' => 0,
            'kode_unit' => 'Un.08',
            'kode_bagian' => 'PPKES',
            'tahun_surat' => 2027,
        ]);
        $res1->assertSessionHasErrors(['nomor_surat_mulai']);

        // Slash in kode_unit / kode_bagian is rejected
        $res2 = $this->actingAs($admin)->put("/admin/tahun-maba/{$tahun2027->id}/format", [
            'nomor_surat_mulai' => 100,
            'kode_unit' => 'Un/08',
            'kode_bagian' => 'PPKES',
            'tahun_surat' => 2027,
        ]);
        $res2->assertSessionHasErrors(['kode_unit']);

        // Invalid tahun_surat
        $res3 = $this->actingAs($admin)->put("/admin/tahun-maba/{$tahun2027->id}/format", [
            'nomor_surat_mulai' => 100,
            'kode_unit' => 'Un.08',
            'kode_bagian' => 'PPKES',
            'tahun_surat' => 1999,
        ]);
        $res3->assertSessionHasErrors(['tahun_surat']);
    }

    public function test_generator_respects_custom_format_unit_bagian_and_tahun_surat()
    {
        $admin = $this->createAdmin();
        $operator = $this->createOperator();

        // Create Maba 2027 with starting number 5000, Unit Un.08, Bagian PPKES, Tahun Surat 2027 and activate it
        $tahun2027 = TahunMaba::create([
            'tahun' => 2027,
            'nama' => 'Maba 2027',
            'is_active' => false,
            'nomor_surat_mulai' => 5000,
            'kode_unit' => 'Un.08',
            'kode_bagian' => 'PPKES',
            'tahun_surat' => 2027,
        ]);

        $this->actingAs($admin)->post("/admin/tahun-maba/{$tahun2027->id}/activate");

        // First record for Maba 2027
        $p1Data = [
            'nama' => 'Maba Satu',
            'email' => 'mabasatu@example.com',
            'nim' => '270001',
            'nik' => '1111222233334441',
            'tempat_lahir' => 'Banda Aceh',
            'tanggal_lahir' => '2005-01-01',
            'jenis_kelamin' => 'L',
            'program_studi' => 'Teknik Informatika',
            'fakultas' => 'Sains dan Teknologi',
            'tinggi_badan' => 170,
            'berat_badan' => 65,
            'sistole' => 120,
            'diastole' => 80,
            'golongan_darah' => 'O',
            'buta_warna' => 'Tidak',
            'hasil_pemeriksaan' => 'Sehat',
            'keperluan' => 'Syarat Daftar Ulang',
            'dokter_nama' => 'dr. Desminawati',
            'dokter_nip' => '198002062010012007',
            'tahun_masuk' => 2027,
        ];

        $res = $this->actingAs($operator)->post('/pemeriksaan', $p1Data);
        $res->assertSessionHasNoErrors();

        $p1 = Pemeriksaan::where('tahun_masuk', 2027)->first();

        $this->assertNotNull($p1);
        $month = date('m');
        $this->assertEquals("5000/Un.08/PPKES/{$month}/2027", $p1->nomor_surat);
    }
}
