<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\SuperadminService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private SuperadminService $superadminService
    ) {
        $this->middleware('auth');
        $this->middleware('role:super-admin');
    }

    /**
     * Display the superadmin dashboard.
     */
    public function index(): View
    {
        $stats = $this->superadminService->getDashboardStats();
        $systemHealth = $this->superadminService->getSystemHealth();

        return view('dashboard', compact('stats', 'systemHealth'));
    }

    /**
     * Get dashboard statistics via AJAX.
     */
    public function getStats(): JsonResponse
    {
        $stats = $this->superadminService->getDashboardStats();
        
        return response()->json($stats);
    }

    /**
     * Get system health status via AJAX.
     */
    public function getSystemHealth(): JsonResponse
    {
        $health = $this->superadminService->getSystemHealth();
        
        return response()->json($health);
    }

    /**
     * Get user growth chart data.
     */
    public function getUserGrowthData(Request $request): JsonResponse
    {
        $days = $request->get('days', 30);
        $data = $this->superadminService->getUserGrowthData($days);
        
        return response()->json($data);
    }

    /**
     * Get recent activities.
     */
    public function getRecentActivities(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $activities = $this->superadminService->getRecentActivities($limit);
        
        return response()->json($activities);
    }

    /**
     * Clear dashboard caches.
     */
    public function clearCaches(): JsonResponse
    {
        $success = $this->superadminService->clearCaches();
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'Cache berhasil dibersihkan' : 'Gagal membersihkan cache'
        ]);
    }
}
