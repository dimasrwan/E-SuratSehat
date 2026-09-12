<?php

namespace Tests\Feature;

use App\Models\ImportBatch;
use App\Models\ImportBatchRow;
use App\Models\MabaData;
use App\Models\Pemeriksaan;
use App\Models\TahunMaba;
use App\Models\User;
use App\Services\BiroImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class AdminImportBiroTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected TahunMaba $tahun2026;
    protected TahunMaba $tahun2027;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->operator = User::factory()->create(['role' => 'operator', 'is_active' => true]);

        $this->tahun2026 = TahunMaba::firstOrCreate(
            ['tahun' => 2026],
            ['nama' => 'Maba 2026', 'nomor_surat_mulai' => 172, 'kode_unit' => 'Un.08', 'kode_bagian' => 'PPKES', 'tahun_surat' => 2026, 'is_active' => true]
        );

        $this->tahun2027 = TahunMaba::firstOrCreate(
            ['tahun' => 2027],
            ['nama' => 'Maba 2027', 'nomor_surat_mulai' => 1, 'kode_unit' => 'Un.08', 'kode_bagian' => 'PPKES', 'tahun_surat' => 2027, 'is_active' => false]
        );
    }

    /**
     * Helper to create a temporary XLSX file for testing.
     */
    protected function createExcelFile(array $rows, string $filename = 'test_maba.xlsx'): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($rows);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_excel_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return new UploadedFile(
            $tempPath,
            $filename,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    public function test_guest_tidak_dapat_mengakses_route_import()
    {
        $this->get(route('admin.import.index'))->assertRedirect(route('login'));
        $this->get(route('admin.import.history'))->assertRedirect(route('login'));
    }

    public function test_operator_mendapat_403_forbidden_saat_akses_import()
    {
        $this->actingAs($this->operator)
            ->get(route('admin.import.index'))
            ->assertStatus(403);
    }

    public function test_admin_dapat_membuka_halaman_import()
    {
        $this->actingAs($this->admin)
            ->get(route('admin.import.index'))
            ->assertStatus(200)
            ->assertSee('Import Data Biro Maba');
    }

    public function test_file_non_excel_seperti_pdf_ditolak_validasi()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $this->actingAs($this->admin)
            ->post(route('admin.import.preview.store'), [
                'tahun_maba_id' => $this->tahun2027->id,
                'file' => $file,
            ])
            ->assertSessionHasErrors(['file']);
    }

    public function test_file_header_invalid_menampilkan_pesan_error_jelas()
    {
        $file = $this->createExcelFile([
            ['HeaderSalah1', 'HeaderSalah2'],
            ['Val1', 'Val2'],
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.import.preview.store'), [
                'tahun_maba_id' => $this->tahun2027->id,
                'file' => $file,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_file_xlsx_valid_dapat_diupload_dan_menghasilkan_preview()
    {
        $file = $this->createExcelFile([
            ['Nama', 'Program Studi', 'Tanggal', 'Sesi', 'Waktu'],
            ['Ahmad Fauzan', 'Teknik Informatika', '2027-09-15', 'Sesi 1', '08:00 WIB'],
            ['Budi Santoso', 'Sistem Informasi', '2027-09-15', 'Sesi 2', '10:00 WIB'],
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.import.preview.store'), [
                'tahun_maba_id' => $this->tahun2027->id,
                'file' => $file,
            ]);

        $batch = ImportBatch::latest('id')->first();
        $this->assertNotNull($batch);
        $this->assertEquals('PREVIEW', $batch->status);
        $this->assertEquals(2, $batch->total_rows);
        $this->assertEquals(2, $batch->new_rows);

        $response->assertRedirect(route('admin.import.preview', $batch->id));
    }

    public function test_baris_dengan_nama_kosong_diklasifikasikan_sebagai_error()
    {
        $file = $this->createExcelFile([
            ['Nama', 'Program Studi', 'Tanggal'],
            ['', 'Teknik Informatika', '2027-09-15'],
            ['Siti Rahma', 'Kedokteran', '2027-09-15'],
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.import.preview.store'), [
                'tahun_maba_id' => $this->tahun2027->id,
                'file' => $file,
            ]);

        $batch = ImportBatch::latest('id')->first();
        $this->assertEquals(2, $batch->total_rows);
        $this->assertEquals(1, $batch->new_rows);
        $this->assertEquals(1, $batch->error_rows);
    }

    public function test_konfirmasi_import_berhasil_membuat_record_maba_datas()
    {
        $file = $this->createExcelFile([
            ['Nama Maba', 'Prodi', 'Tanggal Jadwal', 'Sesi', 'Waktu'],
            ['Ahmad Fauzan', 'Teknik Informatika', '2027-09-15', 'Sesi 1', '08:00 WIB'],
            ['Budi Santoso', 'Sistem Informasi', '2027-09-16', 'Sesi 2', '10:00 WIB'],
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.import.preview.store'), [
                'tahun_maba_id' => $this->tahun2027->id,
                'file' => $file,
            ]);

        $batch = ImportBatch::latest('id')->first();

        $this->actingAs($this->admin)
            ->post(route('admin.import.confirm', $batch->id))
            ->assertRedirect(route('admin.import.history'))
            ->assertSessionHas('success');

        $batch->refresh();
        $this->assertEquals('COMPLETED', $batch->status);
        $this->assertEquals(2, $batch->inserted_rows);

        $this->assertDatabaseHas('maba_datas', [
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Ahmad Fauzan',
            'program_studi_biro' => 'Teknik Informatika',
            'status_biodata' => 'BELUM_MENGISI',
        ]);
    }

    public function test_exact_duplicate_file_di_skip_saat_reimport()
    {
        // First Import
        $file1 = $this->createExcelFile([
            ['Nama', 'Program Studi', 'Tanggal', 'Sesi', 'Waktu'],
            ['Ahmad Fauzan', 'Teknik Informatika', '2027-09-15', 'Sesi 1', '08:00 WIB'],
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.import.preview.store'), ['tahun_maba_id' => $this->tahun2027->id, 'file' => $file1]);
        $batch1 = ImportBatch::latest('id')->first();
        $this->actingAs($this->admin)->post(route('admin.import.confirm', $batch1->id));

        $this->assertEquals(1, MabaData::where('tahun_maba_id', $this->tahun2027->id)->count());

        // Re-import exact same file
        $file2 = $this->createExcelFile([
            ['Nama', 'Program Studi', 'Tanggal', 'Sesi', 'Waktu'],
            ['Ahmad Fauzan', 'Teknik Informatika', '2027-09-15', 'Sesi 1', '08:00 WIB'],
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.import.preview.store'), ['tahun_maba_id' => $this->tahun2027->id, 'file' => $file2]);
        $batch2 = ImportBatch::latest('id')->first();

        $this->assertEquals(1, $batch2->exact_duplicate_rows);

        $this->actingAs($this->admin)->post(route('admin.import.confirm', $batch2->id));
        $batch2->refresh();

        $this->assertEquals(1, $batch2->skipped_rows);
        $this->assertEquals(0, $batch2->inserted_rows);
        // Total DB records remains 1
        $this->assertEquals(1, MabaData::where('tahun_maba_id', $this->tahun2027->id)->count());
    }

    public function test_possible_duplicate_dapat_memilih_update_jadwal_tanpa_merusak_biodata_maba()
    {
        // Existing Maba with filled personal biodata & verification status
        $maba = MabaData::create([
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Ahmad Fauzan',
            'program_studi_biro' => 'Teknik Informatika',
            'tanggal_jadwal' => '2027-09-15',
            'sesi_jadwal' => 'Sesi 1',
            'nama_lengkap' => 'Ahmad Fauzan M.Kom',
            'nik' => '3201123456789012',
            'email' => 'ahmad@example.com',
            'status_biodata' => 'TERVERIFIKASI',
            'verified_at' => now(),
            'verified_by' => $this->operator->id,
        ]);

        // Biro sends revised schedule file (same name & prodi, different date/session)
        $file = $this->createExcelFile([
            ['Nama', 'Program Studi', 'Tanggal', 'Sesi', 'Waktu'],
            ['Ahmad Fauzan', 'Teknik Informatika', '2027-09-20', 'Sesi 3', '13:00 WIB'],
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.import.preview.store'), ['tahun_maba_id' => $this->tahun2027->id, 'file' => $file]);

        $batch = ImportBatch::latest('id')->first();
        $row = ImportBatchRow::where('import_batch_id', $batch->id)->first();

        $this->assertEquals('POSSIBLE_DUPLICATE', $row->classification);

        // Confirm with action UPDATE_JADWAL
        $this->actingAs($this->admin)->post(route('admin.import.confirm', $batch->id), [
            'actions' => [
                $row->id => 'UPDATE_JADWAL',
            ],
        ]);

        $maba->refresh();

        // Schedule MUST be updated
        $this->assertEquals('2027-09-20', $maba->tanggal_jadwal->format('Y-m-d'));
        $this->assertEquals('Sesi 3', $maba->sesi_jadwal);

        // BIODATA PROTECTION RULE CHECK: Personal identity fields & verification status MUST REMAIN INTACT!
        $this->assertEquals('Ahmad Fauzan M.Kom', $maba->nama_lengkap);
        $this->assertEquals('3201123456789012', $maba->nik);
        $this->assertEquals('ahmad@example.com', $maba->email);
        $this->assertEquals('TERVERIFIKASI', $maba->status_biodata);
        $this->assertNotNull($maba->verified_at);
        $this->assertEquals($this->operator->id, $maba->verified_by);
    }

    public function test_import_maba_2027_terisolasi_dan_tidak_menyentuh_maba_2026_atau_pemeriksaans()
    {
        // Existing legacy pemeriksaan count
        $initialPemeriksaanCount = Pemeriksaan::count();

        $file = $this->createExcelFile([
            ['Nama', 'Program Studi', 'Tanggal'],
            ['Maba Baru 2027', 'Teknik Mesin', '2027-09-15'],
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.import.preview.store'), ['tahun_maba_id' => $this->tahun2027->id, 'file' => $file]);

        $batch = ImportBatch::latest('id')->first();
        $this->actingAs($this->admin)->post(route('admin.import.confirm', $batch->id));

        // New record exists in MabaData for 2027
        $this->assertDatabaseHas('maba_datas', [
            'tahun_maba_id' => $this->tahun2027->id,
            'nama_biro' => 'Maba Baru 2027',
        ]);

        // Pemeriksaans count MUST remain unchanged
        $this->assertEquals($initialPemeriksaanCount, Pemeriksaan::count());
    }

    public function test_pembatalan_import_mengubah_status_menjadi_cancelled()
    {
        $file = $this->createExcelFile([
            ['Nama', 'Program Studi'],
            ['Cancel Test', 'Teknik Elektro'],
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.import.preview.store'), ['tahun_maba_id' => $this->tahun2027->id, 'file' => $file]);

        $batch = ImportBatch::latest('id')->first();

        $this->actingAs($this->admin)
            ->post(route('admin.import.cancel', $batch->id))
            ->assertRedirect(route('admin.import.index'));

        $batch->refresh();
        $this->assertEquals('CANCELLED', $batch->status);
        $this->assertEquals(0, MabaData::where('nama_biro', 'Cancel Test')->count());
    }
}
