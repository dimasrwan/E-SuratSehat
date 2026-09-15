<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImportBatch;
use App\Models\TahunMaba;
use App\Services\BiroImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImportBiroController extends Controller
{
    protected BiroImportService $importService;

    public function __construct(BiroImportService $importService)
    {
        $this->importService = $importService;
    }

    public function index()
    {
        $tahunMabas = TahunMaba::orderBy('tahun', 'desc')->get();
        $recentBatches = ImportBatch::with(['tahunMaba', 'user'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.import.index', compact('tahunMabas', 'recentBatches'));
    }

    public function preview(Request $request)
    {
        $validated = $request->validate([
            'tahun_maba_id' => ['required', 'integer', 'exists:tahun_mabas,id'],
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB
                'mimes:xlsx,xls,csv,txt,pdf',
            ],
        ], [
            'tahun_maba_id.required' => 'Target Tahun Maba wajib dipilih.',
            'tahun_maba_id.exists' => 'Tahun Maba yang dipilih tidak terdaftar di sistem.',
            'file.required' => 'File data Biro wajib diunggah.',
            'file.mimes' => 'Format file harus berupa Excel (.xlsx, .xls), CSV (.csv), atau PDF (.pdf).',
            'file.max' => 'Ukuran file maksimal adalah 10 MB.',
        ]);

        try {
            $batch = $this->importService->createBatchAndParsePreview(
                $request->file('file'),
                (int) $validated['tahun_maba_id'],
                Auth::user()
            );

            return redirect()->route('admin.import.preview', $batch->id);
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    public function showPreview(ImportBatch $batch)
    {
        if ($batch->status !== 'PREVIEW') {
            return redirect()->route('admin.import.index')
                ->with('error', 'Batch impor tersebut sudah tidak dalam status PREVIEW.');
        }

        $batch->load(['tahunMaba', 'user']);

        // Load preview rows grouped by classification
        $newRows = $batch->rows()->where('classification', 'NEW')->paginate(20, ['*'], 'page_new');
        $possibleDuplicates = $batch->rows()->where('classification', 'POSSIBLE_DUPLICATE')->with('existingMabaData')->paginate(20, ['*'], 'page_possible');
        $exactDuplicates = $batch->rows()->where('classification', 'EXACT_DUPLICATE')->paginate(20, ['*'], 'page_exact');
        $errorRows = $batch->rows()->where('classification', 'ERROR')->paginate(20, ['*'], 'page_error');

        return view('admin.import.preview', compact(
            'batch',
            'newRows',
            'possibleDuplicates',
            'exactDuplicates',
            'errorRows'
        ));
    }

    public function confirm(Request $request, ImportBatch $batch)
    {
        if ($batch->status !== 'PREVIEW') {
            return redirect()->route('admin.import.index')
                ->with('error', 'Batch impor tersebut sudah diproses atau dibatalkan.');
        }

        $actions = $request->input('actions', []);

        try {
            $completedBatch = $this->importService->confirmBatch($batch, $actions);

            return redirect()->route('admin.import.history')
                ->with('success', "Import berhasil! {$completedBatch->inserted_rows} Maba baru ditambahkan, {$completedBatch->updated_rows} jadwal diperbarui, dan {$completedBatch->skipped_rows} data dilewati.");
        } catch (\Throwable $e) {
            return redirect()->route('admin.import.preview', $batch->id)
                ->with('error', 'Gagal memproses konfirmasi import: ' . $e->getMessage());
        }
    }

    public function cancel(ImportBatch $batch)
    {
        if ($batch->status === 'PREVIEW') {
            $this->importService->cancelBatch($batch);
            return redirect()->route('admin.import.index')
                ->with('info', 'Proses import data Biro telah dibatalkan.');
        }

        return redirect()->route('admin.import.index');
    }

    public function history()
    {
        $batches = ImportBatch::with(['tahunMaba', 'user'])
            ->latest()
            ->paginate(15);

        return view('admin.import.history', compact('batches'));
    }

    public function downloadTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\BiroTemplateExport(),
            'Template_Import_Data_Biro_Maba.xlsx'
        );
    }
}
