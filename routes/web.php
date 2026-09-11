<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PemeriksaanController;
use App\Http\Controllers\AuthController;
use App\Models\Pemeriksaan;

use App\Http\Controllers\Admin\UserController;

// Helper function for dashboard data calculation
if (!function_exists('getDashboardData')) {
    function getDashboardData(\Illuminate\Http\Request $request) {
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

        $query = Pemeriksaan::query();
        if ($selectedTahun !== '' && $selectedTahun !== 'all') {
            $query->where('tahun_masuk', (int)$selectedTahun);
        }

        $totalPemeriksaan = (clone $query)->count();
        $pdfDibuat = $totalPemeriksaan;
        $emailTerkirim = (clone $query)->where('status_pengiriman', 'Terkirim')->count();
        $emailGagal = (clone $query)->where('status_pengiriman', 'Gagal')->count();
        
        $pemeriksaanTerbaru = (clone $query)->latest()->take(5)->get();

        $totalUser = \App\Models\User::count();
        $totalAdmin = \App\Models\User::where('role', 'admin')->count();
        $totalOperator = \App\Models\User::where('role', 'operator')->count();

        return compact(
            'totalPemeriksaan',
            'pdfDibuat',
            'emailTerkirim',
            'emailGagal',
            'pemeriksaanTerbaru',
            'totalUser',
            'totalAdmin',
            'totalOperator',
            'availableYears',
            'selectedTahun',
            'activeYear'
        );
    }
}

// Public Landing Page Route / Authenticated Dashboard Route
Route::get('/', function (\Illuminate\Http\Request $request) {
    if (\Illuminate\Support\Facades\Auth::check()) {
        return view('dashboard', getDashboardData($request));
    }
    return view('landing');
})->name('landing');

// Guest Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function (\Illuminate\Http\Request $request) {
        return view('dashboard', getDashboardData($request));
    })->name('dashboard');

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

    // Admin Routes
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');

        Route::resource('tahun-maba', \App\Http\Controllers\Admin\TahunMabaController::class)->only(['index', 'store']);
        Route::post('tahun-maba/{tahunMaba}/activate', [\App\Http\Controllers\Admin\TahunMabaController::class, 'activate'])->name('tahun-maba.activate');
    });
});
