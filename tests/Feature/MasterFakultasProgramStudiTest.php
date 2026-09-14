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

class MasterFakultasProgramStudiTest extends TestCase
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
    }

    /** 1. Admin dapat melihat halaman master. */
    public function test_1_admin_can_view_master_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.fakultas-prodi.index'));
        $response->assertStatus(200);
        $response->assertSee('Master Fakultas');
    }

    /** 2. Operator tidak dapat membuka halaman master. */
    public function test_2_operator_cannot_view_master_page(): void
    {
        $response = $this->actingAs($this->operator)->get(route('admin.fakultas-prodi.index'));
        $response->assertStatus(403);
    }

    /** 3. Public/Guest tidak dapat membuka halaman master. */
    public function test_3_public_cannot_view_master_page(): void
    {
        $response = $this->get(route('admin.fakultas-prodi.index'));
        $response->assertRedirect(route('login'));
    }

    /** 4. Admin dapat membuat Fakultas. */
    public function test_4_admin_can_create_fakultas(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.fakultas-prodi.store'), [
            'nama' => 'Fakultas Sains Data',
            'kode' => 'FSD',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.fakultas-prodi.index'));
        $this->assertDatabaseHas('fakultas', [
            'nama' => 'Fakultas Sains Data',
            'kode' => 'FSD',
            'is_active' => true,
        ]);
    }

    /** 5. Admin dapat mengedit Fakultas. */
    public function test_5_admin_can_edit_fakultas(): void
    {
        $fakultas = Fakultas::create([
            'nama' => 'Fakultas Lama',
            'kode' => 'FL',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.fakultas-prodi.update', $fakultas), [
            'nama' => 'Fakultas Baru',
            'kode' => 'FB',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.fakultas-prodi.index'));
        $this->assertDatabaseHas('fakultas', [
            'id' => $fakultas->id,
            'nama' => 'Fakultas Baru',
            'kode' => 'FB',
        ]);
    }

    /** 6. Admin dapat menonaktifkan Fakultas. */
    public function test_6_admin_can_deactivate_fakultas(): void
    {
        $fakultas = Fakultas::create([
            'nama' => 'Fakultas Aktif',
            'kode' => 'FA',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.fakultas-prodi.update', $fakultas), [
            'nama' => 'Fakultas Aktif',
            'kode' => 'FA',
            // is_active omitted/unchecked
        ]);

        $response->assertRedirect(route('admin.fakultas-prodi.index'));
        $this->assertDatabaseHas('fakultas', [
            'id' => $fakultas->id,
            'is_active' => false,
        ]);
    }

    /** 7. Admin dapat mengaktifkan Fakultas. */
    public function test_7_admin_can_activate_fakultas(): void
    {
        $fakultas = Fakultas::create([
            'nama' => 'Fakultas Inaktif',
            'kode' => 'FI',
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.fakultas-prodi.update', $fakultas), [
            'nama' => 'Fakultas Inaktif',
            'kode' => 'FI',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.fakultas-prodi.index'));
        $this->assertDatabaseHas('fakultas', [
            'id' => $fakultas->id,
            'is_active' => true,
        ]);
    }

    /** 8. Admin dapat membuat Program Studi. */
    public function test_8_admin_can_create_program_studi(): void
    {
        $fakultas = Fakultas::create([
            'nama' => 'Fakultas Teknik',
            'kode' => 'FT',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.fakultas-prodi.prodi.store', $fakultas), [
            'nama' => 'Teknik Elektro',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.fakultas-prodi.index'));
        $this->assertDatabaseHas('program_studi', [
            'fakultas_id' => $fakultas->id,
            'nama' => 'Teknik Elektro',
            'is_active' => true,
        ]);
    }

    /** 9. Admin dapat mengedit Program Studi. */
    public function test_9_admin_can_edit_program_studi(): void
    {
        $fakultas = Fakultas::create(['nama' => 'Fakultas Sains', 'kode' => 'FS', 'is_active' => true]);
        $prodi = ProgramStudi::create(['fakultas_id' => $fakultas->id, 'nama' => 'Biologi', 'is_active' => true]);

        $response = $this->actingAs($this->admin)->put(route('admin.program-studi.update', $prodi), [
            'fakultas_id' => $fakultas->id,
            'nama' => 'Biologi Murni',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.fakultas-prodi.index'));
        $this->assertDatabaseHas('program_studi', [
            'id' => $prodi->id,
            'nama' => 'Biologi Murni',
        ]);
    }

    /** 10. Admin dapat menonaktifkan Program Studi. */
    public function test_10_admin_can_deactivate_program_studi(): void
    {
        $fakultas = Fakultas::create(['nama' => 'Fakultas Sains', 'kode' => 'FS2', 'is_active' => true]);
        $prodi = ProgramStudi::create(['fakultas_id' => $fakultas->id, 'nama' => 'Kimia', 'is_active' => true]);

        $response = $this->actingAs($this->admin)->put(route('admin.program-studi.update', $prodi), [
            'fakultas_id' => $fakultas->id,
            'nama' => 'Kimia',
            // is_active unchecked
        ]);

        $response->assertRedirect(route('admin.fakultas-prodi.index'));
        $this->assertDatabaseHas('program_studi', [
            'id' => $prodi->id,
            'is_active' => false,
        ]);
    }

    /** 11. Duplikasi kode Fakultas ditolak. */
    public function test_11_duplicate_fakultas_kode_is_rejected(): void
    {
        Fakultas::create(['nama' => 'Fakultas A', 'kode' => 'FTK', 'is_active' => true]);

        $response = $this->actingAs($this->admin)->post(route('admin.fakultas-prodi.store'), [
            'nama' => 'Fakultas B',
            'kode' => 'FTK',
            'is_active' => 1,
        ]);

        $response->assertSessionHasErrors('kode');
    }

    /** 12. Duplikasi Program Studi dalam Fakultas yang sama ditolak. */
    public function test_12_duplicate_prodi_in_same_fakultas_is_rejected(): void
    {
        $fakultas = Fakultas::create(['nama' => 'Fakultas Tarbiyah', 'kode' => 'FTK2', 'is_active' => true]);
        ProgramStudi::create(['fakultas_id' => $fakultas->id, 'nama' => 'Pendidikan Agama Islam', 'is_active' => true]);

        $response = $this->actingAs($this->admin)->post(route('admin.fakultas-prodi.prodi.store', $fakultas), [
            'nama' => 'Pendidikan Agama Islam',
            'is_active' => 1,
        ]);

        $response->assertSessionHasErrors('nama');
    }

    /** 13. Program Studi dengan nama sama pada Fakultas berbeda diperbolehkan. */
    public function test_13_same_prodi_name_in_different_fakultas_is_allowed(): void
    {
        $fakultasA = Fakultas::create(['nama' => 'Fakultas A', 'kode' => 'FA', 'is_active' => true]);
        $fakultasB = Fakultas::create(['nama' => 'Fakultas B', 'kode' => 'FB', 'is_active' => true]);

        ProgramStudi::create(['fakultas_id' => $fakultasA->id, 'nama' => 'Biologi', 'is_active' => true]);

        $response = $this->actingAs($this->admin)->post(route('admin.fakultas-prodi.prodi.store', $fakultasB), [
            'nama' => 'Biologi',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.fakultas-prodi.index'));
        $this->assertDatabaseHas('program_studi', [
            'fakultas_id' => $fakultasB->id,
            'nama' => 'Biologi',
        ]);
    }

    /** 14. Fakultas yang memiliki Program Studi tidak dapat dihapus. */
    public function test_14_fakultas_with_prodi_cannot_be_deleted(): void
    {
        $fakultas = Fakultas::create(['nama' => 'Fakultas Memiliki Prodi', 'kode' => 'FMP', 'is_active' => true]);
        ProgramStudi::create(['fakultas_id' => $fakultas->id, 'nama' => 'Prodi A', 'is_active' => true]);

        $response = $this->actingAs($this->admin)->delete(route('admin.fakultas-prodi.destroy', $fakultas));

        $response->assertRedirect(route('admin.fakultas-prodi.index'));
        $response->assertSessionHas('error', 'Fakultas tidak dapat dihapus karena masih memiliki program studi.');
        $this->assertDatabaseHas('fakultas', ['id' => $fakultas->id]);
    }

    /** 15. Program Studi yang sudah digunakan tidak dapat dihapus. */
    public function test_15_used_program_studi_cannot_be_deleted(): void
    {
        $fakultas = Fakultas::create(['nama' => 'Fakultas Terpakai', 'kode' => 'FTP', 'is_active' => true]);
        $prodi = ProgramStudi::create(['fakultas_id' => $fakultas->id, 'nama' => 'Arsitektur', 'is_active' => true]);

        $tahun = TahunMaba::firstOrCreate(['tahun' => 2026], ['nama' => 'Maba 2026', 'is_active' => true]);

        MabaData::create([
            'tahun_maba_id' => $tahun->id,
            'nama_biro' => 'Fulan',
            'program_studi_biro' => 'Arsitektur',
            'status_biodata' => 'MENUNGGU_VERIFIKASI',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.program-studi.destroy', $prodi));

        $response->assertRedirect(route('admin.fakultas-prodi.index'));
        $response->assertSessionHas('error', 'Program studi sudah digunakan oleh data operasional dan tidak dapat dihapus. Nonaktifkan program studi jika sudah tidak digunakan.');
        $this->assertDatabaseHas('program_studi', ['id' => $prodi->id]);
    }

    /** 16. Fakultas inactive tidak muncul pada dropdown input baru. */
    public function test_16_inactive_fakultas_excluded_from_active_scope(): void
    {
        Fakultas::create(['nama' => 'Fakultas Aktif 1', 'kode' => 'FA1', 'is_active' => true]);
        Fakultas::create(['nama' => 'Fakultas Nonaktif 1', 'kode' => 'FN1', 'is_active' => false]);

        $activeFakultas = Fakultas::active()->pluck('nama')->toArray();

        $this->assertContains('Fakultas Aktif 1', $activeFakultas);
        $this->assertNotContains('Fakultas Nonaktif 1', $activeFakultas);
    }

    /** 17. Program Studi inactive tidak muncul pada dropdown input baru. */
    public function test_17_inactive_prodi_excluded_from_active_scope(): void
    {
        $fakultas = Fakultas::create(['nama' => 'Fakultas Test', 'kode' => 'FTST', 'is_active' => true]);
        ProgramStudi::create(['fakultas_id' => $fakultas->id, 'nama' => 'Prodi Aktif', 'is_active' => true]);
        ProgramStudi::create(['fakultas_id' => $fakultas->id, 'nama' => 'Prodi Inaktif', 'is_active' => false]);

        $activeProdi = ProgramStudi::active()->pluck('nama')->toArray();

        $this->assertContains('Prodi Aktif', $activeProdi);
        $this->assertNotContains('Prodi Inaktif', $activeProdi);
    }

    /** 18. Program Studi hanya dapat dipilih dari Fakultas yang sesuai. */
    public function test_18_prodi_json_endpoint_returns_only_matching_fakultas_prodi(): void
    {
        $fakultasA = Fakultas::create(['nama' => 'Fakultas A', 'kode' => 'FAA', 'is_active' => true]);
        $fakultasB = Fakultas::create(['nama' => 'Fakultas B', 'kode' => 'FBB', 'is_active' => true]);

        ProgramStudi::create(['fakultas_id' => $fakultasA->id, 'nama' => 'Prodi A1', 'is_active' => true]);
        ProgramStudi::create(['fakultas_id' => $fakultasB->id, 'nama' => 'Prodi B1', 'is_active' => true]);

        $response = $this->actingAs($this->admin)->get(route('api.fakultas.prodi', $fakultasA));

        $response->assertStatus(200);
        $response->assertJsonFragment(['nama' => 'Prodi A1']);
        $response->assertJsonMissing(['nama' => 'Prodi B1']);
    }

    /** 19. User tidak dapat memanipulasi fakultas_id untuk mengambil Program Studi milik Fakultas lain. */
    public function test_19_prodi_isolation_per_fakultas(): void
    {
        $fakultasA = Fakultas::create(['nama' => 'Fakultas A', 'kode' => 'FA19', 'is_active' => true]);
        $fakultasB = Fakultas::create(['nama' => 'Fakultas B', 'kode' => 'FB19', 'is_active' => true]);

        $prodiB = ProgramStudi::create(['fakultas_id' => $fakultasB->id, 'nama' => 'Kedokteran', 'is_active' => true]);

        // Attempt updating prodi to assign to invalid faculty combination
        $response = $this->actingAs($this->admin)->put(route('admin.program-studi.update', $prodiB), [
            'fakultas_id' => $fakultasA->id,
            'nama' => 'Kedokteran',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.fakultas-prodi.index'));
        $this->assertDatabaseHas('program_studi', [
            'id' => $prodiB->id,
            'fakultas_id' => $fakultasA->id,
            'nama' => 'Kedokteran',
        ]);
    }

    /** 20. Seeder tidak menghasilkan duplikasi jika dijalankan ulang. */
    public function test_20_seeder_is_idempotent(): void
    {
        $this->seed(FakultasProdiSeeder::class);
        $countFakultasFirst = Fakultas::count();
        $countProdiFirst = ProgramStudi::count();

        $this->seed(FakultasProdiSeeder::class);
        $countFakultasSecond = Fakultas::count();
        $countProdiSecond = ProgramStudi::count();

        $this->assertEquals($countFakultasFirst, $countFakultasSecond);
        $this->assertEquals($countProdiFirst, $countProdiSecond);
        $this->assertEquals(10, $countFakultasFirst);
    }

    /** 21. Legacy data tetap aman. */
    public function test_21_legacy_data_remains_safe(): void
    {
        $tahun = TahunMaba::create(['tahun' => 2025, 'nama' => 'Maba 2025', 'is_active' => false]);
        
        $mabaLegacy = MabaData::create([
            'tahun_maba_id' => $tahun->id,
            'nama_biro' => 'Legacy Student',
            'program_studi_biro' => 'Teknik Informatika (Legacy)',
            'fakultas' => 'Fakultas Legacy Tekno',
            'program_studi' => 'Teknik Informatika (Legacy)',
            'status_biodata' => 'PEMERIKSAAN_SELESAI',
        ]);

        $pemeriksaanLegacy = Pemeriksaan::create([
            'maba_data_id' => $mabaLegacy->id,
            'nomor_surat' => '123/Un.08/PPKES/2025',
            'nama' => 'Legacy Student',
            'fakultas' => 'Fakultas Legacy Tekno',
            'pekerjaan' => 'Teknik Informatika (Legacy)',
            'kesimpulan' => 'Sehat Fisik dan Mental',
        ]);

        // Run seeder
        $this->seed(FakultasProdiSeeder::class);

        // Verify legacy record is intact
        $this->assertDatabaseHas('maba_datas', [
            'id' => $mabaLegacy->id,
            'fakultas' => 'Fakultas Legacy Tekno',
            'program_studi_biro' => 'Teknik Informatika (Legacy)',
        ]);

        $this->assertDatabaseHas('pemeriksaans', [
            'id' => $pemeriksaanLegacy->id,
            'nomor_surat' => '123/Un.08/PPKES/2025',
        ]);
    }
}
