<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PemeriksaanController;
use App\Http\Controllers\AuthController;
use App\Models\Pemeriksaan;

use App\Http\Controllers\Admin\UserController;

// Public Landing Page Route / Authenticated Dashboard Route
Route::get('/', function () {
    if (\Illuminate\Support\Facades\Auth::check()) {
        $totalPemeriksaan = Pemeriksaan::count();
        $pdfDibuat = $totalPemeriksaan;
        $emailTerkirim = Pemeriksaan::where('status_pengiriman', 'Terkirim')->count();
        $emailGagal = Pemeriksaan::where('status_pengiriman', 'Gagal')->count();
        
        $pemeriksaanTerbaru = Pemeriksaan::latest()->take(5)->get();

        $totalUser = \App\Models\User::count();
        $totalAdmin = \App\Models\User::where('role', 'admin')->count();
        $totalOperator = \App\Models\User::where('role', 'operator')->count();

        return view('dashboard', compact(
            'totalPemeriksaan',
            'pdfDibuat',
            'emailTerkirim',
            'emailGagal',
            'pemeriksaanTerbaru',
            'totalUser',
            'totalAdmin',
            'totalOperator'
        ));
    }
    return view('landing');
})->name('landing');

// Guest Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function () {
        $totalPemeriksaan = Pemeriksaan::count();
        $pdfDibuat = $totalPemeriksaan;
        $emailTerkirim = Pemeriksaan::where('status_pengiriman', 'Terkirim')->count();
        $emailGagal = Pemeriksaan::where('status_pengiriman', 'Gagal')->count();
        
        $pemeriksaanTerbaru = Pemeriksaan::latest()->take(5)->get();

        $totalUser = \App\Models\User::count();
        $totalAdmin = \App\Models\User::where('role', 'admin')->count();
        $totalOperator = \App\Models\User::where('role', 'operator')->count();

        return view('dashboard', compact(
            'totalPemeriksaan',
            'pdfDibuat',
            'emailTerkirim',
            'emailGagal',
            'pemeriksaanTerbaru',
            'totalUser',
            'totalAdmin',
            'totalOperator'
        ));
    })->name('dashboard');

    Route::get('/pemeriksaan/export/excel', [PemeriksaanController::class, 'exportExcel'])->name('pemeriksaan.exportExcel');
    Route::get('/pemeriksaan/export/pdf', [PemeriksaanController::class, 'exportPdf'])->name('pemeriksaan.exportPdf');
    Route::resource('pemeriksaan', PemeriksaanController::class);
    Route::get('/pemeriksaan/{pemeriksaan}/preview', [PemeriksaanController::class, 'previewPdf'])->name('pemeriksaan.preview');
    Route::get('/pemeriksaan/{pemeriksaan}/download', [PemeriksaanController::class, 'downloadPdf'])->name('pemeriksaan.download');
    Route::post('/pemeriksaan/{pemeriksaan}/send-email', [PemeriksaanController::class, 'sendEmail'])->name('pemeriksaan.sendEmail');
    Route::post('/pengiriman/bulk-send', [PemeriksaanController::class, 'sendEmailBulk'])->name('pengiriman.bulkSend');
    Route::post('/pengiriman/retry-failed', [PemeriksaanController::class, 'sendFailedBulk'])->name('pengiriman.retryFailed');

    Route::get('/pengiriman', function () {
        $pengiriman = Pemeriksaan::whereNotNull('email')->latest()->paginate(10);
        return view('pengiriman.index', compact('pengiriman'));
    })->name('pengiriman.index');

    // Admin Routes
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');
    });
});
