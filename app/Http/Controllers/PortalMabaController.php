<?php

namespace App\Http\Controllers;

use App\Services\PortalMabaService;
use Illuminate\Http\Request;

class PortalMabaController extends Controller
{
    protected PortalMabaService $portalService;

    public function __construct(PortalMabaService $portalService)
    {
        $this->portalService = $portalService;
    }

    /**
     * Show Public Portal Landing / Search Page.
     */
    public function index()
    {
        $activeYear = $this->portalService->getActiveYear();
        return view('portal.index', compact('activeYear'));
    }

    /**
     * Search Maba records in active year.
     */
    public function search(Request $request)
    {
        $activeYear = $this->portalService->getActiveYear();
        if (!$activeYear) {
            return redirect()->route('portal.index')->with('error', 'Sistem belum memiliki Tahun Maba aktif.');
        }

        $request->validate([
            'nama' => 'required|string|min:2|max:100',
            'program_studi' => 'nullable|string|max:100',
        ], [
            'nama.required' => 'Masukkan nama lengkap untuk mencari data.',
            'nama.min' => 'Nama minimal 2 karakter.',
        ]);

        $nama = $request->input('nama');
        $prodi = $request->input('program_studi');

        $results = $this->portalService->searchMaba($nama, $prodi);

        return view('portal.index', compact('activeYear', 'results', 'nama', 'prodi'));
    }

    /**
     * Claim Maba record ("Ini Data Saya").
     */
    public function claim(Request $request)
    {
        $request->validate([
            'maba_id' => 'required|integer',
        ]);

        try {
            $this->portalService->claimRecord((int)$request->input('maba_id'));
            return redirect()->route('portal.biodata');
        } catch (\Exception $e) {
            return redirect()->route('portal.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Show Biodata Entry Form.
     */
    public function showForm()
    {
        $claimedMaba = $this->portalService->getClaimedMabaData();
        if (!$claimedMaba) {
            return redirect()->route('portal.index')->with('error', 'Sesi pengisian telah berakhir. Silakan cari data Anda kembali.');
        }

        $fakultas = \App\Models\Fakultas::where('is_active', true)->orderBy('sort_order', 'asc')->get();

        return view('portal.form', compact('claimedMaba', 'fakultas'));
    }

    /**
     * Submit Biodata Form.
     */
    public function submitForm(Request $request)
    {
        $claimedMaba = $this->portalService->getClaimedMabaData();
        if (!$claimedMaba) {
            return redirect()->route('portal.index')->with('error', 'Sesi pengisian telah berakhir. Silakan cari data Anda kembali.');
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
            'pekerjaan' => 'nullable|string|max:255',
            'alamat' => 'required|string|max:1000',
            'konfirmasi' => 'required|accepted',
        ], [
            'nik.digits' => 'NIK harus berjumlah 16 digit angka.',
            'tanggal_lahir.before' => 'Tanggal lahir tidak boleh di masa depan.',
            'tanggal_lahir.after' => 'Tanggal lahir tidak valid.',
            'konfirmasi.accepted' => 'Anda harus menyetujui pernyataan kebenaran data.',
        ]);

        try {
            $submittedMaba = $this->portalService->submitBiodata($validated);
            // Flash success state to session for success page
            session(['submitted_maba_id' => $submittedMaba->id]);
            return redirect()->route('portal.success');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Show Success Confirmation Page.
     */
    public function success()
    {
        $submittedId = session('submitted_maba_id');
        $mabaData = null;
        if ($submittedId) {
            $mabaData = \App\Models\MabaData::find($submittedId);
        }

        return view('portal.success', compact('mabaData'));
    }
}
