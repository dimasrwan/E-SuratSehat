<?php

namespace App\Http\Controllers;

use App\Models\Pemeriksaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PemeriksaanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $tanggal = $request->input('tanggal');
        $kesimpulan = $request->input('kesimpulan');
        $fakultas = $request->input('fakultas');
        $jenis_kelamin = $request->input('jenis_kelamin');
        $status_email = $request->input('status_email');
        $riwayat_medis = $request->input('riwayat_medis');
        
        $nomor_surat = $request->nomor_surat;
        
        $query = Pemeriksaan::query();

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

        if ($tanggal) {
            $query->whereDate('created_at', '=', $tanggal);
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

        $pemeriksaans = $query->orderBy('id', 'desc')->paginate(10)->appends($request->all());

        return view('pemeriksaan.index', compact('pemeriksaans', 'search', 'tanggal', 'kesimpulan', 'fakultas', 'jenis_kelamin', 'status_email', 'riwayat_medis', 'nomor_surat'));
    }

    public function generateNomorSurat(bool $lock = true): string
    {
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
        
        $new_num_int = 172; 
        if (count($used_numbers) > 0) {
            $max_num = max($used_numbers);
            $found_gap = false;
            for ($i = 172; $i <= $max_num; $i++) {
                if (!in_array($i, $used_numbers)) {
                    $new_num_int = $i;
                    $found_gap = true;
                    break;
                }
            }
            if (!$found_gap) {
                $new_num_int = $max_num + 1;
            }
        }

        $new_num_str = str_pad($new_num_int, 4, '0', STR_PAD_LEFT);
        $current_month = date('m');
        $current_year = date('Y');
        return "{$new_num_str}/Un.08/PPKES/{$current_month}/{$current_year}";
    }

    public function create()
    {
        $estimated_nomor = $this->generateNomorSurat(false);
        return view('pemeriksaan.create', compact('estimated_nomor'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
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

        $maxAttempts = 5;
        $attempt = 0;
        $pemeriksaan = null;

        while ($attempt < $maxAttempts) {
            $attempt++;
            try {
                $pemeriksaan = DB::transaction(function () use ($validated) {
                    $validated['nomor_surat'] = $this->generateNomorSurat(true);
                    return Pemeriksaan::create($validated);
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
        return view('pemeriksaan.show', compact('pemeriksaan'));
    }

    public function edit(Pemeriksaan $pemeriksaan)
    {
        return view('pemeriksaan.edit', compact('pemeriksaan'));
    }

    public function update(Request $request, Pemeriksaan $pemeriksaan)
    {
        $validated = $request->validate([
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

        unset($validated['nomor_surat'], $validated['status_pengiriman'], $validated['waktu_pengiriman']);

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
        return view('pemeriksaan.preview', compact('pemeriksaan'));
    }

    public function downloadPdf(Pemeriksaan $pemeriksaan)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pemeriksaan.pdf', compact('pemeriksaan'))
            ->setPaper('a4', 'portrait');
        
        $filename = 'Surat-Keterangan-Sehat-' . str_replace(' ', '-', $pemeriksaan->nama) . '.pdf';
        
        return $pdf->download($filename);
    }

    public function sendEmail(Pemeriksaan $pemeriksaan)
    {
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
        $search = $request->input('search');
        $tanggal_awal = $request->input('tanggal_awal');
        $tanggal_akhir = $request->input('tanggal_akhir');
        $kesimpulan = $request->input('kesimpulan');
        $fakultas = $request->input('fakultas');
        $jenis_kelamin = $request->input('jenis_kelamin');
        $status_email = $request->input('status_email');
        $riwayat_medis = $request->input('riwayat_medis');
        $nomor_surat = $request->input('nomor_surat');
        
        $query = Pemeriksaan::query();

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
