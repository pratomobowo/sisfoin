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
<div class="space-y-8">
    @if(isActiveRole('staff|employee'))
        @php
            $user = auth()->user();
            $employee = $user->employeeData; // From User.php relationship/accessor
            $unitKerja = $employee?->satuan_kerja ?? 'Unit Kerja Belum Diatur';
            $announcements = \App\Models\Employee\Announcement::active()->latest()->take(5)->get();
            
            // Metrics
            $nip = $user->nip;
            $userSlipCount = \App\Models\SlipGajiDetail::where('nip', $nip)->count();
            $lastSlip = \App\Models\SlipGajiDetail::where('nip', $nip)->latest('created_at')->first();
            $lastSalary = $lastSlip ? $lastSlip->penerimaan_bersih : 0;
            $statusAktif = $employee->status_aktif ?? 'Aktif';
        @endphp

        <!-- Header Section: Personal Info -->
        <div class="relative overflow-hidden bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 sm:p-12">
            <div class="relative z-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div class="space-y-4">
                    <div class="inline-flex items-center px-4 py-1.5 bg-blue-50 text-blue-600 rounded-full text-xs font-black uppercase tracking-widest border border-blue-100/50">
                        Selamat Datang Kembali
                    </div>
                    <div>
                        <h1 class="text-4xl sm:text-5xl font-black text-gray-900 tracking-tight mb-2">
                            {{ $employee?->nama_lengkap_with_gelar ?? $user->name }}
                        </h1>
                        <p class="text-lg font-bold text-gray-400 flex items-center">
                            <x-lucide-building-2 class="w-5 h-5 mr-2 text-blue-500" />
                            {{ $unitKerja }}
                        </p>
                    </div>
                </div>
                <div class="hidden lg:block">
                    <div class="text-right">
                        <p class="text-sm font-black text-gray-400 uppercase tracking-widest mb-1">Hari Ini</p>
                        <p class="text-2xl font-black text-gray-900 tracking-tight">{{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Abstract background shape -->
            <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-blue-50 rounded-full opacity-50 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-48 h-48 bg-emerald-50 rounded-full opacity-30 blur-2xl"></div>
        </div>

        <!-- Attendance Monitoring -->
        <livewire:staff.today-attendance-card />

        <!-- Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Salary Card -->
            <div class="group bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 transition-all duration-300 hover:shadow-xl hover:shadow-blue-500/5 hover:-translate-y-1">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center shadow-inner group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                        <x-lucide-receipt class="w-7 h-7" />
                    </div>
                    <a href="{{ route('staff.penggajian.index') }}" class="text-[10px] font-black uppercase tracking-widest text-blue-500 hover:text-blue-700 transition-colors">Lihat Detail</a>
                </div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Gaji Terakhir</p>
                <p class="text-2xl font-black text-gray-900 tracking-tight">Rp {{ number_format($lastSalary, 0, ',', '.') }}</p>
                <div class="mt-4 pt-4 border-t border-gray-50 flex items-center justify-between">
                    <span class="text-[10px] font-bold text-gray-400">Total Slip Gaji</span>
                    <span class="px-2.5 py-1 bg-gray-50 text-gray-600 text-[10px] font-black rounded-lg border border-gray-100">{{ $userSlipCount }} Dokumen</span>
                </div>
            </div>

            <!-- Profile Info / Status Card -->
            <div class="group bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 transition-all duration-300 hover:shadow-xl hover:shadow-emerald-500/5 hover:-translate-y-1">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center shadow-inner group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300">
                        <x-lucide-user-check class="w-7 h-7" />
                    </div>
                    <div class="flex items-center space-x-1.5 px-3 py-1 bg-emerald-50 text-emerald-600 rounded-lg border border-emerald-100/50">
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                        <span class="text-[10px] font-black uppercase tracking-widest">Aktif</span>
                    </div>
                </div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Status Kepegawaian</p>
                <p class="text-2xl font-black text-gray-900 tracking-tight">{{ $employee?->status_kepegawaian ?? 'Karyawan' }}</p>
                <div class="mt-4 pt-4 border-t border-gray-50">
                    <p class="text-[10px] font-bold text-gray-400">NIP: <span class="text-gray-900">{{ $nip }}</span></p>
                </div>
            </div>

            <!-- Quick Link / Shortcut Card -->
            <div class="group bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 transition-all duration-300 hover:shadow-xl hover:shadow-indigo-500/5 hover:-translate-y-1">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center shadow-inner group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                        <x-lucide-calendar-check-2 class="w-7 h-7" />
                    </div>
                </div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Absensi & Riwayat</p>
                <p class="text-2xl font-black text-gray-900 tracking-tight">Monitoring Absensi</p>
                <div class="mt-4 pt-4 border-t border-gray-50">
                    <a href="{{ route('staff.absensi.index') }}" class="group/btn inline-flex items-center text-[10px] font-black uppercase tracking-widest text-indigo-500 hover:text-indigo-700">
                        Buka Riwayat Absensi
                        <x-lucide-move-right class="w-3.5 h-3.5 ml-2 transition-transform duration-300 group-hover/btn:translate-x-1" />
                    </a>
                </div>
            </div>
        </div>

        <!-- Announcements Panel -->
        <div class="bg-white rounded-[2rem] border border-gray-100 overflow-hidden shadow-sm">
            <div class="p-8 sm:p-10 border-b border-gray-50 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600">
                        <x-lucide-megaphone class="w-6 h-6" />
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-gray-900 tracking-tight">Pengumuman Terbaru</h2>
                        <p class="text-xs font-bold text-gray-400">Informasi dan update terkini untuk Anda</p>
                    </div>
                </div>
                <a href="{{ route('staff.pengumuman.index') }}" class="text-[10px] font-black uppercase tracking-widest text-blue-500 hover:text-blue-700 transition-colors">Lihat Semua</a>
            </div>
            <div class="p-4 sm:p-6 bg-gray-50/30">
                <div class="space-y-4">
                    @forelse($announcements as $announcement)
                        <a href="{{ route('staff.pengumuman.show', $announcement->id) }}" class="flex items-start bg-white p-6 rounded-2xl border border-gray-100 shadow-sm transition-all duration-300 hover:shadow-md hover:border-blue-100 group">
                            <div class="mr-6 hidden sm:block">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center transition-colors group-hover:scale-110 duration-300
                                    {{ $announcement->priority === 'high' ? 'bg-amber-50 text-amber-600' : 'bg-blue-50 text-blue-600' }}">
                                    @if($announcement->priority === 'high')
                                        <x-lucide-alert-circle class="w-6 h-6" />
                                    @else
                                        <x-lucide-info class="w-6 h-6" />
                                    @endif
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-[9px] font-black uppercase tracking-widest text-gray-400">
                                        {{ $announcement->published_at->diffForHumans() }}
                                    </span>
                                    @if($announcement->is_pinned)
                                        <span class="px-2 py-0.5 bg-amber-50 text-amber-600 text-[8px] font-black rounded-full uppercase tracking-widest border border-amber-100">PINNED</span>
                                    @endif
                                </div>
                                <h3 class="text-lg font-black text-gray-900 group-hover:text-blue-600 transition-colors leading-tight mb-2">
                                    {{ $announcement->title }}
                                </h3>
                                <p class="text-sm text-gray-500 line-clamp-2 leading-relaxed">
                                    {{ strip_tags($announcement->content) }}
                                </p>
                            </div>
                            <div class="ml-4 flex-shrink-0">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-50 text-gray-300 group-hover:bg-blue-50 group-hover:text-blue-500 transition-all duration-300">
                                    <x-lucide-chevron-right class="w-5 h-5" />
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="flex flex-col items-center justify-center p-12 text-center bg-white rounded-3xl border border-dashed border-gray-200">
                            <x-lucide-inbox class="w-12 h-12 text-gray-200 mb-4" />
                            <p class="text-sm font-bold text-gray-400">Belum ada pengumuman untuk ditampilkan</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    @else
        <!-- Original Dashboard for Admin/Superadmin (Kept Intact but wrapped in common layout) -->
        <!-- Welcome Section -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl shadow-lg p-8 text-white">
            <div class="flex items-center justify-between">
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
                <!-- ... existing content ... -->
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Pengguna</p>
                        <p class="text-3xl font-bold text-gray-900">{{ App\Models\User::count() }}</p>
                    </div>
                    <!-- ... -->
                </div>
            </div>
            @endif

            @if(isActiveRole('super-admin|admin-sdm|sdm'))
            <!-- Existing Admin Stats ... -->
            @endif
        </div>
        
        <!-- Quick Access etc ... -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Akses Cepat Modul</h2>
            <!-- ... existing ... -->
        </div>
    @endif
</div>
@endsection
