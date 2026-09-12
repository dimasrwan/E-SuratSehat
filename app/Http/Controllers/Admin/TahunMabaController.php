<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunMaba;
use App\Services\TahunMabaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TahunMabaController extends Controller
{
    public function index()
    {
        $tahunMabas = TahunMaba::orderBy('tahun', 'desc')->get();
        $activeYearInt = TahunMabaService::getActiveYearInt();

        return view('admin.tahun_maba.index', compact('tahunMabas', 'activeYearInt'));
    }

    public function store(Request $request)
    {
        $currentYear = (int) date('Y');
        $validated = $request->validate([
            'tahun' => [
                'required',
                'integer',
                'min:2000',
                'max:' . ($currentYear + 10),
                'unique:tahun_mabas,tahun',
            ],
            'nomor_surat_mulai' => [
                'nullable',
                'integer',
                'min:1',
                'max:99999',
            ],
            'kode_unit' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[^\/]+$/',
            ],
            'kode_bagian' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[^\/]+$/',
            ],
            'tahun_surat' => [
                'nullable',
                'integer',
                'min:2000',
                'max:2100',
            ],
        ], [
            'tahun.required' => 'Tahun Maba wajib diisi.',
            'tahun.integer' => 'Tahun Maba harus berupa angka bulat.',
            'tahun.min' => 'Tahun Maba minimal adalah 2000.',
            'tahun.max' => 'Tahun Maba maksimal adalah ' . ($currentYear + 10) . '.',
            'tahun.unique' => 'Tahun Maba tersebut sudah terdaftar di sistem.',
            'nomor_surat_mulai.integer' => 'Nomor Surat Mulai harus berupa angka bulat.',
            'nomor_surat_mulai.min' => 'Nomor Surat Mulai minimal adalah 1.',
            'nomor_surat_mulai.max' => 'Nomor Surat Mulai maksimal adalah 99999.',
            'kode_unit.regex' => 'Kode Unit tidak boleh mengandung karakter garis miring (/).',
            'kode_bagian.regex' => 'Kode Bagian tidak boleh mengandung karakter garis miring (/).',
            'tahun_surat.integer' => 'Tahun Surat harus berupa angka bulat.',
            'tahun_surat.min' => 'Tahun Surat minimal adalah 2000.',
            'tahun_surat.max' => 'Tahun Surat maksimal adalah 2100.',
        ]);

        $tahunInt = (int) $validated['tahun'];
        $nomorMulai = isset($validated['nomor_surat_mulai']) && $validated['nomor_surat_mulai'] !== null 
            ? (int) $validated['nomor_surat_mulai'] 
            : 172;
        $kodeUnit = isset($validated['kode_unit']) && trim($validated['kode_unit']) !== ''
            ? trim($validated['kode_unit'])
            : 'Un.08';
        $kodeBagian = isset($validated['kode_bagian']) && trim($validated['kode_bagian']) !== ''
            ? trim($validated['kode_bagian'])
            : 'PPKES';
        $tahunSurat = isset($validated['tahun_surat']) && $validated['tahun_surat'] !== null
            ? (int) $validated['tahun_surat']
            : $tahunInt;

        TahunMaba::create([
            'tahun' => $tahunInt,
            'nama' => 'Maba ' . $tahunInt,
            'nomor_surat_mulai' => $nomorMulai,
            'kode_unit' => $kodeUnit,
            'kode_bagian' => $kodeBagian,
            'tahun_surat' => $tahunSurat,
            'is_active' => false, // Default is NONAKTIF
        ]);

        return redirect()->route('admin.tahun-maba.index')
            ->with('success', "Tahun Maba {$tahunInt} berhasil ditambahkan.");
    }

    public function updateFormat(Request $request, TahunMaba $tahunMaba)
    {
        $validated = $request->validate([
            'nomor_surat_mulai' => [
                'required',
                'integer',
                'min:1',
                'max:99999',
            ],
            'kode_unit' => [
                'required',
                'string',
                'max:50',
                'regex:/^[^\/]+$/',
            ],
            'kode_bagian' => [
                'required',
                'string',
                'max:50',
                'regex:/^[^\/]+$/',
            ],
            'tahun_surat' => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
            ],
        ], [
            'nomor_surat_mulai.required' => 'Nomor Surat Mulai wajib diisi.',
            'nomor_surat_mulai.integer' => 'Nomor Surat Mulai harus berupa angka bulat.',
            'nomor_surat_mulai.min' => 'Nomor Surat Mulai minimal adalah 1.',
            'nomor_surat_mulai.max' => 'Nomor Surat Mulai maksimal adalah 99999.',
            'kode_unit.required' => 'Kode Unit wajib diisi.',
            'kode_unit.regex' => 'Kode Unit tidak boleh mengandung karakter garis miring (/).',
            'kode_bagian.required' => 'Kode Bagian wajib diisi.',
            'kode_bagian.regex' => 'Kode Bagian tidak boleh mengandung karakter garis miring (/).',
            'tahun_surat.required' => 'Tahun Surat wajib diisi.',
            'tahun_surat.integer' => 'Tahun Surat harus berupa angka bulat.',
            'tahun_surat.min' => 'Tahun Surat minimal adalah 2000.',
            'tahun_surat.max' => 'Tahun Surat maksimal adalah 2100.',
        ]);

        $tahunMaba->update([
            'nomor_surat_mulai' => (int) $validated['nomor_surat_mulai'],
            'kode_unit' => trim($validated['kode_unit']),
            'kode_bagian' => trim($validated['kode_bagian']),
            'tahun_surat' => (int) $validated['tahun_surat'],
        ]);

        return redirect()->route('admin.tahun-maba.index')
            ->with('success', "Pengaturan format nomor surat untuk Maba {$tahunMaba->tahun} berhasil diperbarui.");
    }

    public function activate(Request $request, TahunMaba $tahunMaba)
    {
        $success = TahunMabaService::activateYear($tahunMaba->id, Auth::user());

        if (!$success) {
            return redirect()->route('admin.tahun-maba.index')
                ->with('error', 'Gagal mengaktifkan Tahun Maba. Silakan coba lagi.');
        }

        return redirect()->route('admin.tahun-maba.index')
            ->with('success', "Berhasil! Tahun Maba {$tahunMaba->tahun} kini AKTIF untuk seluruh operator.");
    }
}
