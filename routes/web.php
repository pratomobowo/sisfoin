<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SDM\DosenController;
use App\Http\Controllers\SDM\EmployeeController;
use App\Http\Controllers\SDM\SlipGajiController;
use App\Http\Controllers\Superadmin\ActivityLogController;
use App\Http\Controllers\Superadmin\RoleController as SuperadminRoleController;
use App\Http\Controllers\Superadmin\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    /** @var \App\Models\User|null $user */
    $user = Auth::user();

    if (request()->boolean('admin')) {
        foreach (['super-admin', 'admin-sdm', 'admin-sekretariat'] as $adminRole) {
            if ($user && $user->hasRole($adminRole)) {
                setActiveRole($adminRole);
                break;
            }
        }
    }

    if (isActiveRole('staff|employee')) {
        return redirect()->route('staff.dashboard');
    }

    if (! $user?->canAny([
        'users.view',
        'roles.view',
        'employees.view',
        'dosen.view',
        'payroll.view',
        'employee.attendance.view',
        'employee.attendance.edit',
        'employee.attendance.report',
        'sekretariat.view',
        'surat_keputusan.view',
    ])) {
        abort(403);
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Role switching routes
    Route::post('/switch-role', [RoleController::class, 'switchRole'])->name('role.switch');
    Route::get('/available-roles', [RoleController::class, 'getAvailableRoles'])->name('role.available');
});

