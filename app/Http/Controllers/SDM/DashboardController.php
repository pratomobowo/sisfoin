<?php

namespace App\Http\Controllers\SDM;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    /**
     * Display the SDM dashboard.
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('dashboard');
    }
}
