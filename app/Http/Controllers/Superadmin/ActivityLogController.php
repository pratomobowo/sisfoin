<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;

class ActivityLogController extends Controller
{
    public function index()
    {
        return view('superadmin.activity-logs.index');
    }
}
