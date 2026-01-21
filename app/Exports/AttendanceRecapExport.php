<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceRecapExport implements FromView, ShouldAutoSize
{
    protected $employees;
    protected $attendanceMatrix;
    protected $days;

    public function __construct($employees, $attendanceMatrix, $days)
    {
        $this->employees = $employees;
        $this->attendanceMatrix = $attendanceMatrix;
        $this->days = $days;
    }

    public function view(): View
    {
        return view('exports.attendance-recap', [
            'employees' => $this->employees,
            'attendanceMatrix' => $this->attendanceMatrix,
            'days' => $this->days,
        ]);
    }
}
