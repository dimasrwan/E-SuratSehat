<?php

namespace App\Exports;

use App\Models\Pemeriksaan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PemeriksaanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    private $rowCounter = 1;
    protected $pemeriksaans;

    public function __construct($pemeriksaans = null)
    {
        $this->pemeriksaans = $pemeriksaans ?: Pemeriksaan::all();
    }

    public function collection(): \Illuminate\Support\Collection
    {
        return $this->pemeriksaans;
    }

    public function headings(): array
    {
        return [
            'No', 'Nomor Surat', 'Nama Lengkap', 'NIK', 'Umur', 'Jenis Kelamin', 'Tempat Lahir', 'Tanggal Lahir', 'Agama', 'Email', 'Fakultas / Pekerjaan', 'Alamat', 'Dokter Pemeriksa', 'Tinggi Badan (cm)', 'Berat Badan (kg)', 'Tekanan Darah (Mm/hg)', 'Golongan Darah', 'Buta Warna', 'Riwayat Penyakit Kronis', 'Riwayat Penggunaan Obat', 'Riwayat Alergi', 'Dinyatakan', 'Keperluan', 'Status Pengiriman', 'Tanggal Dibuat'
        ];
    }

    public function map($p): array
    {
        return [
            $this->rowCounter++,
            $p->nomor_surat,
            $p->nama,
            $p->nik ? "'" . $p->nik : '',
            $p->umur,
            $p->jenis_kelamin,
            $p->tempat_lahir,
            $p->tanggal_lahir,
            $p->agama,
            $p->email,
            $p->fakultas . ' / ' . $p->pekerjaan,
            $p->alamat,
            $p->dokter_nama,
            $p->tinggi_badan,
            $p->berat_badan,
            $p->tekanan_darah,
            $p->golongan_darah,
            $p->buta_warna,
            $p->riwayat_penyakit_kronis,
            $p->riwayat_penggunaan_obat,
            $p->riwayat_alergi,
            $p->kesimpulan,
            $p->keperluan,
            $p->status_pengiriman,
            $p->created_at ? $p->created_at->format('Y-m-d H:i:s') : ''
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Style the first row as bold text.
            1    => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF065F46'], // Emerald-800 color
                ]
            ],
        ];
    }
}
