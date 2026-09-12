<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MabaData;
use App\Services\MabaVerificationService;
use Illuminate\Http\Request;

class MabaVerificationController extends Controller
{
    protected MabaVerificationService $verificationService;

    public function __construct(MabaVerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    /**
     * Display list of Maba pending verification for Operator.
     */
    public function index(Request $request)
    {
        $tahunId = $request->input('tahun_id');
        $search = $request->input('search');
        $statusFilter = $request->input('status');

        $verifications = $this->verificationService->getVerifications($tahunId, $search, $statusFilter);
        $years = \App\Models\TahunMaba::orderBy('tahun', 'desc')->get();
        $activeYear = \App\Services\TahunMabaService::getActiveYear();

        return view('admin.maba_verifikasi.index', compact('verifications', 'years', 'activeYear', 'search', 'statusFilter', 'tahunId'));
    }

    /**
     * Show detail of a single Maba for verification.
     */
    public function show(MabaData $mabaData)
    {
        return view('admin.maba_verifikasi.show', compact('mabaData'));
    }

    /**
     * Operator approves verification.
     */
    public function verify(Request $request, MabaData $mabaData)
    {
        try {
            $this->verificationService->verifyIdentity($mabaData, $request->user());
            return redirect()->route('admin.maba-verifikasi.index')->with('success', "Identitas {$mabaData->nama_lengkap} berhasil diverifikasi.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Operator requests correction.
     */
    public function requestCorrection(Request $request, MabaData $mabaData)
    {
        $request->validate([
            'catatan_perbaikan' => 'required|string|max:1000',
        ], [
            'catatan_perbaikan.required' => 'Catatan perbaikan wajib diisi.',
            'catatan_perbaikan.max' => 'Catatan perbaikan maksimal 1000 karakter.',
        ]);

        try {
            $catatan = $request->input('catatan_perbaikan');
            $this->verificationService->requestCorrection($mabaData, $request->user(), $catatan);
            return redirect()->route('admin.maba-verifikasi.index')->with('success', "Status data {$mabaData->nama_lengkap} diubah menjadi PERLU PERBAIKAN.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
