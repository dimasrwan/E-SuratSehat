<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MabaData;
use App\Models\TahunMaba;
use App\Services\TahunMabaService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MabaManagementController extends Controller
{
    /**
     * Display centralized Maba data management list with search, filter, and pagination.
     */
    public function index(Request $request)
    {
        $activeYear = TahunMabaService::getActiveYearInt();
        $yearsInMaster = TahunMaba::orderBy('tahun', 'desc')->pluck('tahun')->toArray();
        $availableYears = array_unique(array_merge([$activeYear], $yearsInMaster));
        rsort($availableYears);

        $selectedTahun = $request->input('tahun', $request->input('tahun_masuk'));
        if ($selectedTahun === null) {
            $selectedTahun = (string)$activeYear;
        } else if ($selectedTahun !== 'all') {
            $selectedTahun = preg_replace('/[^0-9]/', '', (string)$selectedTahun);
            if ($selectedTahun !== '' && !in_array((int)$selectedTahun, $availableYears)) {
                $selectedTahun = (string)$activeYear;
            }
        }

        $search = $request->input('search');
        $statusFilter = $request->input('status');
        $prodiFilter = $request->input('prodi');
        $jadwalFilter = $request->input('jadwal');
        $sort = $request->input('sort', 'latest');
        $dir = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $query = MabaData::query()->with(['tahunMaba', 'pemeriksaan', 'verifier']);

        if ($selectedTahun !== '' && $selectedTahun !== 'all') {
            $tahunModel = TahunMaba::where('tahun', (int)$selectedTahun)->first();
            if ($tahunModel) {
                $query->where('tahun_maba_id', $tahunModel->id);
            }
        }

        if (!empty($statusFilter)) {
            $query->where('status_biodata', $statusFilter);
        }

        if (!empty($prodiFilter)) {
            $cleanProdi = mb_strtolower(trim($prodiFilter), 'UTF-8');
            $query->whereRaw('LOWER(program_studi_biro) LIKE ?', ["%{$cleanProdi}%"]);
        }

        if (!empty($jadwalFilter)) {
            $query->whereDate('tanggal_jadwal', $jadwalFilter);
        }

        if (!empty($search)) {
            $cleanSearch = mb_strtolower(trim($search), 'UTF-8');
            $query->where(function ($q) use ($cleanSearch) {
                $q->whereRaw('LOWER(nama_biro) LIKE ?', ["%{$cleanSearch}%"])
                  ->orWhereRaw('LOWER(nama_lengkap) LIKE ?', ["%{$cleanSearch}%"])
                  ->orWhere('nik', 'like', "%{$cleanSearch}%")
                  ->orWhere('email', 'like', "%{$cleanSearch}%")
                  ->orWhereRaw('LOWER(program_studi_biro) LIKE ?', ["%{$cleanSearch}%"]);
            });
        }

        // Whitelisted sorting
        if ($sort === 'name') {
            $query->orderBy('nama_biro', $dir);
        } elseif ($sort === 'jadwal') {
            $query->orderBy('tanggal_jadwal', $dir);
        } elseif ($sort === 'status') {
            $query->orderBy('status_biodata', $dir);
        } else {
            $query->orderBy('updated_at', $dir === 'asc' ? 'desc' : 'asc');
        }

        $mabaList = $query->paginate(15)->withQueryString();

        // Unique Prodis for filter dropdown
        $prodiList = MabaData::distinct()->pluck('program_studi_biro')->filter()->values();

        return view('admin.maba.index', compact(
            'mabaList', 'availableYears', 'selectedTahun', 'activeYear', 
            'search', 'statusFilter', 'prodiFilter', 'jadwalFilter', 'prodiList', 'sort', 'dir'
        ));
    }

    /**
     * Display single Maba details including identity, biro data, status timeline & examination.
     */
    public function show(Request $request, MabaData $mabaData)
    {
        $activeYear = TahunMabaService::getActiveYearInt();
        $selectedTahun = $request->input('tahun', $request->input('tahun_masuk'));

        if ($selectedTahun !== null && $selectedTahun !== 'all') {
            $tahunModel = TahunMaba::where('tahun', (int)$selectedTahun)->first();
            if ($tahunModel && $mabaData->tahun_maba_id !== $tahunModel->id) {
                abort(404, 'Record Maba tidak ditemukan pada context tahun yang dipilih.');
            }
        }

        $mabaData->load(['tahunMaba', 'verifier', 'pemeriksaan']);
        return view('admin.maba.show', compact('mabaData'));
    }

    /**
     * Show edit form for Admin & Operator to correct Maba biodata.
     */
    public function edit(Request $request, MabaData $mabaData)
    {
        $selectedTahun = $request->input('tahun', $request->input('tahun_masuk'));

        if ($selectedTahun !== null && $selectedTahun !== 'all') {
            $tahunModel = TahunMaba::where('tahun', (int)$selectedTahun)->first();
            if ($tahunModel && $mabaData->tahun_maba_id !== $tahunModel->id) {
                abort(404, 'Record Maba tidak ditemukan pada context tahun yang dipilih.');
            }
        }

        $mabaData->load(['tahunMaba', 'pemeriksaan']);

        $fakultasOptions = [
            'Fakultas Adab dan Humaniora',
            'Fakultas Dakwah dan Komunikasi',
            'Fakultas Ekonomi dan Bisnis Islam',
            'Fakultas Ilmu Tarbiyah dan Keguruan',
            'Fakultas Syariah dan Hukum',
            'Fakultas Ushuluddin dan Filsafat',
            'Fakultas Sains dan Teknologi',
            'Fakultas Psikologi',
            'Fakultas Kedokteran dan Ilmu Kesehatan',
            'Pascasarjana',
        ];

        $agamaOptions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu'];
        $jkOptions = ['Laki-laki', 'Perempuan'];

        return view('admin.maba.edit', compact('mabaData', 'fakultasOptions', 'agamaOptions', 'jkOptions'));
    }

    /**
     * Handle updating Maba biodata by Admin & Operator.
     */
    public function update(Request $request, MabaData $mabaData)
    {
        $selectedTahun = $request->input('tahun', $request->input('tahun_masuk'));

        if ($selectedTahun !== null && $selectedTahun !== 'all') {
            $tahunModel = TahunMaba::where('tahun', (int)$selectedTahun)->first();
            if ($tahunModel && $mabaData->tahun_maba_id !== $tahunModel->id) {
                abort(404, 'Record Maba tidak ditemukan pada context tahun yang dipilih.');
            }
        }

        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nik' => 'required|numeric|digits:16',
            'email' => 'required|email|max:255',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date|before:today|after:1940-01-01',
            'jenis_kelamin' => 'required|string|in:Laki-laki,Perempuan,L,P',
            'agama' => 'required|string|max:50',
            'fakultas' => 'required|string|max:255',
            'pekerjaan' => 'required|string|max:255',
            'alamat' => 'required|string|max:1000',
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus tepat 16 digit angka.',
            'nik.numeric' => 'NIK harus berupa angka.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.before' => 'Tanggal lahir tidak boleh di masa depan.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'agama.required' => 'Agama wajib dipilih.',
            'fakultas.required' => 'Fakultas wajib dipilih.',
            'pekerjaan.required' => 'Pekerjaan wajib diisi.',
            'alamat.required' => 'Alamat wajib diisi.',
        ]);

        $jkRaw = $validated['jenis_kelamin'];
        $jkFinal = null;
        if (in_array($jkRaw, ['Laki-laki', 'Laki-Laki', 'L'])) {
            $jkFinal = 'L';
        } elseif (in_array($jkRaw, ['Perempuan', 'P'])) {
            $jkFinal = 'P';
        }

        // Whitelist ONLY editable biodata fields (strictly ignoring server-owned fields)
        $updateData = [
            'nama_lengkap' => trim($validated['nama_lengkap']),
            'nik' => preg_replace('/[^0-9]/', '', $validated['nik']),
            'email' => strtolower(trim($validated['email'])),
            'tempat_lahir' => trim($validated['tempat_lahir']),
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'jenis_kelamin' => $jkFinal,
            'agama' => $validated['agama'],
            'fakultas' => trim($validated['fakultas']),
            'pekerjaan' => trim($validated['pekerjaan']),
            'alamat' => trim($validated['alamat']),
        ];

        // Workflow state transition rules upon edit by Admin/Operator:
        // - BELUM_MENGISI -> becomes MENUNGGU_VERIFIKASI
        // - PERLU_PERBAIKAN -> status becomes MENUNGGU_VERIFIKASI, catatan_perbaikan = null, verified_at = null, verified_by = null
        // - TERVERIFIKASI / PEMERIKSAAN_SELESAI -> status biodata becomes MENUNGGU_VERIFIKASI, verified_at = null, verified_by = null
        if ($mabaData->status_biodata === 'PERLU_PERBAIKAN') {
            $updateData['status_biodata'] = 'MENUNGGU_VERIFIKASI';
            $updateData['catatan_perbaikan'] = null;
            $updateData['verified_at'] = null;
            $updateData['verified_by'] = null;
        } elseif (in_array($mabaData->status_biodata, ['TERVERIFIKASI', 'PEMERIKSAAN_SELESAI'])) {
            $updateData['status_biodata'] = 'MENUNGGU_VERIFIKASI';
            $updateData['verified_at'] = null;
            $updateData['verified_by'] = null;
        } elseif ($mabaData->status_biodata === 'BELUM_MENGISI') {
            $updateData['status_biodata'] = 'MENUNGGU_VERIFIKASI';
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($mabaData, $updateData) {
            $mabaData->update($updateData);
        });

        return redirect()->route('maba.show', $mabaData)
            ->with('success', 'Data biodata mahasiswa berhasil diperbarui.');
    }

    /**
     * Rekap Jadwal Operasional Maba.
     */
    public function rekapJadwal(Request $request)
    {
        $activeYear = TahunMabaService::getActiveYearInt();
        $tahunSelected = $request->input('tahun', $request->input('tahun_masuk', (string)$activeYear));
        $prodiSelected = $request->input('prodi');
        $tahunModel = TahunMaba::where('tahun', (int)$tahunSelected)->first();

        $query = MabaData::query();
        if ($tahunModel) {
            $query->where('tahun_maba_id', $tahunModel->id);
        }

        if (!empty($prodiSelected)) {
            $cleanProdi = mb_strtolower(trim($prodiSelected), 'UTF-8');
            $query->whereRaw('LOWER(program_studi_biro) LIKE ?', ["%{$cleanProdi}%"]);
        }

        $totalMaba = (clone $query)->count();
        $totalSelesai = (clone $query)->where('status_biodata', 'PEMERIKSAAN_SELESAI')->count();
        $totalTerverifikasi = (clone $query)->where('status_biodata', 'TERVERIFIKASI')->count();
        $totalBelumDipemeriksa = (clone $query)->whereIn('status_biodata', ['BELUM_MENGISI', 'MENUNGGU_VERIFIKASI', 'PERLU_PERBAIKAN', 'TERVERIFIKASI'])->count();

        $rekapJadwal = (clone $query)
            ->select(
                'tanggal_jadwal',
                'sesi_jadwal',
                'waktu_jadwal',
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_maba'),
                \Illuminate\Support\Facades\DB::raw("SUM(CASE WHEN status_biodata = 'BELUM_MENGISI' THEN 1 ELSE 0 END) as total_belum_mengisi"),
                \Illuminate\Support\Facades\DB::raw("SUM(CASE WHEN status_biodata = 'MENUNGGU_VERIFIKASI' THEN 1 ELSE 0 END) as total_menunggu_verifikasi"),
                \Illuminate\Support\Facades\DB::raw("SUM(CASE WHEN status_biodata = 'PERLU_PERBAIKAN' THEN 1 ELSE 0 END) as total_perlu_perbaikan"),
                \Illuminate\Support\Facades\DB::raw("SUM(CASE WHEN status_biodata = 'TERVERIFIKASI' THEN 1 ELSE 0 END) as total_terverifikasi"),
                \Illuminate\Support\Facades\DB::raw("SUM(CASE WHEN status_biodata = 'PEMERIKSAAN_SELESAI' THEN 1 ELSE 0 END) as total_selesai")
            )
            ->groupBy('tanggal_jadwal', 'sesi_jadwal', 'waktu_jadwal')
            ->orderBy('tanggal_jadwal', 'asc')
            ->get();

        $tahunList = TahunMaba::orderBy('tahun', 'desc')->pluck('tahun')->toArray();
        $prodiList = MabaData::distinct()->pluck('program_studi_biro')->filter()->values();

        return view('admin.maba.rekap_jadwal', compact(
            'rekapJadwal', 'tahunSelected', 'prodiSelected', 'tahunList', 
            'totalMaba', 'totalSelesai', 'totalTerverifikasi', 'totalBelumDipemeriksa', 'prodiList'
        ));
    }

    /**
     * Rekap Program Studi Operasional Maba.
     */
    public function rekapProdi(Request $request)
    {
        $activeYear = TahunMabaService::getActiveYearInt();
        $tahunSelected = $request->input('tahun', $request->input('tahun_masuk', (string)$activeYear));
        $tahunModel = TahunMaba::where('tahun', (int)$tahunSelected)->first();

        $query = MabaData::query();
        if ($tahunModel) {
            $query->where('tahun_maba_id', $tahunModel->id);
        }

        $rekapProdi = (clone $query)
            ->select(
                'program_studi_biro',
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_maba'),
                \Illuminate\Support\Facades\DB::raw("SUM(CASE WHEN status_biodata = 'BELUM_MENGISI' THEN 1 ELSE 0 END) as total_belum_mengisi"),
                \Illuminate\Support\Facades\DB::raw("SUM(CASE WHEN status_biodata = 'MENUNGGU_VERIFIKASI' THEN 1 ELSE 0 END) as total_menunggu_verifikasi"),
                \Illuminate\Support\Facades\DB::raw("SUM(CASE WHEN status_biodata = 'PERLU_PERBAIKAN' THEN 1 ELSE 0 END) as total_perlu_perbaikan"),
                \Illuminate\Support\Facades\DB::raw("SUM(CASE WHEN status_biodata = 'TERVERIFIKASI' THEN 1 ELSE 0 END) as total_terverifikasi"),
                \Illuminate\Support\Facades\DB::raw("SUM(CASE WHEN status_biodata = 'PEMERIKSAAN_SELESAI' THEN 1 ELSE 0 END) as total_selesai")
            )
            ->groupBy('program_studi_biro')
            ->orderBy('total_maba', 'desc')
            ->get();

        $tahunList = TahunMaba::orderBy('tahun', 'desc')->pluck('tahun')->toArray();

        return view('admin.maba.rekap_prodi', compact('rekapProdi', 'tahunSelected', 'tahunList'));
    }

    /**
     * Export Maba data following active filters to Excel.
     */
    public function exportExcel(Request $request)
    {
        $activeYear = TahunMabaService::getActiveYearInt();
        $selectedTahun = $request->input('tahun', $request->input('tahun_masuk', (string)$activeYear));
        $statusFilter = $request->input('status');
        $prodiFilter = $request->input('prodi');
        $search = $request->input('search');
        $sort = $request->input('sort', 'name');
        $dir = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $query = MabaData::query()->with(['tahunMaba', 'pemeriksaan']);

        if ($selectedTahun !== 'all' && !empty($selectedTahun)) {
            $tahunModel = TahunMaba::where('tahun', (int)$selectedTahun)->first();
            if ($tahunModel) {
                $query->where('tahun_maba_id', $tahunModel->id);
            }
        }

        if (!empty($statusFilter)) {
            $query->where('status_biodata', $statusFilter);
        }

        if (!empty($prodiFilter)) {
            $cleanProdi = mb_strtolower(trim($prodiFilter), 'UTF-8');
            $query->whereRaw('LOWER(program_studi_biro) LIKE ?', ["%{$cleanProdi}%"]);
        }

        if (!empty($search)) {
            $cleanSearch = mb_strtolower(trim($search), 'UTF-8');
            $query->where(function ($q) use ($cleanSearch) {
                $q->whereRaw('LOWER(nama_biro) LIKE ?', ["%{$cleanSearch}%"])
                  ->orWhereRaw('LOWER(nama_lengkap) LIKE ?', ["%{$cleanSearch}%"])
                  ->orWhere('nik', 'like', "%{$cleanSearch}%")
                  ->orWhere('email', 'like', "%{$cleanSearch}%");
            });
        }

        // Whitelisted sorting for export
        if ($sort === 'jadwal') {
            $query->orderBy('tanggal_jadwal', $dir);
        } elseif ($sort === 'status') {
            $query->orderBy('status_biodata', $dir);
        } else {
            $query->orderBy('nama_biro', $dir);
        }

        $mabaData = $query->get();

        $fileName = 'Data_Maba_' . ($selectedTahun !== 'all' ? $selectedTahun . '_' : '') . date('Y-m-d_H-i') . '.xlsx';

        return Excel::download(new \App\Exports\MabaDataExport($mabaData), $fileName);
    }
}
