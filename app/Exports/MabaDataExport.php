<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MabaDataExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected Collection $mabaCollection;

    public function __construct(Collection $mabaCollection)
    {
        $this->mabaCollection = $mabaCollection;
    }

    public function collection(): \Illuminate\Support\Enumerable
    {
        return $this->mabaCollection;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tahun Maba',
            'Nama Biro',
            'Program Studi Biro',
            'Tanggal Jadwal',
            'Sesi Jadwal',
            'Waktu Jadwal',
            'Nama Lengkap',
            'NIK',
            'Email',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Jenis Kelamin',
            'Agama',
            'Fakultas',
            'Program Studi Final',
            'Pekerjaan',
            'Alamat',
            'Status Biodata',
            'Status Examination',
            'Nomor Surat Medis',
            'Diverifikasi Pada',
        ];
    }

    public function map($maba): array
    {
        return [
            $maba->id,
            $maba->tahunMaba->tahun ?? '-',
            $maba->nama_biro,
            $maba->program_studi_biro,
            $maba->tanggal_jadwal ? $maba->tanggal_jadwal->format('Y-m-d') : '-',
            $maba->sesi_jadwal ?? '-',
            $maba->waktu_jadwal ?? '-',
            $maba->nama_lengkap ?? '-',
            $maba->nik ?? '-',
            $maba->email ?? '-',
            $maba->tempat_lahir ?? '-',
            $maba->tanggal_lahir ? $maba->tanggal_lahir->format('Y-m-d') : '-',
            $maba->jenis_kelamin ?? '-',
            $maba->agama ?? '-',
            $maba->fakultas ?? '-',
            $maba->program_studi ?? $maba->program_studi_biro,
            $maba->pekerjaan ?? 'Mahasiswa',
            $maba->alamat ?? '-',
            $maba->status_biodata,
            $maba->pemeriksaan ? 'SELESAI' : 'BELUM',
            $maba->pemeriksaan->nomor_surat ?? '-',
            $maba->verified_at ? $maba->verified_at->format('Y-m-d H:i') : '-',
        ];
    }
}
