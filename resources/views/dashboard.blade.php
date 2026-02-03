@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('breadcrumb')
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                    </svg>
                    Dashboard
                </a>
            </li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="space-y-6 sm:space-y-8">
    @if(isActiveRole('staff|employee'))
        @php
            $user = auth()->user();
            $employee = $user->employeeData; // From User.php relationship/accessor
            $unitKerja = $employee?->satuan_kerja ?? $employee?->unit_kerja ?? 'Unit Kerja Belum Diatur';
            $announcements = \App\Models\Employee\Announcement::active()->latest()->take(5)->get();
            
            // Metrics
            $nip = $user->nip;
            $userSlipCount = \App\Models\SlipGajiDetail::where('nip', $nip)->count();
            $lastSlip = \App\Models\SlipGajiDetail::where('nip', $nip)->latest('created_at')->first();
            $lastSalary = $lastSlip ? $lastSlip->penerimaan_bersih : 0;
            $statusAktif = $employee->status_aktif ?? 'Aktif';
        @endphp

        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl shadow-lg p-5 sm:p-6 lg:p-8 text-white relative overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
                <div class="space-y-1">
                    <p class="text-blue-200 text-[10px] sm:text-xs font-medium uppercase tracking-wider">Selamat Datang Kembali</p>
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold leading-tight">
                        {{ $employee?->nama_lengkap_with_gelar ?? $user->name }}
                    </h1>
                    <div class="flex items-center text-blue-100/90">
                        <x-lucide-building-2 class="w-3.5 h-3.5 sm:w-5 sm:h-5 mr-1.5 sm:mr-2 flex-shrink-0" />
                        <span class="text-xs sm:text-base lg:text-lg font-medium truncate">{{ $unitKerja }}</span>
                    </div>
                </div>
                <div class="flex sm:block items-center justify-between border-t border-white/10 pt-3 sm:pt-0 sm:border-0 sm:text-right">
                    <div class="sm:mb-1">
                        <p class="text-blue-200 text-[9px] sm:text-xs font-medium uppercase tracking-wider">Hari Ini</p>
                        <p class="text-sm sm:text-lg lg:text-xl font-bold">{{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Subtle decoration - hidden on smallest mobile to save visual space -->
            <div class="absolute top-0 right-0 -mr-8 -mt-8 w-64 h-64 bg-white/10 rounded-full blur-3xl hidden sm:block"></div>
        </div>

        <!-- Attendance Monitoring -->
        <div class="py-0 sm:py-2">
            <livewire:staff.today-attendance-card />
        </div>

        <!-- Quick Action Grid (Staff Special) -->
        <div class="grid grid-cols-3 gap-3">
            <a href="{{ route('staff.penggajian.index') }}" class="flex flex-col items-center p-3 bg-blue-50/50 rounded-xl border border-blue-100 hover:bg-blue-50 transition-colors group">
                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center text-blue-600 shadow-sm mb-2 group-hover:scale-110 transition-transform">
                    <x-lucide-receipt class="w-5 h-5" />
                </div>
                <span class="text-[10px] sm:text-xs font-bold text-gray-700 text-center">Gaji</span>
            </a>
            <a href="{{ route('staff.attendance.index') }}" class="flex flex-col items-center p-3 bg-indigo-50/50 rounded-xl border border-indigo-100 hover:bg-indigo-50 transition-colors group">
                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center text-indigo-600 shadow-sm mb-2 group-hover:scale-110 transition-transform">
                    <x-lucide-calendar-check-2 class="w-5 h-5" />
                </div>
                <span class="text-[10px] sm:text-xs font-bold text-gray-700 text-center">Absensi</span>
            </a>
            <a href="{{ route('staff.profile') }}" class="flex flex-col items-center p-3 bg-emerald-50/50 rounded-xl border border-emerald-100 hover:bg-emerald-50 transition-colors group">
                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center text-emerald-600 shadow-sm mb-2 group-hover:scale-110 transition-transform">
                    <x-lucide-user-cog class="w-5 h-5" />
                </div>
                <span class="text-[10px] sm:text-xs font-bold text-gray-700 text-center">Profil</span>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4 sm:gap-6">
            <!-- Salary Info Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center">
                            <x-lucide-wallet class="w-5 h-5" />
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Ringkasan Payroll</h3>
                    </div>
                    <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-bold rounded-full uppercase tracking-tight">Terbaru</span>
                </div>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-medium text-gray-500 mb-0.5">Penerimaan Bersih</p>
                        <p class="text-xl font-black text-gray-900">Rp {{ number_format($lastSalary, 0, ',', '.') }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                        <div>
                            <p class="text-[10px] font-medium text-gray-500 mb-0.5">Periode Terakhir</p>
                            <p class="text-xs font-bold text-gray-900">{{ $lastSlip ? $lastSlip->created_at->locale('id')->isoFormat('MMMM YYYY') : '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-medium text-gray-500 mb-0.5">Total Dokumen</p>
                            <p class="text-xs font-bold text-gray-900">{{ $userSlipCount }} Slip Gaji</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employment Details Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center">
                            <x-lucide-shield-check class="w-5 h-5" />
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Data Kepegawaian</h3>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800 uppercase tracking-tight">
                        {{ $statusAktif }}
                    </span>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <div>
                            <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wider">Jabatan / Status</p>
                            <p class="text-sm font-bold text-gray-900">{{ $employee?->status_kepegawaian ?? 'Karyawan' }}</p>
                        </div>
                        <x-lucide-badge-check class="w-5 h-5 text-emerald-500" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[10px] font-medium text-gray-500 mb-0.5 uppercase tracking-wider">NIP</p>
                            <p class="text-xs font-black text-gray-900">{{ $nip }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-medium text-gray-500 mb-0.5 uppercase tracking-wider">Tipe</p>
                            <p class="text-xs font-black text-gray-900">{{ $employee?->jenis_pegawai ?? 'Staff' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Announcements Panel (Admin Style) -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="p-4 sm:p-5 lg:p-6 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-amber-50 rounded-lg flex items-center justify-center text-amber-600 flex-shrink-0">
                        <x-lucide-megaphone class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6" />
                    </div>
                    <div>
                        <h2 class="text-sm sm:text-lg font-black text-gray-900 uppercase tracking-tight">Pengumuman</h2>
                    </div>
                </div>
                <a href="{{ route('staff.pengumuman.index') }}" class="text-[10px] sm:text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center">
                    Semua <x-lucide-chevron-right class="w-3 h-3 ml-0.5" />
                </a>
            </div>
            <div class="p-3 sm:p-4 lg:p-6">
                <div class="space-y-3">
                    @forelse($announcements as $announcement)
                        <a href="{{ route('staff.pengumuman.show', $announcement->id) }}" class="flex items-start bg-gray-50/50 p-3 sm:p-4 rounded-xl border border-gray-200 transition-all duration-300 hover:bg-white hover:shadow-md hover:border-blue-100 group">
                            <div class="mr-3 hidden sm:block flex-shrink-0">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center
                                    {{ $announcement->priority === 'high' ? 'bg-amber-50 text-amber-600' : 'bg-blue-50 text-blue-600' }}">
                                    @if($announcement->priority === 'high')
                                        <x-lucide-alert-circle class="w-4 h-4" />
                                    @else
                                        <x-lucide-info class="w-4 h-4" />
                                    @endif
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                    <span class="text-[10px] sm:text-xs text-gray-500">
                                        {{ $announcement->published_at->diffForHumans() }}
                                    </span>
                                    @if($announcement->is_pinned)
                                        <span class="px-1.5 sm:px-2 py-0.5 bg-amber-100 text-amber-800 text-[9px] sm:text-[10px] font-bold rounded-full uppercase tracking-tight">PINNED</span>
                                    @endif
                                </div>
                                <h3 class="text-xs sm:text-base font-bold text-gray-900 group-hover:text-blue-600 transition-colors line-clamp-1 sm:line-clamp-2 mb-0.5 sm:mb-1">
                                    {{ $announcement->title }}
                                </h3>
                                <p class="text-[10px] sm:text-sm text-gray-600 line-clamp-1 opacity-80">
                                    {{ strip_tags($announcement->content) }}
                                </p>
                            </div>
                            <div class="ml-2 sm:ml-4 flex-shrink-0 self-center">
                                <x-lucide-chevron-right class="w-3.5 h-3.5 sm:w-5 sm:h-5 text-gray-400 group-hover:text-blue-500 transition-colors" />
                            </div>
                        </a>
                    @empty
                        <div class="flex flex-col items-center justify-center py-8 sm:py-10 lg:py-12 text-center bg-gray-50/50 rounded-xl border border-dashed border-gray-200">
                            <x-lucide-inbox class="w-10 h-10 sm:w-12 sm:h-12 text-gray-300 mb-2 sm:mb-3" />
                            <p class="text-xs sm:text-sm font-medium text-gray-500">Belum ada pengumuman</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    @else
        <!-- Admin/Superadmin Dashboard -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl shadow-lg p-8 text-white relative overflow-hidden">
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Selamat Datang, {{ auth()->user()->name }}!</h1>
                    <p class="text-blue-100 text-lg">Sistem Informasi Manajemen USBYPKP</p>
                    <p class="text-blue-200 text-sm mt-1">{{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
                </div>
                <div class="hidden md:block">
                    <svg class="w-24 h-24 text-blue-300" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        <path d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @if(isActiveRole('super-admin'))
            <!-- Total Users -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Pengguna</p>
                        <p class="text-3xl font-bold text-gray-900">{{ App\Models\User::count() }}</p>
                        <p class="text-xs text-gray-500 mt-1">Pengguna sistem</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center">
                        <x-lucide-users class="w-6 h-6" />
                    </div>
                </div>
            </div>
            @endif

            @if(isActiveRole('super-admin|admin-sdm'))
            <!-- Total Employees -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Karyawan</p>
                        <p class="text-3xl font-bold text-gray-900">{{ App\Models\Employee::count() }}</p>
                        <p class="text-xs text-gray-500 mt-1">Data karyawan</p>
                    </div>
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center">
                        <x-lucide-briefcase class="w-6 h-6" />
                    </div>
                </div>
            </div>

            <!-- Total Dosen -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Dosen</p>
                        <p class="text-3xl font-bold text-gray-900">{{ App\Models\Dosen::count() }}</p>
                        <p class="text-xs text-gray-500 mt-1">Data dosen</p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center">
                        <x-lucide-graduation-cap class="w-6 h-6" />
                    </div>
                </div>
            </div>
            @endif

            @if(isActiveRole('super-admin'))
            <!-- Attendance Logs Today -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Absensi Hari Ini</p>
                        <p class="text-3xl font-bold text-gray-900">{{ App\Models\AttendanceLog::whereDate('datetime', today())->count() }}</p>
                        <p class="text-xs text-gray-500 mt-1">Log absensi</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center">
                        <x-lucide-fingerprint class="w-6 h-6" />
                    </div>
                </div>
            </div>
            @endif
        </div>
        
        <!-- Quick Access Modules -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Akses Cepat Modul</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @if(isActiveRole('super-admin'))
                <a href="{{ route('superadmin.users.index') }}" class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors group">
                    <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mr-3">
                        <x-lucide-users class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 group-hover:text-blue-600">Manajemen Pengguna</p>
                        <p class="text-xs text-gray-500">Kelola pengguna sistem</p>
                    </div>
                </a>

                <a href="{{ route('superadmin.roles.index') }}" class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors group">
                    <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center mr-3">
                        <x-lucide-shield-check class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 group-hover:text-purple-600">Manajemen Peran</p>
                        <p class="text-xs text-gray-500">Kelola role & permissions</p>
                    </div>
                </a>

                <a href="{{ route('superadmin.activity-logs.index') }}" class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors group">
                    <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center mr-3">
                        <x-lucide-history class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 group-hover:text-amber-600">Log Aktivitas</p>
                        <p class="text-xs text-gray-500">Riwayat aktivitas sistem</p>
                    </div>
                </a>

                <a href="{{ route('superadmin.fingerprint.attendance-logs.index') }}" class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors group">
                    <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center mr-3">
                        <x-lucide-fingerprint class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 group-hover:text-indigo-600">Data Absensi</p>
                        <p class="text-xs text-gray-500">Log mesin fingerprint</p>
                    </div>
                </a>
                @endif
                
                @if(isActiveRole('super-admin|admin-sdm'))
                <a href="{{ route('sdm.employees.index') }}" class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors group">
                    <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center mr-3">
                        <x-lucide-briefcase class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 group-hover:text-emerald-600">Data Karyawan</p>
                        <p class="text-xs text-gray-500">Kelola data karyawan</p>
                    </div>
                </a>

                <a href="{{ route('sdm.dosens.index') }}" class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors group">
                    <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mr-3">
                        <x-lucide-graduation-cap class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 group-hover:text-blue-600">Data Dosen</p>
                        <p class="text-xs text-gray-500">Kelola data dosen</p>
                    </div>
                </a>

                <a href="{{ route('sdm.slip-gaji.index') }}" class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors group">
                    <div class="w-10 h-10 bg-yellow-100 text-yellow-600 rounded-lg flex items-center justify-center mr-3">
                        <x-lucide-receipt class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 group-hover:text-yellow-600">Slip Gaji</p>
                        <p class="text-xs text-gray-500">Kelola slip gaji karyawan</p>
                    </div>
                </a>

                <a href="{{ route('sdm.absensi.management') }}" class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors group">
                    <div class="w-10 h-10 bg-rose-100 text-rose-600 rounded-lg flex items-center justify-center mr-3">
                        <x-lucide-calendar-check class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 group-hover:text-rose-600">Manajemen Absensi</p>
                        <p class="text-xs text-gray-500">Kelola absensi karyawan</p>
                    </div>
                </a>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
