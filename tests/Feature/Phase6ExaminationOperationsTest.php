<?php

namespace Tests\Feature;

use App\Models\MabaData;
use App\Models\Pemeriksaan;
use App\Models\TahunMaba;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class Phase6ExaminationOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected TahunMaba $activeTahun;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activeTahun = TahunMaba::firstOrCreate(
            ['tahun' => 2026],
            [
                'nama' => 'Maba 2026',
                'is_active' => true,
                'nomor_surat_mulai' => 100,
                'kode_unit' => 'Un.08',
                'kode_bagian' => 'PPKES',
                'tahun_surat' => 2026,
            ]
        );

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@uin.ac.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->operator = User::create([
            'name' => 'Operator User',
            'email' => 'operator@uin.ac.id',
            'password' => bcrypt('password'),
            'role' => 'operator',
            'is_active' => true,
        ]);
    }

    public function test_maba_terverifikasi_enters_unexamined_queue()
    {
        $mabaVerified = MabaData::create([
            'tahun_maba_id' => $this->activeTahun->id,
            'nama_biro' => 'Maba Terverifikasi',
            'nama_lengkap' => 'Maba Terverifikasi',
            'email' => 'verified@example.com',
            'nik' => '1111222233334444',
            'program_studi_biro' => 'Teknik Informatika',
            'status_biodata' => 'TERVERIFIKASI',
        ]);

        $mabaBelumMengisi = MabaData::create([
            'tahun_maba_id' => $this->activeTahun->id,
            'nama_biro' => 'Maba Belum Mengisi',
            'email' => 'belum@example.com',
            'program_studi_biro' => 'Teknik Informatika',
            'status_biodata' => 'BELUM_MENGISI',
        ]);

        $mabaMenunggu = MabaData::create([
            'tahun_maba_id' => $this->activeTahun->id,
            'nama_biro' => 'Maba Menunggu',
            'email' => 'menunggu@example.com',
            'program_studi_biro' => 'Teknik Informatika',
            'status_biodata' => 'MENUNGGU_VERIFIKASI',
        ]);

        $mabaPerluPerbaikan = MabaData::create([
            'tahun_maba_id' => $this->activeTahun->id,
            'nama_biro' => 'Maba Perbaikan',
            'email' => 'perbaikan@example.com',
            'program_studi_biro' => 'Teknik Informatika',
            'status_biodata' => 'PERLU_PERBAIKAN',
        ]);

        $response = $this->actingAs($this->operator)
            ->get(route('pemeriksaan.index', ['status_antrean' => 'belum_diperiksa']));

        $response->assertStatus(200);
        $response->assertSee('Maba Terverifikasi');
        $response->assertDontSee('Maba Belum Mengisi');
        $response->assertDontSee('Maba Menunggu');
        $response->assertDontSee('Maba Perbaikan');
    }

    public function test_maba_pemeriksaan_selesai_does_not_appear_in_unexamined_queue()
    {
        $mabaCompleted = MabaData::create([
            'tahun_maba_id' => $this->activeTahun->id,
            'nama_biro' => 'Maba Selesai Exam',
            'nama_lengkap' => 'Maba Selesai Exam',
            'email' => 'completed@example.com',
            'nik' => '9999888877776666',
            'program_studi_biro' => 'Teknik Informatika',
            'status_biodata' => 'PEMERIKSAAN_SELESAI',
        ]);

        Pemeriksaan::create([
            'maba_data_id' => $mabaCompleted->id,
            'nomor_surat' => '0100/Un.08/PPKES/09/2026',
            'tahun_masuk' => 2026,
            'nama' => 'Maba Selesai Exam',
            'email' => 'completed@example.com',
            'kesimpulan' => 'SEHAT DAN TIDAK BUTA WARNA',
        ]);

        $response = $this->actingAs($this->operator)
            ->get(route('pemeriksaan.index', ['status_antrean' => 'belum_diperiksa']));

        $response->assertStatus(200);
        $response->assertDontSee('Maba Selesai Exam');
    }

    public function test_mulai_pemeriksaan_prefills_biodata_and_is_readonly()
    {
        $maba = MabaData::create([
            'tahun_maba_id' => $this->activeTahun->id,
            'nama_biro' => 'Biro Name',
            'nama_lengkap' => 'Ahmad Maba',
            'email' => 'ahmad@example.com',
            'nik' => '1234567890123456',
            'tempat_lahir' => 'Banda Aceh',
            'tanggal_lahir' => '2004-05-15',
            'jenis_kelamin' => 'L',
            'agama' => 'Islam',
            'fakultas' => 'Fakultas Sains dan Teknologi',
            'program_studi' => 'Teknik Informatika',
            'program_studi_biro' => 'Teknik Informatika',
            'pekerjaan' => 'Mahasiswa',
            'alamat' => 'Jl. Syiah Kuala No. 1',
            'status_biodata' => 'TERVERIFIKASI',
        ]);

        $response = $this->actingAs($this->operator)
            ->get(route('pemeriksaan.create', ['maba_id' => $maba->id]));

        $response->assertStatus(200);
        $response->assertSee('Ahmad Maba');
        $response->assertSee('ahmad@example.com');
        $response->assertSee('1234567890123456');
        $response->assertSee('readonly');
    }

    public function test_pemeriksaan_store_creates_record_updates_maba_status_and_generates_nomor_surat()
    {
        $maba = MabaData::create([
            'tahun_maba_id' => $this->activeTahun->id,
            'nama_biro' => 'Biro Name',
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'nik' => '9876543210987654',
            'tempat_lahir' => 'Aceh Besar',
            'tanggal_lahir' => '2005-01-10',
            'jenis_kelamin' => 'L',
            'agama' => 'Islam',
            'fakultas' => 'Fakultas Syariah dan Hukum',
            'program_studi' => 'Hukum Keluarga',
            'program_studi_biro' => 'Hukum Keluarga',
            'status_biodata' => 'TERVERIFIKASI',
        ]);

        $response = $this->actingAs($this->operator)
            ->post(route('pemeriksaan.store'), [
                'maba_data_id' => $maba->id,
                'nama' => 'Forged Name', // Should be overridden by server-side locked MabaData
                'email' => 'forged@example.com',
                'dokter_nama' => 'dr. Desminawati',
                'dokter_nip' => '198002062010012007',
                'golongan_darah' => 'O',
                'tinggi_badan' => 170,
                'berat_badan' => 65,
                'tekanan_darah' => '120/80',
                'buta_warna' => 'Tidak',
                'riwayat_penyakit_kronis' => 'Disangkal',
                'riwayat_penggunaan_obat' => 'Disangkal',
                'riwayat_alergi' => 'Disangkal',
                'kesimpulan' => 'SEHAT DAN TIDAK BUTA WARNA',
                'keperluan' => 'Persyaratan Mahasiswa Baru',
            ]);

        $maba->refresh();
        $this->assertEquals('PEMERIKSAAN_SELESAI', $maba->status_biodata);

        $pemeriksaan = Pemeriksaan::where('maba_data_id', $maba->id)->first();
        $this->assertNotNull($pemeriksaan);
        $this->assertEquals('Budi Santoso', $pemeriksaan->nama);
        $this->assertEquals('budi@example.com', $pemeriksaan->email);
        $this->assertStringContainsString('/Un.08/PPKES/', $pemeriksaan->nomor_surat);

        $response->assertRedirect(route('pemeriksaan.show', $pemeriksaan->id));
    }

    public function test_email_resend_dispatches_queue_and_uses_mail_fake()
    {
        Mail::fake();

        $pemeriksaan = Pemeriksaan::create([
            'tahun_masuk' => 2026,
            'nama' => 'Email Test Maba',
            'email' => 'emailtest@example.com',
            'nomor_surat' => '0101/Un.08/PPKES/09/2026',
            'status_pengiriman' => 'Belum dikirim',
        ]);

        $response = $this->actingAs($this->operator)
            ->post(route('pemeriksaan.sendEmail', $pemeriksaan->id));

        $pemeriksaan->refresh();
        $this->assertContains($pemeriksaan->status_pengiriman, ['Dalam antrean', 'Terkirim']);
    }

    public function test_cross_year_create_pemeriksaan_is_rejected()
    {
        $year2027 = TahunMaba::firstOrCreate(
            ['tahun' => 2027],
            [
                'nama' => 'Maba 2027',
                'is_active' => false,
            ]
        );

        $maba2027 = MabaData::create([
            'tahun_maba_id' => $year2027->id,
            'nama_biro' => 'Future Maba 2027',
            'nama_lengkap' => 'Future Maba 2027',
            'email' => 'maba2027@example.com',
            'program_studi_biro' => 'Teknik Informatika',
            'status_biodata' => 'TERVERIFIKASI',
        ]);

        $response = $this->actingAs($this->operator)
            ->get(route('pemeriksaan.create', ['maba_id' => $maba2027->id]));

        $response->assertRedirect(route('pemeriksaan.index'));
        $response->assertSessionHas('error');
    }

    public function test_public_cannot_access_internal_pemeriksaan_routes()
    {
        $this->get(route('pemeriksaan.index'))->assertRedirect(route('login'));
        $this->get(route('pemeriksaan.create'))->assertRedirect(route('login'));
    }

    public function test_legacy_4635_examination_records_safety()
    {
        $legacy = Pemeriksaan::create([
            'maba_data_id' => null,
            'nomor_surat' => '0001/Un.08/PPKES/08/2025',
            'tahun_masuk' => 2025,
            'nama' => 'Legacy Student 2025',
            'email' => 'legacy@example.com',
            'kesimpulan' => 'SEHAT',
            'status_pengiriman' => 'Terkirim',
        ]);

        $response = $this->actingAs($this->operator)
            ->get(route('pemeriksaan.show', $legacy->id));

        $response->assertStatus(200);
        $response->assertSee('Legacy Student 2025');
        $response->assertSee('0001/Un.08/PPKES/08/2025');
    }

    public function test_filtering_by_date_session_prodi_and_search_works()
    {
        $maba = MabaData::create([
            'tahun_maba_id' => $this->activeTahun->id,
            'nama_biro' => 'Filter Target Maba',
            'nama_lengkap' => 'Filter Target Maba',
            'email' => 'targetfilter@example.com',
            'nik' => '3333444455556666',
            'tanggal_jadwal' => '2026-09-15',
            'sesi_jadwal' => 2,
            'program_studi_biro' => 'Pendidikan Dokter',
            'status_biodata' => 'TERVERIFIKASI',
        ]);

        $response = $this->actingAs($this->operator)
            ->get(route('pemeriksaan.index', [
                'status_antrean' => 'belum_diperiksa',
                'tanggal' => '2026-09-15',
                'sesi' => 2,
                'prodi' => 'Pendidikan Dokter',
                'search' => 'Filter Target',
            ]));

        $response->assertStatus(200);
        $response->assertSee('Filter Target Maba');
    }
}
