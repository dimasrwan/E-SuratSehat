<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PemeriksaanController;
use App\Http\Controllers\AuthController;
use App\Models\Pemeriksaan;

// Guest Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Authenticated Operator Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', function () {
        $totalPemeriksaan = Pemeriksaan::count();
        $pdfDibuat = $totalPemeriksaan;
        $emailTerkirim = Pemeriksaan::where('status_pengiriman', 'Terkirim')->count();
        $emailGagal = Pemeriksaan::where('status_pengiriman', 'Gagal')->count();
        
        $pemeriksaanTerbaru = Pemeriksaan::latest()->take(5)->get();

        return view('dashboard', compact(
            'totalPemeriksaan',
            'pdfDibuat',
            'emailTerkirim',
            'emailGagal',
            'pemeriksaanTerbaru'
        ));
    });

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
});
