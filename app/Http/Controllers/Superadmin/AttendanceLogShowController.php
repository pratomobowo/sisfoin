<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;

class AttendanceLogShowController extends Controller
{
    /**
     * Display the specified attendance log.
     */
    public function show(AttendanceLog $attendanceLog)
    {
        $attendanceLog->load('mesinFinger');

        return view('superadmin.attendance-logs.show', compact('attendanceLog'));
    }
}