// Superadmin routes
Route::prefix('superadmin')->middleware(['auth', 'role:super-admin'])->name('superadmin.')->group(function () {
    // Dashboard
    Route::get('/', [\App\Http\Controllers\Superadmin\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/clear-caches', [\App\Http\Controllers\Superadmin\DashboardController::class, 'clearCaches'])->name('dashboard.clear-caches');

    // User management routes
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', function () {
        return view('superadmin.users.create');
    })->name('users.create');
    Route::get('/users/{id}/edit', function ($id) {
        return view('superadmin.users.edit', ['userId' => $id]);
    })->name('users.edit');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/absensi/logs', App\Livewire\Superadmin\AttendanceLogs::class)->name('absensi.logs');
    Route::get('/absensi/laporan', App\Livewire\Sdm\EmployeeAttendanceManagement::class)->name('absensi.management');
    Route::get('/absensi/recap', App\Livewire\Sdm\AttendanceRecap::class)->name('absensi.recap');

    // Role management routes
    Route::get('/roles', [SuperadminRoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [SuperadminRoleController::class, 'create'])->name('roles.create');

    Route::get('/roles/{id}', [SuperadminRoleController::class, 'show'])->name('roles.show');
    Route::get('/roles/{id}/edit', [SuperadminRoleController::class, 'edit'])->name('roles.edit');

    // Activity Logs routes
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    // System Services route
    Route::get('/system-services', App\Livewire\Superadmin\SystemServices::class)->name('system-services.index');
    Route::get('/operations-console', App\Livewire\Superadmin\OperationsConsole::class)->name('operations-console.index');

    // SMTP Management routes
    Route::get('/smtp', function () {
        return view('superadmin.smtp.index');
    })->name('smtp.index');

    Route::get('/email-log', function () {
        return view('superadmin.email-log.index');
    })->name('email-log.index');

    // Fingerprint Management routes
    Route::prefix('fingerprint')->name('fingerprint.')->group(function () {
        // Attendance Logs routes
        Route::get('/attendance-logs', function () {
            return view('superadmin.attendance-logs.index');
        })->name('attendance-logs.index');

        Route::get('/attendance-logs/{attendanceLog}', [\App\Http\Controllers\Superadmin\AttendanceLogShowController::class, 'show'])->name('attendance-logs.show');
    });
});

// SDM routes
Route::prefix('sdm')->middleware(['auth'])->name('sdm.')->group(function () {
    // SDM Dashboard
    Route::get('/', function () {
        return view('sdm.dashboard');
    })->middleware('can:employees.view')->name('dashboard');

    // Employee management routes
    Route::get('/employees', [EmployeeController::class, 'index'])->middleware('can:employees.view')->name('employees.index');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->middleware('can:employees.create')->name('employees.create');
    Route::get('/employees/{id}', [EmployeeController::class, 'show'])->middleware('can:employees.view')->name('employees.show');
    Route::get('/employees/{id}/edit', [EmployeeController::class, 'edit'])->middleware('can:employees.edit')->name('employees.edit');

    // Dosen management routes
    Route::get('/dosens', [DosenController::class, 'index'])->middleware('can:dosen.view')->name('dosens.index');
    Route::get('/dosens/create', [DosenController::class, 'create'])->middleware('can:dosen.create')->name('dosens.create');
    Route::get('/dosens/{id}', [DosenController::class, 'show'])->middleware('can:dosen.view')->name('dosens.show');
    Route::get('/dosens/{id}/edit', [DosenController::class, 'edit'])->middleware('can:dosen.edit')->name('dosens.edit');

    // Slip Gaji routes
    Route::prefix('slip-gaji')->middleware(['can:payroll.view'])->name('slip-gaji.')->group(function () {
        Route::get('/', [SlipGajiController::class, 'index'])->name('index');
        Route::get('/create', [SlipGajiController::class, 'create'])->name('create');
        Route::get('/upload', [SlipGajiController::class, 'upload'])->name('upload');
        Route::post('/upload', [SlipGajiController::class, 'processUpload'])->name('upload.process');
        Route::get('/upload/preview/{token}', [SlipGajiController::class, 'showUploadPreview'])->name('upload.preview.show');
        Route::post('/upload/preview/{token}/confirm', [SlipGajiController::class, 'confirmUploadPreview'])->name('upload.preview.confirm');
        Route::post('/upload/preview/{token}/cancel', [SlipGajiController::class, 'cancelUploadPreview'])->name('upload.preview.cancel');
        Route::get('/download-template', [SlipGajiController::class, 'downloadTemplate'])->name('download-template');
        Route::get('/{header}/update-upload', [SlipGajiController::class, 'showUpdateUpload'])->name('update-upload');
        Route::post('/{header}/update-upload', [SlipGajiController::class, 'processUpdateUpload'])->name('update-upload.process');

        // Preview and confirm routes removed - direct import implemented
        Route::delete('/{header}/cancel', [SlipGajiController::class, 'cancel'])->name('cancel');

        Route::get('/{header}', [SlipGajiController::class, 'show'])->name('show');
        Route::get('/{detail}/edit', [SlipGajiController::class, 'edit'])->name('edit');
        Route::put('/{detail}', [SlipGajiController::class, 'update'])->name('update');
        Route::get('/{detail}/preview-pdf-slip', [SlipGajiController::class, 'previewPdfSlip'])->name('preview-pdf-slip');
        Route::get('/{detail}/download-pdf-slip', [SlipGajiController::class, 'downloadPdfSlip'])->name('download-pdf-slip');
        Route::get('/{header}/download-bulk-pdf', [SlipGajiController::class, 'downloadBulkPdf'])->name('download-bulk-pdf');
        Route::delete('/{header}', [SlipGajiController::class, 'destroy'])->name('destroy');

        // Publish/Unpublish routes
        Route::post('/{id}/publish', [SlipGajiController::class, 'publish'])->name('publish');
        Route::post('/{id}/unpublish', [SlipGajiController::class, 'unpublish'])->name('unpublish');

        // Bulk email routes
        Route::post('/{header}/send-bulk-email', [SlipGajiController::class, 'sendBulkEmail'])->name('send-bulk-email');
        Route::get('/{header}/email-logs', [SlipGajiController::class, 'showEmailLogs'])->name('email-logs');
        Route::post('/{header}/retry-failed-emails', [SlipGajiController::class, 'retryFailedEmails'])->name('retry-failed-emails');
    });

    // Employee Attendance routes
    Route::prefix('employee-attendance')->name('employee-attendance.')->group(function () {
        Route::get('/', function () {
            return view('sdm.employee-attendance.index');
        })->middleware('can:employee.attendance.view')->name('index');
    });

    // Absensi Routes (New)
    Route::get('/absensi', App\Livewire\Sdm\EmployeeAttendanceManagement::class)->middleware('can:employee.attendance.edit')->name('absensi.management');
    Route::get('/absensi/monitor', App\Livewire\Sdm\AttendanceMonitor::class)->middleware('can:employee.attendance.view')->name('absensi.monitor');
    Route::get('/absensi/recap', App\Livewire\Sdm\AttendanceRecap::class)->middleware('can:employee.attendance.report')->name('absensi.recap');
    Route::get('/absensi/logs', App\Livewire\Superadmin\AttendanceLogs::class)->middleware('can:employee.attendance.edit')->name('absensi.logs');
    Route::get('/absensi/settings', App\Livewire\Sdm\AttendanceSettingsManager::class)->middleware('can:employee.attendance.edit')->name('absensi.settings');
    Route::get('/absensi/holidays', App\Livewire\Sdm\HolidayManager::class)->middleware('can:employee.attendance.edit')->name('absensi.holidays');
    Route::get('/absensi/shifts', App\Livewire\Sdm\WorkShiftManager::class)->middleware('can:employee.attendance.edit')->name('absensi.shifts');
    Route::get('/absensi/kelola-shift', App\Livewire\Sdm\UnitShiftManagement::class)->middleware('can:employee.attendance.edit')->name('absensi.kelola-shift');
    Route::get('/absensi/kelola-shift/{unit}', App\Livewire\Sdm\UnitShiftCalendar::class)->middleware('can:employee.attendance.edit')->name('absensi.unit-detail');
    Route::get('/absensi/kelola-shift/{unit}/list', App\Livewire\Sdm\UnitShiftDetail::class)->middleware('can:employee.attendance.edit')->name('absensi.unit-list');

    // Fingerprint management routes under sdm
    Route::prefix('fingerprint')->name('fingerprint.')->group(function () {
        Route::get('/', function () {
            return view('superadmin.fingerprint.attendance-logs');
        })->middleware('can:employee.attendance.edit')->name('index');
    });
});

// Sekretariat routes
Route::prefix('sekretariat')->middleware(['auth'])->name('sekretariat.')->group(function () {
    // Sekretariat Dashboard -> redirect to unified dashboard
    Route::get('/', function () {
        return redirect()->route('dashboard');
    })->name('dashboard');

    // Surat Keputusan routes
    Route::get('/surat-keputusan', function () {
        return view('sekretariat.surat-keputusan.index');
    })->middleware(['can:surat_keputusan.view'])->name('surat-keputusan.index');

    // Kegiatan Pejabat routes
    Route::get('/kegiatan-pejabat', function () {
        return view('sekretariat.kegiatan-pejabat.index');
    })->middleware(['can:sekretariat.view'])->name('kegiatan-pejabat.index');

    // Sekretariat announcement management (admin dashboard)
    Route::prefix('pengumuman')->middleware(['can:sekretariat.view'])->name('pengumuman.')->group(function () {
        Route::get('/', [App\Http\Controllers\Sekretariat\AnnouncementController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Sekretariat\AnnouncementController::class, 'store'])->middleware('can:sekretariat.create')->name('store');
        Route::put('/{id}', [App\Http\Controllers\Sekretariat\AnnouncementController::class, 'update'])->middleware('can:sekretariat.edit')->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Sekretariat\AnnouncementController::class, 'destroy'])->middleware('can:sekretariat.delete')->name('destroy');
        Route::patch('/{id}/pin', [App\Http\Controllers\Sekretariat\AnnouncementController::class, 'togglePin'])->middleware('can:sekretariat.edit')->name('toggle-pin');
        Route::patch('/{id}/status', [App\Http\Controllers\Sekretariat\AnnouncementController::class, 'toggleStatus'])->middleware('can:sekretariat.edit')->name('toggle-status');
    });
});

