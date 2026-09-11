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
        ], [
            'tahun.required' => 'Tahun Maba wajib diisi.',
            'tahun.integer' => 'Tahun Maba harus berupa angka bulat.',
            'tahun.min' => 'Tahun Maba minimal adalah 2000.',
            'tahun.max' => 'Tahun Maba maksimal adalah ' . ($currentYear + 10) . '.',
            'tahun.unique' => 'Tahun Maba tersebut sudah terdaftar di sistem.',
        ]);

        $tahunInt = (int) $validated['tahun'];

        TahunMaba::create([
            'tahun' => $tahunInt,
            'nama' => 'Maba ' . $tahunInt,
            'is_active' => false, // Default is NONAKTIF
        ]);

        return redirect()->route('admin.tahun-maba.index')
            ->with('success', "Tahun Maba {$tahunInt} berhasil ditambahkan (Status: Nonaktif).");
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
