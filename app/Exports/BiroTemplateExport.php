<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BiroTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'Nama Peserta',
            'Program Studi',
            'Tanggal Pemeriksaan',
            'Sesi',
            'Waktu Pemeriksaan',
        ];
    }

    public function array(): array
    {
        return [
            [
                'AFIFA JAHRA',
                'Bimbingan Konseling',
                '2026-08-15',
                'Sesi 1',
                '08.00 s/d 12.30',
            ],
            [
                'IMELDA',
                'Pendidikan Agama Islam',
                '2026-08-15',
                'Sesi 2',
                '13.00 s/d 17.30',
            ],
            [
                'MUHAMMAD RAIHAN',
                'Teknologi Informasi',
                '2026-08-21',
                'Sesi 1',
                '08.00 s/d 12.00',
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '047857'], // Emerald 700
                ],
            ],
        ];
    }
}
