<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SDM\DosenController;
use App\Http\Controllers\SDM\EmployeeController;
use App\Http\Controllers\SDM\SlipGajiController;
use App\Http\Controllers\Superadmin\ActivityLogController;
use App\Http\Controllers\Superadmin\RoleController as SuperadminRoleController;
use App\Http\Controllers\Superadmin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
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
Route::prefix('sdm')->middleware(['auth', 'role:super-admin|admin-sdm'])->name('sdm.')->group(function () {
    // SDM Dashboard
    Route::get('/', function () {
        return view('sdm.dashboard');
    })->name('dashboard');

    // Employee management routes
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::get('/employees/{id}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('/employees/{id}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');

    // Dosen management routes
    Route::get('/dosens', [DosenController::class, 'index'])->name('dosens.index');
    Route::get('/dosens/create', [DosenController::class, 'create'])->name('dosens.create');
    Route::get('/dosens/{id}', [DosenController::class, 'show'])->name('dosens.show');
    Route::get('/dosens/{id}/edit', [DosenController::class, 'edit'])->name('dosens.edit');

    // Slip Gaji routes
    Route::prefix('slip-gaji')->name('slip-gaji.')->group(function () {
        Route::get('/', [SlipGajiController::class, 'index'])->name('index');
        Route::get('/create', [SlipGajiController::class, 'create'])->name('create');
        Route::get('/upload', [SlipGajiController::class, 'upload'])->name('upload');
        Route::post('/upload', [SlipGajiController::class, 'processUpload'])->name('upload.process');
        Route::get('/download-template', [SlipGajiController::class, 'downloadTemplate'])->name('download-template');

        // Preview and confirm routes removed - direct import implemented
        Route::delete('/{header}/cancel', [SlipGajiController::class, 'cancel'])->name('cancel');

        Route::get('/{header}', [SlipGajiController::class, 'show'])->name('show');
        Route::get('/{detail}/edit', [SlipGajiController::class, 'edit'])->name('edit');
        Route::put('/{detail}', [SlipGajiController::class, 'update'])->name('update');
        Route::get('/{detail}/preview-pdf-slip', [SlipGajiController::class, 'previewPdfSlip'])->name('preview-pdf-slip');
        Route::get('/{detail}/download-pdf-slip', [SlipGajiController::class, 'downloadPdfSlip'])->name('download-pdf-slip');
        Route::get('/{header}/download-bulk-pdf', [SlipGajiController::class, 'downloadBulkPdf'])->name('download-bulk-pdf');
        Route::delete('/{header}', [SlipGajiController::class, 'destroy'])->name('destroy');
        
        // Bulk email routes
        Route::post('/{header}/send-bulk-email', [SlipGajiController::class, 'sendBulkEmail'])->name('send-bulk-email');
        Route::get('/{header}/email-logs', [SlipGajiController::class, 'showEmailLogs'])->name('email-logs');
        Route::post('/{header}/retry-failed-emails', [SlipGajiController::class, 'retryFailedEmails'])->name('retry-failed-emails');
    });
    
    // Employee Attendance routes
    Route::prefix('employee-attendance')->name('employee-attendance.')->group(function () {
        Route::get('/', function () {
            return view('sdm.employee-attendance.index');
        })->name('index');
    });

    // Absensi Routes (New)
    Route::get('/absensi', App\Livewire\Sdm\EmployeeAttendanceManagement::class)->name('absensi.management');
    Route::get('/absensi/recap', App\Livewire\Sdm\AttendanceRecap::class)->name('absensi.recap');
    Route::get('/absensi/logs', App\Livewire\Superadmin\AttendanceLogs::class)->name('absensi.logs');
    Route::get('/absensi/settings', App\Livewire\Sdm\AttendanceSettingsManager::class)->name('absensi.settings');
    Route::get('/absensi/holidays', App\Livewire\Sdm\HolidayManager::class)->name('absensi.holidays');
    Route::get('/absensi/shifts', App\Livewire\Sdm\WorkShiftManager::class)->name('absensi.shifts');
    Route::get('/absensi/kelola-shift', App\Livewire\Sdm\UnitShiftManagement::class)->name('absensi.kelola-shift');
    Route::get('/absensi/kelola-shift/{unit}', App\Livewire\Sdm\UnitShiftDetail::class)->name('absensi.unit-detail');
    
    // Fingerprint management routes under sdm
    Route::prefix('fingerprint')->name('fingerprint.')->group(function () {
        Route::get('/', function () {
            return view('superadmin.fingerprint.attendance-logs');
        })->name('index');
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
    })->name('surat-keputusan.index');
    
    // Kegiatan Pejabat routes
    Route::get('/kegiatan-pejabat', function () {
        return view('sekretariat.kegiatan-pejabat.index');
    })->middleware(['role:sekretariat|admin-sekretariat|super-admin'])->name('kegiatan-pejabat.index');
});

// Staff routes
Route::prefix('staff')->middleware(['auth', 'role:staff'])->name('staff.')->group(function () {
    // Staff Dashboard -> redirect to unified dashboard
    Route::get('/', function () {
        return redirect()->route('dashboard');
    })->name('dashboard');

    // Staff Profile routes (self-service)
    Route::get('/profile', [ProfileController::class, 'staffProfile'])->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'staffEdit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'staffUpdate'])->name('profile.update');

    // Staff Penggajian routes (view only) - hanya download PDF saja
    Route::prefix('penggajian')->name('penggajian.')->group(function () {
        Route::get('/', function () {
            return view('staff.penggajian.index');
        })->name('index');
        Route::get('/{header}/download-pdf', [SlipGajiController::class, 'staffDownloadPdf'])->name('download-pdf');
    });

    // Staff Absensi routes (view only)
    Route::prefix('absensi')->name('absensi.')->group(function () {
        Route::get('/', function () {
            return view('staff.absensi.index');
        })->name('index');
    });

    // Staff Pengumuman routes (view only)
    Route::prefix('pengumuman')->name('pengumuman.')->group(function () {
        Route::get('/', function () {
            return view('staff.pengumuman.index');
        })->name('index');
        Route::get('/{id}', function ($id) {
            return view('staff.pengumuman.show', compact('id'));
        })->name('show');
    });

    // Staff Slip Gaji routes (view only) - kept for backward compatibility
    Route::get('/slip-gaji', function () {
        return redirect()->route('staff.penggajian.index');
    })->name('slip-gaji.index');
    Route::get('/slip-gaji/{header}/download-pdf', [SlipGajiController::class, 'staffDownloadPdf'])->name('slip-gaji.download-pdf');
});

require __DIR__.'/auth.php';
