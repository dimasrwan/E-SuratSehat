<?php

namespace Database\Seeders;

use App\Models\Fakultas;
use App\Models\ProgramStudi;
use Illuminate\Database\Seeder;

class FakultasProdiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'nama' => 'Fakultas Tarbiyah dan Keguruan',
                'kode' => 'FTK',
                'prodi' => [
                    'Pendidikan Agama Islam',
                    'Pendidikan Bahasa Arab',
                    'Pendidikan Bahasa Inggris',
                    'Manajemen Pendidikan Islam',
                    'Pendidikan Matematika',
                    'Pendidikan Fisika',
                    'Pendidikan Biologi',
                    'Pendidikan Kimia',
                    'Pendidikan Guru Madrasah Ibtidaiyah',
                    'Pendidikan Islam Anak Usia Dini',
                    'Pendidikan Teknik Elektro',
                    'Pendidikan Teknologi Informasi',
                    'Bimbingan dan Konseling',
                    'Pendidikan Profesi Guru',
                ]
            ],
            [
                'nama' => 'Fakultas Syariah dan Hukum',
                'kode' => 'FSH',
                'prodi' => [
                    'Hukum Keluarga',
                    'Hukum Ekonomi Syariah',
                    'Hukum Pidana Islam',
                    'Hukum Tata Negara',
                    'Perbandingan Mazhab',
                    'Ilmu Hukum',
                ]
            ],
            [
                'nama' => 'Fakultas Ushuluddin dan Filsafat',
                'kode' => 'FUF',
                'prodi' => [
                    'Aqidah dan Filsafat Islam',
                    'Ilmu Al-Qur\'an dan Tafsir',
                    'Ilmu Hadis',
                    'Studi Agama-Agama',
                    'Sosiologi Agama',
                ]
            ],
            [
                'nama' => 'Fakultas Dakwah dan Komunikasi',
                'kode' => 'FDK',
                'prodi' => [
                    'Komunikasi dan Penyiaran Islam',
                    'Bimbingan dan Konseling Islam',
                    'Manajemen Dakwah',
                    'Pengembangan Masyarakat Islam',
                    'Kesejahteraan Sosial',
                    'Manajemen Haji dan Umrah',
                ]
            ],
            [
                'nama' => 'Fakultas Adab dan Humaniora',
                'kode' => 'FAH',
                'prodi' => [
                    'Sejarah dan Kebudayaan Islam',
                    'Bahasa dan Sastra Arab',
                    'Ilmu Perpustakaan',
                ]
            ],
            [
                'nama' => 'Fakultas Ekonomi dan Bisnis Islam',
                'kode' => 'FEBI',
                'prodi' => [
                    'Ekonomi Syariah',
                    'Perbankan Syariah',
                    'Ilmu Ekonomi',
                    'Manajemen Bisnis Syariah',
                    'Manajemen Industri Halal',
                ]
            ],
            [
                'nama' => 'Fakultas Sains dan Teknologi',
                'kode' => 'SAINTEK',
                'prodi' => [
                    'Arsitektur',
                    'Teknik Lingkungan',
                    'Biologi',
                    'Kimia',
                    'Teknik Fisika',
                    'Teknologi Informasi',
                ]
            ],
            [
                'nama' => 'Fakultas Ilmu Sosial dan Ilmu Pemerintahan',
                'kode' => 'FISIP',
                'prodi' => [
                    'Ilmu Administrasi Negara',
                    'Ilmu Politik',
                ]
            ],
            [
                'nama' => 'Fakultas Psikologi',
                'kode' => 'FPSI',
                'prodi' => [
                    'Psikologi',
                ]
            ],
            [
                'nama' => 'Fakultas Kedokteran',
                'kode' => 'FK',
                'prodi' => [
                    'Kedokteran',
                    'Pendidikan Profesi Dokter',
                ]
            ],
        ];

        foreach ($data as $fIndex => $fData) {
            $fSortOrder = $fIndex + 1;
            $fakultas = Fakultas::where('kode', $fData['kode'])->first();
            
            if (!$fakultas) {
                $fakultas = Fakultas::create([
                    'kode' => $fData['kode'],
                    'nama' => $fData['nama'],
                    'is_active' => true,
                    'sort_order' => $fSortOrder,
                ]);
            } else {
                $fakultas->update([
                    'nama' => $fData['nama'],
                    'sort_order' => $fSortOrder,
                ]);
            }

            foreach ($fData['prodi'] as $pIndex => $pNama) {
                $pSortOrder = $pIndex + 1;
                $prodi = ProgramStudi::where('fakultas_id', $fakultas->id)
                    ->where('nama', $pNama)
                    ->first();

                if (!$prodi) {
                    ProgramStudi::create([
                        'fakultas_id' => $fakultas->id,
                        'nama' => $pNama,
                        'is_active' => true,
                        'sort_order' => $pSortOrder,
                    ]);
                } else {
                    $prodi->update([
                        'sort_order' => $pSortOrder,
                    ]);
                }
            }
        }
    }
}
