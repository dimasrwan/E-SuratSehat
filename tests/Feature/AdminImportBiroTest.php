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

    public function test_file_tidak_didukung_seperti_docx_ditolak_validasi()
    {
        $file = UploadedFile::fake()->create('document.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

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

    public function test_pdf_schedule_parser_service_membaca_format_jadwal_2026_dengan_benar()
    {
        $parser = new \App\Services\PdfScheduleParserService();
        $sampleText = "
DAFTAR JADWAL PEMERIKSAAN KESEHATAN
MAHASISWA BARU TAHUN AJARAN 2026/2027

FAKULTAS TARBIYAH DAN KEGURUAN
Prodi. Bimbingan Konseling

1 | S1 Bimbingan Konseling | AFIFA JAHRA | Sabtu, 15 Agustus 2026 | Sesi 1 | 08.00 s/d 12.30
2 | S1 Bimbingan Konseling | IMELDA | Sabtu, 15 Agustus 2026 | Sesi 2 | 13.00 s/d 17.30
3 | S1 Bimbingan Konseling | MUHAMMAD RAIHAN ALBAR | Jumat, 21 Agustus 2026 | Sesi 1 | 08.00 s/d 12.00
Halaman 1 dari 100
";

        $results = $parser->parseTextContent($sampleText);

        $this->assertCount(3, $results);

        // Row 1
        $this->assertEquals('AFIFA JAHRA', $results[0]['nama_biro']);
        $this->assertEquals('Bimbingan Konseling', $results[0]['program_studi_biro']);
        $this->assertEquals('FAKULTAS TARBIYAH DAN KEGURUAN', $results[0]['fakultas_biro']);
        $this->assertEquals('2026-08-15', $results[0]['tanggal_jadwal']);
        $this->assertEquals('Sesi 1', $results[0]['sesi_jadwal']);
        $this->assertEquals('08.00 s/d 12.30', $results[0]['waktu_jadwal']);

        // Row 2
        $this->assertEquals('IMELDA', $results[1]['nama_biro']);
        $this->assertEquals('Sesi 2', $results[1]['sesi_jadwal']);
        $this->assertEquals('13.00 s/d 17.30', $results[1]['waktu_jadwal']);

        // Row 3 (Custom time on Friday: 08.00 s/d 12.00)
        $this->assertEquals('MUHAMMAD RAIHAN ALBAR', $results[2]['nama_biro']);
        $this->assertEquals('2026-08-21', $results[2]['tanggal_jadwal']);
        $this->assertEquals('08.00 s/d 12.00', $results[2]['waktu_jadwal']);
    }

    public function test_download_template_excel_biro_berhasil()
    {
        $this->actingAs($this->admin)
            ->get(route('admin.import.template'))
            ->assertStatus(200)
            ->assertHeader('content-disposition');
    }

    public function test_pdf_schedule_parser_memproses_format_space_separated_dan_transisi_fakultas_prodi()
    {
        $parser = new \App\Services\PdfScheduleParserService();
        $sampleText = "
DAFTAR JADWAL PEMERIKSAAN KESEHATAN
MAHASISWA BARU TAHUN AJARAN 2026/2027
Nama Peserta Hari / Tanggal Sesi Waktu Pemeriksaan

FAKULTAS TARBIYAH DAN KEGURUAN
Prodi. Bimbingan Konseling

1 S1 Bimbingan Konseling AFIFA JAHRA Sabtu, 15 Agustus 2026 Sesi 1 08.00 s/d 12.30
2 S1 Bimbingan Konseling MUHAMMAD RAIHAN ALBAR Sabtu, 15 Agustus 2026 Sesi 2 13.00 s/d 17.30

FAKULTAS SAINS DAN TEKNOLOGI
Prodi. Teknologi Informasi

3 S1 Teknologi Informasi CUT RAHMAWATI Jumat, 21 Agustus 2026 Sesi 1 08.00 s/d 12.00
4 S1 Teknologi Informasi DEDI KURNIAWAN Jumat, 21 Agustus 2026 Sesi 2 13.30 s/d 17.30

Halaman 2 dari 50
";

        $results = $parser->parseTextContent($sampleText);

        $this->assertCount(4, $results);

        // Row 1: FTK
        $this->assertEquals('AFIFA JAHRA', $results[0]['nama_biro']);
        $this->assertEquals('Bimbingan Konseling', $results[0]['program_studi_biro']);
        $this->assertEquals('FAKULTAS TARBIYAH DAN KEGURUAN', $results[0]['fakultas_biro']);
        $this->assertEquals('2026-08-15', $results[0]['tanggal_jadwal']);
        $this->assertEquals('Sesi 1', $results[0]['sesi_jadwal']);
        $this->assertEquals('08.00 s/d 12.30', $results[0]['waktu_jadwal']);

        // Row 3: Transisi ke SAINTEK & Teknologi Informasi
        $this->assertEquals('CUT RAHMAWATI', $results[2]['nama_biro']);
        $this->assertEquals('Teknologi Informasi', $results[2]['program_studi_biro']);
        $this->assertEquals('FAKULTAS SAINS DAN TEKNOLOGI', $results[2]['fakultas_biro']);
        $this->assertEquals('2026-08-21', $results[2]['tanggal_jadwal']);
        $this->assertEquals('Sesi 1', $results[2]['sesi_jadwal']);
        $this->assertEquals('08.00 s/d 12.00', $results[2]['waktu_jadwal']);
    }

    public function test_prodi_tidak_ada_di_master_diklasifikasikan_sebagai_error()
    {
        $this->seed(\Database\Seeders\FakultasProdiSeeder::class);

        $file = $this->createExcelFile([
            ['Nama', 'Program Studi', 'Tanggal', 'Sesi', 'Waktu'],
            ['Mahasiswa Test', 'Prodi Tidak Terdaftar Master', '2027-09-15', 'Sesi 1', '08:00 WIB'],
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.import.preview.store'), [
                'tahun_maba_id' => $this->tahun2027->id,
                'file' => $file,
            ]);

        $batch = ImportBatch::latest('id')->first();
        $this->assertEquals(1, $batch->error_rows);

        $row = ImportBatchRow::where('import_batch_id', $batch->id)->first();
        $this->assertEquals('ERROR', $row->classification);
        $this->assertStringContainsString('tidak ditemukan di Master Program Studi', $row->error_messages);
    }

    public function test_legacy_4635_pemeriksaan_tetap_utuh_dan_maba_data_id_null_tetap_valid()
    {
        // Seed 5 legacy pemeriksaans with null maba_data_id
        for ($i = 1; $i <= 5; $i++) {
            Pemeriksaan::create([
                'maba_data_id' => null,
                'nomor_surat' => "00{$i}/Un.08/PPKES/09/2026",
                'nama' => "Legacy Patient {$i}",
                'pekerjaan' => 'Mahasiswa / Teknologi Informasi',
                'status_pemeriksaan' => 'SELESAI',
            ]);
        }

        $legacyCount = Pemeriksaan::whereNull('maba_data_id')->count();
        $this->assertEquals(5, $legacyCount);

        // Run import
        $file = $this->createExcelFile([
            ['Nama', 'Program Studi', 'Tanggal'],
            ['Peserta Import Baru', 'Teknologi Informasi', '2027-09-15'],
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.import.preview.store'), ['tahun_maba_id' => $this->tahun2027->id, 'file' => $file]);

        $batch = ImportBatch::latest('id')->first();
        $this->actingAs($this->admin)->post(route('admin.import.confirm', $batch->id));

        // Legacy count & null maba_data_id MUST NOT change
        $this->assertEquals(5, Pemeriksaan::whereNull('maba_data_id')->count());
    }
}
