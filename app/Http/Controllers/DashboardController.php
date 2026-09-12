<?php

namespace App\Http\Controllers;

use App\Services\DashboardMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected DashboardMonitoringService $dashboardService;

    public function __construct(DashboardMonitoringService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display the operational dashboard view.
     */
    public function index(Request $request)
    {
        $selectedTahun = $request->input('tahun_masuk');
        $user = Auth::user();

        $stats = $this->dashboardService->getDashboardStats($selectedTahun, $user);

        return view('dashboard', $stats);
    }
}
