<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PemeriksaanController;
use App\Http\Controllers\AuthController;
use App\Models\Pemeriksaan;

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ImportBiroController;

use App\Http\Controllers\DashboardController;

// Public Landing Page Route / Authenticated Dashboard Route
Route::get('/', function (\Illuminate\Http\Request $request) {
    if (\Illuminate\Support\Facades\Auth::check()) {
        return app(DashboardController::class)->index($request);
    }
    return view('landing');
})->name('landing');

// Guest Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/pemeriksaan/export/excel', [PemeriksaanController::class, 'exportExcel'])->name('pemeriksaan.exportExcel');
    Route::get('/pemeriksaan/export/pdf', [PemeriksaanController::class, 'exportPdf'])->name('pemeriksaan.exportPdf');
    Route::resource('pemeriksaan', PemeriksaanController::class);
    Route::get('/pemeriksaan/{pemeriksaan}/preview', [PemeriksaanController::class, 'previewPdf'])->name('pemeriksaan.preview');
    Route::get('/pemeriksaan/{pemeriksaan}/download', [PemeriksaanController::class, 'downloadPdf'])->name('pemeriksaan.download');
    Route::post('/pemeriksaan/{pemeriksaan}/send-email', [PemeriksaanController::class, 'sendEmail'])->name('pemeriksaan.sendEmail');
    Route::post('/pengiriman/bulk-send', [PemeriksaanController::class, 'sendEmailBulk'])->name('pengiriman.bulkSend');
    Route::post('/pengiriman/retry-failed', [PemeriksaanController::class, 'sendFailedBulk'])->name('pengiriman.retryFailed');

    Route::get('/pengiriman', function (\Illuminate\Http\Request $request) {
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

        $query = Pemeriksaan::whereNotNull('email')->where('email', '!=', '');
        if ($selectedTahun !== '' && $selectedTahun !== 'all') {
            $query->where('tahun_masuk', (int)$selectedTahun);
        }

        $pengiriman = $query->latest()->paginate(10)->appends($request->all());
        return view('pengiriman.index', compact('pengiriman', 'availableYears', 'selectedTahun', 'activeYear'));
    })->name('pengiriman.index');

    // Operational Data Management Routes (Data Maba, Rekap, Detail, Edit)
    Route::get('/maba', [\App\Http\Controllers\Admin\MabaManagementController::class, 'index'])->name('maba.index');
    Route::get('/maba/rekap-jadwal', [\App\Http\Controllers\Admin\MabaManagementController::class, 'rekapJadwal'])->name('maba.rekapJadwal');
    Route::get('/maba/rekap-prodi', [\App\Http\Controllers\Admin\MabaManagementController::class, 'rekapProdi'])->name('maba.rekapProdi');
    Route::get('/maba/export', [\App\Http\Controllers\Admin\MabaManagementController::class, 'exportExcel'])->name('maba.exportExcel');
    Route::get('/maba/{mabaData}', [\App\Http\Controllers\Admin\MabaManagementController::class, 'show'])->name('maba.show');
    Route::get('/maba/{mabaData}/edit', [\App\Http\Controllers\Admin\MabaManagementController::class, 'edit'])->name('maba.edit');
    Route::put('/maba/{mabaData}', [\App\Http\Controllers\Admin\MabaManagementController::class, 'update'])->name('maba.update');

    // Operator & Admin Maba Verification Routes
    Route::get('admin/maba-verifikasi', [\App\Http\Controllers\Admin\MabaVerificationController::class, 'index'])->name('admin.maba-verifikasi.index');
    Route::get('admin/maba-verifikasi/{mabaData}', [\App\Http\Controllers\Admin\MabaVerificationController::class, 'show'])->name('admin.maba-verifikasi.show');
    Route::post('admin/maba-verifikasi/{mabaData}/verify', [\App\Http\Controllers\Admin\MabaVerificationController::class, 'verify'])->name('admin.maba-verifikasi.verify');
    Route::post('admin/maba-verifikasi/{mabaData}/request-correction', [\App\Http\Controllers\Admin\MabaVerificationController::class, 'requestCorrection'])->name('admin.maba-verifikasi.request-correction');

    // Admin Routes
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');

        Route::resource('tahun-maba', \App\Http\Controllers\Admin\TahunMabaController::class)->only(['index', 'store']);
        Route::post('tahun-maba/{tahunMaba}/activate', [\App\Http\Controllers\Admin\TahunMabaController::class, 'activate'])->name('tahun-maba.activate');
        Route::put('tahun-maba/{tahunMaba}/format', [\App\Http\Controllers\Admin\TahunMabaController::class, 'updateFormat'])->name('tahun-maba.updateFormat');

        // Admin Import Biro Routes
        Route::get('import', [ImportBiroController::class, 'index'])->name('import.index');
        Route::post('import/preview', [ImportBiroController::class, 'preview'])->name('import.preview.store');
        Route::get('import/preview/{batch}', [ImportBiroController::class, 'showPreview'])->name('import.preview');
        Route::post('import/confirm/{batch}', [ImportBiroController::class, 'confirm'])->name('import.confirm');
        Route::post('import/cancel/{batch}', [ImportBiroController::class, 'cancel'])->name('import.cancel');
        Route::get('import/history', [ImportBiroController::class, 'history'])->name('import.history');
    });
});

// PUBLIC PORTAL MABA ROUTES (NO AUTH)
Route::prefix('portal-maba')->name('portal.')->group(function () {
    Route::get('/', [\App\Http\Controllers\PortalMabaController::class, 'index'])->name('index');
    Route::get('/search', [\App\Http\Controllers\PortalMabaController::class, 'search'])->middleware('throttle:10,1')->name('search');
    Route::post('/claim', [\App\Http\Controllers\PortalMabaController::class, 'claim'])->middleware('throttle:5,1')->name('claim');
    Route::get('/biodata', [\App\Http\Controllers\PortalMabaController::class, 'showForm'])->name('biodata');
    Route::post('/biodata', [\App\Http\Controllers\PortalMabaController::class, 'submitForm'])->middleware('throttle:5,1')->name('biodata.submit');
    Route::get('/success', [\App\Http\Controllers\PortalMabaController::class, 'success'])->name('success');
});