// Staff routes
Route::prefix('staff')->middleware(['auth', 'role:staff'])->name('staff.')->group(function () {
    // Staff Dashboard
    Route::get('/', [App\Http\Controllers\Employee\DashboardController::class, 'index'])->name('dashboard');

    // Staff Profile routes (self-service)
    Route::get('/profile', [ProfileController::class, 'staffProfile'])->name('profile');

    // Staff Penggajian routes (view only)
    Route::prefix('penggajian')->name('penggajian.')->group(function () {
        Route::get('/', [App\Http\Controllers\Employee\PayrollController::class, 'index'])->name('index');
        Route::get('/{header}/download-pdf', [SlipGajiController::class, 'staffDownloadPdf'])->name('download-pdf');
    });

    // Staff Attendance routes (view only)
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [App\Http\Controllers\Employee\AttendanceController::class, 'index'])->name('index');
    });

    // Staff Pengumuman routes (view only)
    Route::prefix('pengumuman')->name('pengumuman.')->group(function () {
        Route::get('/', [App\Http\Controllers\Employee\AnnouncementController::class, 'index'])->name('index');
        Route::get('/{id}', [App\Http\Controllers\Employee\AnnouncementController::class, 'show'])->name('show');
        Route::post('/{id}/mark-as-read', [App\Http\Controllers\Employee\AnnouncementController::class, 'markAsRead'])->name('mark-as-read');
    });

    // Staff Slip Gaji routes (view only) - kept for backward compatibility
    Route::get('/slip-gaji', function () {
        return redirect()->route('staff.penggajian.index');
    })->name('slip-gaji.index');
    Route::get('/slip-gaji/{header}/download-pdf', [SlipGajiController::class, 'staffDownloadPdf'])->name('slip-gaji.download-pdf');
});

require __DIR__.'/auth.php';
