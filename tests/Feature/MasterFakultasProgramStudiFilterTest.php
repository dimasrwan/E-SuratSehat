<?php

namespace Tests\Feature;

use App\Models\Fakultas;
use App\Models\MabaData;
use App\Models\ProgramStudi;
use App\Models\TahunMaba;
use App\Models\User;
use Database\Seeders\FakultasProdiSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterFakultasProgramStudiFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;

    public function setUp(): void
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

        $this->seed(FakultasProdiSeeder::class);
    }

    /** 1. Admin dapat melihat halaman master. */
    public function test_admin_dapat_melihat_halaman_master(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.fakultas-prodi.index'));
        $response->assertStatus(200);
        $response->assertSee('Master Fakultas & Program Studi', false);
    }

    /** 2. Operator tidak dapat melihat. */
    public function test_operator_tidak_dapat_melihat_halaman_master(): void
    {
        $response = $this->actingAs($this->operator)->get(route('admin.fakultas-prodi.index'));
        $response->assertStatus(403);
    }

    /** 3. Public tidak dapat melihat. */
    public function test_public_tidak_dapat_melihat_halaman_master(): void
    {
        $response = $this->get(route('admin.fakultas-prodi.index'));
        $response->assertRedirect(route('login'));
    }

    /** 4. Search berdasarkan nama fakultas. */
    public function test_search_berdasarkan_nama_fakultas(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.fakultas-prodi.index', ['search' => 'Ekonomi dan Bisnis']));
        $response->assertStatus(200);
        $response->assertSee('FEBI');
        $response->assertSee('Ekonomi Syariah');
        $response->assertDontSee('Pendidikan Profesi Dokter');
    }

    /** 5. Search berdasarkan kode fakultas. */
    public function test_search_berdasarkan_kode_fakultas(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.fakultas-prodi.index', ['search' => 'SAINTEK']));
        $response->assertStatus(200);
        $response->assertSee('SAINTEK');
        $response->assertSee('Arsitektur');
        $response->assertDontSee('Pendidikan Profesi Dokter');
    }

    /** 6. Search berdasarkan nama prodi. */
    public function test_search_berdasarkan_nama_prodi(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.fakultas-prodi.index', ['search' => 'Arsitektur']));
        $response->assertStatus(200);
        $response->assertSee('SAINTEK');
        $response->assertSee('Arsitektur');
        $response->assertDontSee('Pendidikan Profesi Dokter');
    }

    /** 7. Filter semua fakultas. */
    public function test_filter_semua_fakultas(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.fakultas-prodi.index', ['fakultas_id' => 'all']));
        $response->assertStatus(200);
        $response->assertSee('FTK');
        $response->assertSee('FK');
    }

    /** 8. Filter fakultas tertentu. */
    public function test_filter_fakultas_tertentu(): void
    {
        $ftk = Fakultas::where('kode', 'FTK')->first();

        $response = $this->actingAs($this->admin)->get(route('admin.fakultas-prodi.index', ['fakultas_id' => $ftk->id]));
        $response->assertStatus(200);
        $response->assertSee('FTK');
        $response->assertSee('Pendidikan Agama Islam');
        $response->assertDontSee('Pendidikan Profesi Dokter');
    }

    /** 9. Filter status Aktif. */
    public function test_filter_status_aktif(): void
    {
        $f = Fakultas::where('kode', 'FTK')->first();
        $inactiveProdi = ProgramStudi::create(['fakultas_id' => $f->id, 'nama' => 'Prodi Inaktif Custom Filter', 'is_active' => false, 'sort_order' => 99]);

        $response = $this->actingAs($this->admin)->get(route('admin.fakultas-prodi.index', ['status' => 'active']));
        $response->assertStatus(200);
        $response->assertSee('Manajemen Pendidikan Islam');
        $response->assertDontSee('Prodi Inaktif Custom Filter');

        $inactiveProdi->delete();
    }

    /** 10. Filter status Nonaktif. */
    public function test_filter_status_nonaktif(): void
    {
        $f = Fakultas::where('kode', 'FTK')->first();
        $inactiveProdi = ProgramStudi::create(['fakultas_id' => $f->id, 'nama' => 'Prodi Inaktif Custom Filter', 'is_active' => false, 'sort_order' => 99]);

        $response = $this->actingAs($this->admin)->get(route('admin.fakultas-prodi.index', ['status' => 'inactive']));
        $response->assertStatus(200);
        $response->assertSee('Prodi Inaktif Custom Filter');
        $response->assertDontSee('Manajemen Pendidikan Islam');

        $inactiveProdi->delete();
    }

    /** 11. Filter Fakultas + Status. */
    public function test_filter_fakultas_dan_status(): void
    {
        $saintek = Fakultas::where('kode', 'SAINTEK')->first();
        $ti = ProgramStudi::where('fakultas_id', $saintek->id)->where('nama', 'Teknologi Informasi')->first();
        $ti->update(['is_active' => false]);

        $response = $this->actingAs($this->admin)->get(route('admin.fakultas-prodi.index', [
            'fakultas_id' => $saintek->id,
            'status' => 'inactive',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Teknologi Informasi');
        $response->assertDontSee('Biologi');

        $ti->update(['is_active' => true]);
    }

    /** 12. Search + Fakultas. */
    public function test_search_dan_fakultas(): void
    {
        $ftk = Fakultas::where('kode', 'FTK')->first();

        $response = $this->actingAs($this->admin)->get(route('admin.fakultas-prodi.index', [
            'fakultas_id' => $ftk->id,
            'search' => 'Islam',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Pendidikan Agama Islam');
        $response->assertDontSee('Hukum Keluarga');
    }

    /** 13. Search + Status. */
    public function test_search_dan_status(): void
    {
        $ti = ProgramStudi::where('nama', 'Teknologi Informasi')->first();
        $ti->update(['is_active' => false]);

        $response = $this->actingAs($this->admin)->get(route('admin.fakultas-prodi.index', [
            'search' => 'Teknologi',
            'status' => 'inactive',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Teknologi Informasi');
        $response->assertDontSee('Pendidikan Teknologi Informasi');

        $ti->update(['is_active' => true]);
    }

    /** 14. Search + Fakultas + Status. */
    public function test_search_fakultas_dan_status(): void
    {
        $saintek = Fakultas::where('kode', 'SAINTEK')->first();
        $ti = ProgramStudi::where('fakultas_id', $saintek->id)->where('nama', 'Teknologi Informasi')->first();
        $ti->update(['is_active' => false]);

        $response = $this->actingAs($this->admin)->get(route('admin.fakultas-prodi.index', [
            'search' => 'Informasi',
            'fakultas_id' => $saintek->id,
            'status' => 'inactive',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Teknologi Informasi');
        $response->assertDontSee('Biologi');

        $ti->update(['is_active' => true]);
    }

    /** 15. Reset menghapus seluruh filter. */
    public function test_reset_menghapus_seluruh_filter(): void
    {
        $ftk = Fakultas::where('kode', 'FTK')->first();

        // Applied filter
        $responseFiltered = $this->actingAs($this->admin)->get(route('admin.fakultas-prodi.index', [
            'search' => 'Tarbiyah',
            'fakultas_id' => $ftk->id,
            'status' => 'active',
        ]));
        $responseFiltered->assertStatus(200);
        $responseFiltered->assertSee('Reset');

        // Reset
        $responseReset = $this->actingAs($this->admin)->get(route('admin.fakultas-prodi.index'));
        $responseReset->assertStatus(200);
        $responseReset->assertSee('Fakultas Kedokteran');
        $responseReset->assertSee('Fakultas Tarbiyah dan Keguruan');
    }

    /** 16. Urutan fakultas tetap resmi. */
    public function test_urutan_fakultas_tetap_resmi(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.fakultas-prodi.index'));
        $response->assertStatus(200);

        $expectedCodes = ['FTK', 'FSH', 'FUF', 'FDK', 'FAH', 'FEBI', 'SAINTEK', 'FISIP', 'FPSI', 'FK'];
        $actualCodes = Fakultas::orderBy('sort_order', 'asc')->pluck('kode')->toArray();

        $this->assertEquals($expectedCodes, $actualCodes);
    }

    /** 17. Jumlah prodi mengikuti filter status. */
    public function test_jumlah_prodi_mengikuti_filter_status(): void
    {
        $saintek = Fakultas::where('kode', 'SAINTEK')->first();
        $ti = ProgramStudi::where('fakultas_id', $saintek->id)->where('nama', 'Teknologi Informasi')->first();
        $ti->update(['is_active' => false]);

        $responseActive = $this->actingAs($this->admin)->get(route('admin.fakultas-prodi.index', [
            'fakultas_id' => $saintek->id,
            'status' => 'active',
        ]));
        $responseActive->assertStatus(200);
        $responseActive->assertSee('5 Prodi');

        $responseInactive = $this->actingAs($this->admin)->get(route('admin.fakultas-prodi.index', [
            'fakultas_id' => $saintek->id,
            'status' => 'inactive',
        ]));
        $responseInactive->assertStatus(200);
        $responseInactive->assertSee('1 Prodi');
    }

    /** 18. Seeder tetap idempotent. */
    public function test_seeder_tetap_idempotent(): void
    {
        $fCount = Fakultas::count();
        $pCount = ProgramStudi::count();

        $this->seed(FakultasProdiSeeder::class);
        $this->seed(FakultasProdiSeeder::class);

        $this->assertEquals($fCount, Fakultas::count());
        $this->assertEquals($pCount, ProgramStudi::count());
    }

    /** 19. Existing data tetap aman. */
    public function test_existing_data_tetap_aman(): void
    {
        $tahun = TahunMaba::firstOrCreate(['tahun' => 2026], ['nama' => 'Maba 2026', 'is_active' => true]);
        $ftk = Fakultas::where('kode', 'FTK')->first();
        $pai = ProgramStudi::where('fakultas_id', $ftk->id)->where('nama', 'Pendidikan Agama Islam')->first();

        $maba = MabaData::create([
            'tahun_maba_id' => $tahun->id,
            'nama_biro' => 'Santri PAI',
            'program_studi' => $pai->nama,
            'program_studi_biro' => $pai->nama,
            'status_biodata' => 'PEMERIKSAAN_SELESAI',
        ]);

        $this->assertEquals('Santri PAI', $maba->nama_biro);
        $this->assertEquals('Pendidikan Agama Islam', $maba->program_studi);
    }

    /** 20. Authorization tetap aman. */
    public function test_authorization_tetap_aman(): void
    {
        $responseOp = $this->actingAs($this->operator)->get(route('admin.fakultas-prodi.index'));
        $responseOp->assertStatus(403);
    }
}
