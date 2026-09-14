<?php

namespace App\Http\Controllers;

use App\Models\Pemeriksaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PemeriksaanController extends Controller
{
    public function index(Request $request)
    {
        $activeYear = \App\Services\TahunMabaService::getActiveYearInt();
        $yearsInMaster = \App\Models\TahunMaba::orderBy('tahun', 'desc')->pluck('tahun')->toArray();
        $yearsInDb = Pemeriksaan::select('tahun_masuk')->distinct()->orderBy('tahun_masuk', 'desc')->pluck('tahun_masuk')->toArray();
        $availableYears = array_unique(array_merge([$activeYear], $yearsInMaster, $yearsInDb));
        rsort($availableYears);

        $selectedTahun = $request->input('tahun_masuk');
        if ($selectedTahun === null) {
            $selectedTahun = (string)$activeYear;
        } else if ($selectedTahun !== 'all') {
            $selectedTahun = preg_replace('/[^0-9]/', '', (string)$selectedTahun);
            if ($selectedTahun !== '' && !in_array((int)$selectedTahun, $availableYears)) {
                $selectedTahun = (string)$activeYear;
            }
        }

        $search = $request->input('search');
        $status_antrean = $request->input('status_antrean');
        
        // Prioritize today's date if today has scheduled Maba for active year and no explicit date or clear_filter requested
        $tanggal = $request->input('tanggal');
        $hasTodayScheduled = false;
        if ($selectedTahun !== 'all' && $selectedTahun !== '') {
            $hasTodayScheduled = \App\Models\MabaData::whereHas('tahunMaba', function ($q) use ($selectedTahun) {
                $q->where('tahun', (int)$selectedTahun);
            })->whereDate('tanggal_jadwal', date('Y-m-d'))->exists();
        }

        if ($tanggal === null && !$request->has('clear_filter') && $hasTodayScheduled) {
            $tanggal = date('Y-m-d');
        }

        $sesi = $request->input('sesi');
        $prodiInput = $request->input('prodi', $request->input('program_studi_id'));
        $status_email = $request->input('status_email');
        $nomor_surat = $request->input('nomor_surat');
        $kesimpulan = $request->input('kesimpulan');
        $fakultas = $request->input('fakultas');

        // Resolve prodi search string and model if numeric ID or name string is passed
        $prodi = $prodiInput;
        $prodiName = null;
        if (!empty($prodiInput)) {
            if (is_numeric($prodiInput)) {
                $prodiModel = \App\Models\ProgramStudi::where('is_active', true)->find((int)$prodiInput);
                $prodiName = $prodiModel ? $prodiModel->nama : '__INVALID_PRODI__';
            } else {
                $prodiName = $prodiInput;
            }
        }

        // Counts for tabs
        $countUnexaminedQuery = \App\Models\MabaData::where('status_biodata', 'TERVERIFIKASI')
            ->doesntHave('pemeriksaan');
        if ($selectedTahun !== 'all' && $selectedTahun !== '') {
            $countUnexaminedQuery->whereHas('tahunMaba', fn($q) => $q->where('tahun', (int)$selectedTahun));
        }
        if ($search) {
            $countUnexaminedQuery->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nama_biro', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($tanggal) {
            $countUnexaminedQuery->whereDate('tanggal_jadwal', $tanggal);
        }
        if ($sesi) {
            $countUnexaminedQuery->where('sesi_jadwal', $sesi);
        }
        if ($prodiName) {
            $countUnexaminedQuery->where(function ($q) use ($prodiName) {
                $q->whereRaw('LOWER(program_studi) LIKE ?', ['%' . strtolower($prodiName) . '%'])
                  ->orWhereRaw('LOWER(program_studi_biro) LIKE ?', ['%' . strtolower($prodiName) . '%']);
            });
        }
        if ($fakultas) {
            $countUnexaminedQuery->where('fakultas', 'like', "%{$fakultas}%");
        }
        $countBelumDiperiksa = $countUnexaminedQuery->count();

        $countExaminedQuery = Pemeriksaan::query();
        if ($selectedTahun !== 'all' && $selectedTahun !== '') {
            $countExaminedQuery->where('tahun_masuk', (int)$selectedTahun);
        }
        if ($search) {
            $countExaminedQuery->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nomor_surat', 'like', "%{$search}%")
                  ->orWhereHas('mabaData', function ($mq) use ($search) {
                      $mq->where('nama_lengkap', 'like', "%{$search}%")
                         ->orWhere('nama_biro', 'like', "%{$search}%");
                  });
            });
        }
        if ($nomor_surat) {
            $countExaminedQuery->where('nomor_surat', 'like', "%{$nomor_surat}%");
        }
        if ($tanggal) {
            $countExaminedQuery->where(function ($q) use ($tanggal) {
                $q->whereDate('created_at', $tanggal)
                  ->orWhereHas('mabaData', function ($mq) use ($tanggal) {
                      $mq->whereDate('tanggal_jadwal', $tanggal);
                  });
            });
        }
        if ($sesi) {
            $countExaminedQuery->whereHas('mabaData', function ($mq) use ($sesi) {
                $mq->where('sesi_jadwal', $sesi);
            });
        }
        if ($prodiName) {
            $countExaminedQuery->where(function ($q) use ($prodiName) {
                $q->whereRaw('LOWER(pekerjaan) LIKE ?', ['%' . strtolower($prodiName) . '%'])
                  ->orWhereHas('mabaData', function ($mq) use ($prodiName) {
                      $mq->whereRaw('LOWER(program_studi) LIKE ?', ['%' . strtolower($prodiName) . '%'])
                        ->orWhereRaw('LOWER(program_studi_biro) LIKE ?', ['%' . strtolower($prodiName) . '%']);
                  });
            });
        }
        if ($kesimpulan) {
            $countExaminedQuery->where('kesimpulan', $kesimpulan);
        }
        if ($fakultas) {
            $countExaminedQuery->where('fakultas', 'like', "%{$fakultas}%");
        }
        if ($status_email) {
            $countExaminedQuery->where('status_pengiriman', $status_email);
        }
        $countSudahDiperiksa = $countExaminedQuery->count();

        if ($status_antrean === null) {
            $status_antrean = ($countBelumDiperiksa > 0) ? 'belum_diperiksa' : 'selesai';
        }

        // 1. ANTREAN BELUM DIPERIKSA
        $unexaminedQueue = null;
        if ($status_antrean === 'belum_diperiksa' || $status_antrean === 'all') {
            $unexaminedQuery = \App\Models\MabaData::where('status_biodata', 'TERVERIFIKASI')
                ->doesntHave('pemeriksaan')
                ->with(['tahunMaba']);

            if ($selectedTahun !== 'all' && $selectedTahun !== '') {
                $unexaminedQuery->whereHas('tahunMaba', fn($q) => $q->where('tahun', (int)$selectedTahun));
            }

            if ($search) {
                $unexaminedQuery->where(function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                      ->orWhere('nama_biro', 'like', "%{$search}%")
                      ->orWhere('nik', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($tanggal) {
                $unexaminedQuery->whereDate('tanggal_jadwal', $tanggal);
            }

            if ($sesi) {
                $unexaminedQuery->where('sesi_jadwal', $sesi);
            }

            if ($prodiName) {
                $unexaminedQuery->where(function ($q) use ($prodiName) {
                    $q->whereRaw('LOWER(program_studi) LIKE ?', ['%' . strtolower($prodiName) . '%'])
                      ->orWhereRaw('LOWER(program_studi_biro) LIKE ?', ['%' . strtolower($prodiName) . '%']);
                });
            }

            if ($fakultas) {
                $unexaminedQuery->where('fakultas', 'like', "%{$fakultas}%");
            }

            $unexaminedQueue = $unexaminedQuery->orderBy('tanggal_jadwal', 'asc')
                ->orderBy('sesi_jadwal', 'asc')
                ->orderBy('id', 'asc')
                ->paginate(15, ['*'], 'page_maba')
                ->appends($request->all());
        }

        // 2. PEMERIKSAAN SELESAI
        $pemeriksaans = null;
        if ($status_antrean === 'selesai' || $status_antrean === 'all') {
            $query = Pemeriksaan::with('mabaData');

            if ($selectedTahun !== '' && $selectedTahun !== 'all') {
                $query->where('tahun_masuk', (int)$selectedTahun);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('nik', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('nomor_surat', 'like', "%{$search}%")
                      ->orWhereHas('mabaData', function ($mq) use ($search) {
                          $mq->where('nama_lengkap', 'like', "%{$search}%")
                             ->orWhere('nama_biro', 'like', "%{$search}%");
                      });
                });
            }

            if ($nomor_surat) {
                $query->where('nomor_surat', 'like', "%{$nomor_surat}%");
            }

            if ($tanggal) {
                $query->where(function ($q) use ($tanggal) {
                    $q->whereDate('created_at', $tanggal)
                      ->orWhereHas('mabaData', function ($mq) use ($tanggal) {
                          $mq->whereDate('tanggal_jadwal', $tanggal);
                      });
                });
            }

            if ($sesi) {
                $query->whereHas('mabaData', function ($mq) use ($sesi) {
                    $mq->where('sesi_jadwal', $sesi);
                });
            }

            if ($prodiName) {
                $query->where(function ($q) use ($prodiName) {
                    $q->whereRaw('LOWER(pekerjaan) LIKE ?', ['%' . strtolower($prodiName) . '%'])
                      ->orWhereHas('mabaData', function ($mq) use ($prodiName) {
                          $mq->whereRaw('LOWER(program_studi) LIKE ?', ['%' . strtolower($prodiName) . '%'])
                            ->orWhereRaw('LOWER(program_studi_biro) LIKE ?', ['%' . strtolower($prodiName) . '%']);
                      });
                });
            }

            if ($kesimpulan) {
                $query->where('kesimpulan', $kesimpulan);
            }
            if ($fakultas) {
                $query->where('fakultas', 'like', "%{$fakultas}%");
            }
            if ($status_email) {
                $query->where('status_pengiriman', $status_email);
            }

            $pemeriksaans = $query->orderBy('id', 'desc')->paginate(15, ['*'], 'page_exam')->appends($request->all());
        }

        // Master Data Fakultas & Program Studi for grouped searchable dropdown
        $masterFakultas = \App\Models\Fakultas::where('is_active', true)
            ->with(['programStudi' => function ($q) {
                $q->where('is_active', true)->orderBy('nama', 'asc');
            }])
            ->orderBy('nama', 'asc')
            ->get();

        $availableSesi = \App\Models\MabaData::whereNotNull('sesi_jadwal')
            ->select('sesi_jadwal')
            ->distinct()
            ->pluck('sesi_jadwal')
            ->toArray();
        sort($availableSesi);

        return view('pemeriksaan.index', compact(
            'pemeriksaans', 'unexaminedQueue', 'status_antrean', 'search', 'tanggal', 'sesi', 'prodi',
            'kesimpulan', 'fakultas', 'status_email', 'nomor_surat',
            'availableYears', 'selectedTahun', 'activeYear',
            'countBelumDiperiksa', 'countSudahDiperiksa',
            'masterFakultas', 'availableSesi'
        ));
    }

    public function generateNomorSurat(bool $lock = true, ?int $tahunMasuk = null): string
    {
        if ($tahunMasuk === null) {
            $tahunMasuk = \App\Services\TahunMabaService::getActiveYearInt();
        }

        $tahunMabaModel = \App\Models\TahunMaba::where('tahun', $tahunMasuk)->first();
        $startNumber = $tahunMabaModel ? (int)$tahunMabaModel->nomor_surat_mulai : 172;
        if ($startNumber < 1) {
            $startNumber = 172;
        }

        $kodeUnit = ($tahunMabaModel && !empty($tahunMabaModel->kode_unit)) ? trim($tahunMabaModel->kode_unit) : 'Un.08';
        $kodeBagian = ($tahunMabaModel && !empty($tahunMabaModel->kode_bagian)) ? trim($tahunMabaModel->kode_bagian) : 'PPKES';
        $tahunSurat = ($tahunMabaModel && !empty($tahunMabaModel->tahun_surat)) ? (int)$tahunMabaModel->tahun_surat : (int)$tahunMasuk;

        $query = Pemeriksaan::whereNotNull('nomor_surat')->where('nomor_surat', '!=', '');
        if ($lock) {
            $query->lockForUpdate();
        }
        $all_active = $query->get();
        $used_numbers = [];
        foreach ($all_active as $record) {
            $parts = explode('/', $record->nomor_surat);
            if (isset($parts[0]) && is_numeric($parts[0])) {
                $used_numbers[] = (int)$parts[0];
            }
        }
        
        $new_num_int = $startNumber; 
        if (count($used_numbers) > 0) {
            $max_num = max($used_numbers);
            $searchStart = max($startNumber, 1);
            
            $found_gap = false;
            if ($max_num >= $searchStart) {
                for ($i = $searchStart; $i <= $max_num; $i++) {
                    if (!in_array($i, $used_numbers)) {
                        $new_num_int = $i;
                        $found_gap = true;
                        break;
                    }
                }
                if (!$found_gap) {
                    $new_num_int = $max_num + 1;
                }
            } else {
                $new_num_int = $searchStart;
            }
        }

        $new_num_str = str_pad($new_num_int, 4, '0', STR_PAD_LEFT);
        $current_month = date('m');
        return "{$new_num_str}/{$kodeUnit}/{$kodeBagian}/{$current_month}/{$tahunSurat}";
    }

    public function create(Request $request)
    {
        $activeYear = \App\Services\TahunMabaService::getActiveYearInt();
        
        $mabaData = null;
        $mabaId = $request->input('maba_id') ?? $request->input('maba_data_id');
        if ($mabaId) {
            $mabaData = \App\Models\MabaData::with('tahunMaba')->find($mabaId);
            if (!$mabaData) {
                return redirect()->route('pemeriksaan.index')
                    ->with('error', 'Data Maba tidak ditemukan.');
            }

            // Cross-year isolation check
            $mabaYear = $mabaData->tahunMaba ? (int)$mabaData->tahunMaba->tahun : $activeYear;
            if ($mabaYear !== $activeYear) {
                return redirect()->route('pemeriksaan.index')
                    ->with('error', 'Akses data Maba dari tahun lain tidak diizinkan.');
            }

            if ($mabaData->pemeriksaan()->exists()) {
                return redirect()->route('pemeriksaan.index')
                    ->with('error', 'Mahasiswa ini sudah memiliki data pemeriksaan kesehatan.');
            }

            if (!in_array($mabaData->status_biodata, ['TERVERIFIKASI', 'PEMERIKSAAN_SELESAI'])) {
                return redirect()->route('admin.maba-verifikasi.index')
                    ->with('error', 'Data Maba belum terverifikasi oleh Operator.');
            }
        }

        $tahunContext = $activeYear;
        $estimated_nomor = $this->generateNomorSurat(false, $tahunContext);

        return view('pemeriksaan.create', compact('estimated_nomor', 'tahunContext', 'mabaData'));
    }

    public function store(Request $request)
    {
        $currentYear = (int)date('Y');
        $validated = $request->validate([
            'maba_data_id' => 'nullable|integer|exists:maba_datas,id',
            'tahun_masuk' => 'nullable|integer|min:2000|max:' . ($currentYear + 10),
            'nomor_surat' => 'nullable|string|max:255',
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'nik' => 'nullable|string|max:20',
            'umur' => 'nullable|integer',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|string|max:20',
            'agama' => 'nullable|string|max:50',
            'fakultas' => 'nullable|string|max:255',
            'pekerjaan' => 'nullable|string|max:255',
            'alamat' => 'nullable|string',
            'keperluan' => 'nullable|string|max:255',
            'dokter_nama' => 'nullable|string|max:255',
            'dokter_nip' => 'nullable|string|max:50',
            'golongan_darah' => 'nullable|string|max:5',
            'tinggi_badan' => 'nullable|numeric',
            'berat_badan' => 'nullable|numeric',
            'tekanan_darah' => 'nullable|string|max:20',
            'buta_warna' => 'nullable|string|max:20',
            'riwayat_penyakit_kronis' => 'nullable|string|max:255',
            'riwayat_penggunaan_obat' => 'nullable|string|max:255',
            'riwayat_alergi' => 'nullable|string|max:255',
            'kesimpulan' => 'nullable|string|max:255',
        ]);

        $activeYear = \App\Services\TahunMabaService::getActiveYearInt();
        $validated['tahun_masuk'] = $activeYear;

        // If maba_data_id is present, enforce server-side authority & pre-fill identity from MabaData
        if (!empty($validated['maba_data_id'])) {
            $mabaData = \App\Models\MabaData::with('tahunMaba')->find($validated['maba_data_id']);
            if (!$mabaData) {
                return back()->withInput()->withErrors(['maba_data_id' => 'Data Maba tidak ditemukan.']);
            }

            // Cross-year isolation check
            $mabaYear = $mabaData->tahunMaba ? (int)$mabaData->tahunMaba->tahun : $activeYear;
            if ($mabaYear !== $activeYear) {
                return redirect()->route('pemeriksaan.index')
                    ->with('error', 'Akses data Maba dari tahun lain tidak diizinkan.');
            }

            if (!in_array($mabaData->status_biodata, ['TERVERIFIKASI', 'PEMERIKSAAN_SELESAI'])) {
                return redirect()->route('admin.maba-verifikasi.index')
                    ->with('error', 'Data Maba belum terverifikasi oleh Operator.');
            }

            if ($mabaData->pemeriksaan()->exists()) {
                return back()->withInput()->withErrors(['maba_data_id' => 'Mahasiswa ini sudah memiliki data pemeriksaan kesehatan.']);
            }

            // Server-side lock identity fields from MabaData (readonly in UI)
            $validated['nama'] = $mabaData->nama_lengkap ?? $mabaData->nama_biro;
            $validated['nik'] = $mabaData->nik;
            $validated['email'] = $mabaData->email;
            $validated['tempat_lahir'] = $mabaData->tempat_lahir;
            $validated['tanggal_lahir'] = $mabaData->tanggal_lahir ? $mabaData->tanggal_lahir->format('Y-m-d') : null;
            $validated['jenis_kelamin'] = $mabaData->jenis_kelamin;
            $validated['agama'] = $mabaData->agama;
            $validated['fakultas'] = $mabaData->fakultas;
            $validated['pekerjaan'] = $mabaData->pekerjaan;
            $validated['alamat'] = $mabaData->alamat;
            $validated['umur'] = $mabaData->umur;
        }

        $maxAttempts = 5;
        $attempt = 0;
        $pemeriksaan = null;

        while ($attempt < $maxAttempts) {
            $attempt++;
            try {
                $pemeriksaan = DB::transaction(function () use ($validated, $activeYear) {
                    $validated['nomor_surat'] = $this->generateNomorSurat(true, $activeYear);
                    $record = Pemeriksaan::create($validated);
                    
                    if (!empty($validated['maba_data_id'])) {
                        \App\Models\MabaData::where('id', $validated['maba_data_id'])
                            ->update(['status_biodata' => 'PEMERIKSAAN_SELESAI']);
                    }

                    return $record;
                }, 3);
                break;
            } catch (\Illuminate\Database\UniqueConstraintViolationException | \Illuminate\Database\QueryException $e) {
                if ($attempt >= $maxAttempts) {
                    throw $e;
                }
                usleep(50000); // 50ms pause before retry
            }
        }

        return redirect()->route('pemeriksaan.show', $pemeriksaan->id)
            ->with('success', 'Data pemeriksaan berhasil disimpan.');
    }

    public function show(Pemeriksaan $pemeriksaan)
    {
        if (request()->has('tahun_masuk')) {
            $targetYear = (int)request('tahun_masuk');
            if ($pemeriksaan->tahun_masuk !== $targetYear && $targetYear !== 0) {
                return redirect()->route('pemeriksaan.index')
                    ->with('error', 'Pemeriksaan tidak ditemukan untuk tahun context yang dipilih.');
            }
        }
        return view('pemeriksaan.show', compact('pemeriksaan'));
    }

    public function edit(Pemeriksaan $pemeriksaan)
    {
        return view('pemeriksaan.edit', compact('pemeriksaan'));
    }

    public function update(Request $request, Pemeriksaan $pemeriksaan)
    {
        $currentYear = (int)date('Y');
        $validated = $request->validate([
            'tahun_masuk' => 'nullable|integer|min:2000|max:' . ($currentYear + 10),
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'nik' => 'nullable|string|max:20',
            'umur' => 'nullable|integer',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|string|max:20',
            'agama' => 'nullable|string|max:50',
            'fakultas' => 'nullable|string|max:255',
            'pekerjaan' => 'nullable|string|max:255',
            'alamat' => 'nullable|string',
            'keperluan' => 'nullable|string|max:255',
            'dokter_nama' => 'nullable|string|max:255',
            'dokter_nip' => 'nullable|string|max:50',
            'golongan_darah' => 'nullable|string|max:5',
            'tinggi_badan' => 'nullable|numeric',
            'berat_badan' => 'nullable|numeric',
            'tekanan_darah' => 'nullable|string|max:20',
            'buta_warna' => 'nullable|string|max:20',
            'riwayat_penyakit_kronis' => 'nullable|string|max:255',
            'riwayat_penggunaan_obat' => 'nullable|string|max:255',
            'riwayat_alergi' => 'nullable|string|max:255',
            'kesimpulan' => 'nullable|string|max:255',
        ]);

        unset($validated['nomor_surat'], $validated['status_pengiriman'], $validated['waktu_pengiriman'], $validated['tahun_masuk']);

        $pemeriksaan->update($validated);

        return redirect()->route('pemeriksaan.show', $pemeriksaan->id)
            ->with('success', 'Data pemeriksaan berhasil diperbarui.');
    }

    public function destroy(Pemeriksaan $pemeriksaan)
    {
        $pemeriksaan->delete();
        return redirect()->route('pemeriksaan.index')
            ->with('success', 'Data pemeriksaan berhasil dihapus.');
    }

    public function previewPdf(Pemeriksaan $pemeriksaan)
    {
        if (request()->has('tahun_masuk')) {
            $targetYear = (int)request('tahun_masuk');
            if ($pemeriksaan->tahun_masuk !== $targetYear && $targetYear !== 0) {
                abort(403, 'Akses ditolak untuk tahun context yang dipilih.');
            }
        }
        return view('pemeriksaan.preview', compact('pemeriksaan'));
    }

    public function downloadPdf(Pemeriksaan $pemeriksaan)
    {
        if (request()->has('tahun_masuk')) {
            $targetYear = (int)request('tahun_masuk');
            if ($pemeriksaan->tahun_masuk !== $targetYear && $targetYear !== 0) {
                abort(403, 'Akses ditolak untuk tahun context yang dipilih.');
            }
        }
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pemeriksaan.pdf', compact('pemeriksaan'))
            ->setPaper('a4', 'portrait');
        
        $filename = 'Surat-Keterangan-Sehat-' . str_replace(' ', '-', $pemeriksaan->nama) . '.pdf';
        
        return $pdf->download($filename);
    }

    public function sendEmail(Pemeriksaan $pemeriksaan)
    {
        if (request()->has('tahun_masuk')) {
            $targetYear = (int)request('tahun_masuk');
            if ($pemeriksaan->tahun_masuk !== $targetYear && $targetYear !== 0) {
                $msg = 'Akses ditolak untuk tahun context yang dipilih.';
                if (request()->wantsJson()) return response()->json(['success' => false, 'message' => $msg], 403);
                return back()->withErrors(['email' => $msg]);
            }
        }

        if (!$pemeriksaan->email) {
            $msg = 'Alamat email mahasiswa tidak tersedia.';
            if (request()->wantsJson()) return response()->json(['success' => false, 'message' => $msg], 400);
            return back()->withErrors(['email' => $msg]);
        }

        if (in_array($pemeriksaan->status_pengiriman, ['Dalam antrean', 'Mengirim'])) {
            $msg = 'Email untuk ' . $pemeriksaan->nama . ' sudah berada dalam antrean atau sedang dikirim.';
            if (request()->wantsJson()) return response()->json(['success' => true, 'message' => $msg]);
            return back()->with('success', $msg);
        }

        $pemeriksaan->update([
            'status_pengiriman' => 'Dalam antrean'
        ]);

        \App\Jobs\SendSuratSehatJob::dispatch($pemeriksaan);

        $msg = 'Email untuk ' . $pemeriksaan->nama . ' berhasil dimasukkan ke antrean pengiriman.';
        if (request()->wantsJson()) return response()->json(['success' => true, 'message' => $msg]);
        return back()->with('success', $msg);
    }

    public function sendEmailBulk(Request $request)
    {
        $ids = $request->input('pemeriksaan_ids', []);
        
        if (empty($ids)) {
            $msg = 'Tidak ada data yang dipilih.';
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => $msg], 400);
            return back()->withErrors(['bulk' => $msg]);
        }

        $pemeriksaans = Pemeriksaan::whereIn('id', $ids)->whereNotNull('email')->where('email', '!=', '')->get();
        
        if ($pemeriksaans->isEmpty()) {
            $msg = 'Data terpilih tidak memiliki email yang valid.';
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => $msg], 400);
            return back()->withErrors(['bulk' => $msg]);
        }

        $dispatched = 0;
        foreach ($pemeriksaans as $pemeriksaan) {
            if (in_array($pemeriksaan->status_pengiriman, ['Dalam antrean', 'Mengirim'])) {
                continue;
            }

            $pemeriksaan->update([
                'status_pengiriman' => 'Dalam antrean'
            ]);

            \App\Jobs\SendSuratSehatJob::dispatch($pemeriksaan);
            $dispatched++;
        }

        $pesan = $dispatched . ' email berhasil dimasukkan ke antrean pengiriman.';
        if ($dispatched === 0) {
            $pesan = 'Semua email terpilih sudah berada dalam antrean atau sedang dikirim.';
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $pesan]);
        }

        return back()->with('success', $pesan);
    }

    public function sendFailedBulk(Request $request)
    {
        $pemeriksaans = Pemeriksaan::whereNotNull('email')
            ->where('email', '!=', '')
            ->where('status_pengiriman', 'Gagal')
            ->get();
        
        if ($pemeriksaans->isEmpty()) {
            $msg = 'Tidak ada email gagal yang perlu dikirim ulang.';
            if ($request->wantsJson()) return response()->json(['success' => true, 'message' => $msg]);
            return back()->with('success', $msg);
        }

        $dispatched = 0;
        foreach ($pemeriksaans as $pemeriksaan) {
            $pemeriksaan->update([
                'status_pengiriman' => 'Dalam antrean'
            ]);

            \App\Jobs\SendSuratSehatJob::dispatch($pemeriksaan);
            $dispatched++;
        }

        $msg = $dispatched . ' dari ' . $pemeriksaans->count() . ' email gagal berhasil dimasukkan ke antrean pengiriman.';
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }
        return back()->with('success', $msg);
    }

    private function applyFilters(Request $request)
    {
        $activeYear = \App\Services\TahunMabaService::getActiveYearInt();
        $yearsInMaster = \App\Models\TahunMaba::orderBy('tahun', 'desc')->pluck('tahun')->toArray();
        $yearsInDb = Pemeriksaan::select('tahun_masuk')->distinct()->orderBy('tahun_masuk', 'desc')->pluck('tahun_masuk')->toArray();
        $availableYears = array_unique(array_merge([$activeYear], $yearsInMaster, $yearsInDb));

        $tahun_masuk = $request->input('tahun_masuk');
        if ($tahun_masuk === null) {
            $tahun_masuk = (string)$activeYear;
        } else if ($tahun_masuk !== 'all') {
            $tahun_masuk = preg_replace('/[^0-9]/', '', (string)$tahun_masuk);
            if ($tahun_masuk !== '' && !in_array((int)$tahun_masuk, $availableYears)) {
                $tahun_masuk = (string)$activeYear;
            }
        }

        $prodiInput = $request->input('prodi', $request->input('program_studi_id'));
        $search = $request->input('search');
        $tanggal_awal = $request->input('tanggal_awal');
        $tanggal_akhir = $request->input('tanggal_akhir');
        $kesimpulan = $request->input('kesimpulan');
        $fakultas = $request->input('fakultas');
        $jenis_kelamin = $request->input('jenis_kelamin');
        $status_email = $request->input('status_email');
        $riwayat_medis = $request->input('riwayat_medis');
        $nomor_surat = $request->input('nomor_surat');

        $prodiName = null;
        if (!empty($prodiInput)) {
            if (is_numeric($prodiInput)) {
                $prodiModel = \App\Models\ProgramStudi::where('is_active', true)->find((int)$prodiInput);
                $prodiName = $prodiModel ? $prodiModel->nama : '__INVALID_PRODI__';
            } else {
                $prodiName = $prodiInput;
            }
        }
        
        $query = Pemeriksaan::query();

        if ($tahun_masuk !== '' && $tahun_masuk !== 'all') {
            $query->where('tahun_masuk', (int)$tahun_masuk);
        }

        if ($prodiName) {
            $query->where(function ($q) use ($prodiName) {
                $q->whereRaw('LOWER(pekerjaan) LIKE ?', ['%' . strtolower($prodiName) . '%'])
                  ->orWhereHas('mabaData', function ($mq) use ($prodiName) {
                      $mq->whereRaw('LOWER(program_studi) LIKE ?', ['%' . strtolower($prodiName) . '%'])
                        ->orWhereRaw('LOWER(program_studi_biro) LIKE ?', ['%' . strtolower($prodiName) . '%']);
                  });
            });
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('nomor_surat', 'like', "%{$search}%");
            });
        }
        
        if ($nomor_surat) {
            $query->where('nomor_surat', 'like', "%{$nomor_surat}%");
        }
        if ($tanggal_awal) {
            $query->whereDate('created_at', '>=', $tanggal_awal);
        }
        if ($tanggal_akhir) {
            $query->whereDate('created_at', '<=', $tanggal_akhir);
        }
        if ($kesimpulan) {
            $query->where('kesimpulan', $kesimpulan);
        }
        if ($fakultas) {
            $query->where('fakultas', 'like', "%{$fakultas}%");
        }
        if ($jenis_kelamin) {
            $query->where('jenis_kelamin', $jenis_kelamin);
        }
        if ($status_email) {
            $query->where('status_pengiriman', $status_email);
        }
        if ($riwayat_medis) {
            if ($riwayat_medis == 'Ada') {
                $query->where(function($q) {
                    $q->where('riwayat_penyakit_kronis', '!=', 'Disangkal')
                      ->orWhere('riwayat_penggunaan_obat', '!=', 'Disangkal')
                      ->orWhere('riwayat_alergi', '!=', 'Disangkal');
                });
            } elseif ($riwayat_medis == 'Tidak Ada') {
                $query->where(function($q) {
                    $q->where('riwayat_penyakit_kronis', 'Disangkal')
                      ->where('riwayat_penggunaan_obat', 'Disangkal')
                      ->where('riwayat_alergi', 'Disangkal');
                });
            }
        }
        
        return $query->orderBy('id', 'desc')->get();
    }

    public function exportExcel(Request $request)
    {
        $pemeriksaans = $this->applyFilters($request);
        $fileName = 'Data_Pemeriksaan_' . date('Y-m-d_H-i-s') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\PemeriksaanExport($pemeriksaans), $fileName);
    }

    public function exportPdf(Request $request)
    {
        $pemeriksaans = $this->applyFilters($request);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pemeriksaan.export_pdf', compact('pemeriksaans'))->setPaper('a4', 'landscape');
        
        $filename = 'Data-Pemeriksaan-' . date('Y-m-d_H-i-s') . '.pdf';
        
        return $pdf->download($filename);
    }
}
