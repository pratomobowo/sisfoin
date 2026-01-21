<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Employee module dashboard.
     */
    public function index(): View
    {
        return view('employee.dashboard', [
            'title' => 'Dashboard Karyawan',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'Karyawan', 'url' => null],
            ],
        ]);
    }
}
