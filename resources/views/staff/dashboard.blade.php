@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-gray-50 pb-24 lg:pb-0" x-data="staffDashboard()">
    
    <!-- Hero Section - Clean Blue Design -->
    <div class="relative bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-700">
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
            <div class="flex items-start justify-between">
                <div class="space-y-2">
                    <p class="text-blue-100 text-sm font-medium tracking-wide uppercase">
                        {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                    </p>
                    <h1 class="text-2xl lg:text-4xl font-bold text-white">
                        Selamat Datang,
                        <span class="block mt-1">{{ auth()->user()->name }}!</span>
                    </h1>
                    <p class="text-blue-100 text-sm lg:text-base max-w-md">
                        Semoga harimu produktif dan penuh berkah.
                    </p>
                </div>
                
                <!-- Profile Avatar -->
                <div class="relative">
                    <div class="w-14 h-14 lg:w-20 lg:h-20 rounded-2xl bg-white/20 backdrop-blur-sm border-2 border-white/30 flex items-center justify-center overflow-hidden shadow-xl">
                        @if(auth()->user()->avatar)
                            <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-2xl lg:text-3xl font-bold text-white">{{ substr(auth()->user()->name, 0, 1) }}</span>
                        @endif
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-400 rounded-full border-2 border-white flex items-center justify-center">
                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Attendance Status -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 lg:-mt-8 relative z-10 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 lg:p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Kehadiran Hari Ini</h3>
                        <p class="text-sm text-gray-500">{{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
                    </div>
                </div>
            </div>

            @if($todayAttendance)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Check In -->
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-600">Jam Masuk</span>
                        </div>
                        <p class="text-xl font-bold text-gray-800">
                            {{ $todayAttendance->check_in_time ? $todayAttendance->check_in_time->format('H:i') : '-' }}
                        </p>
                        @if($todayAttendance->check_in_time)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium mt-1
                                @if($todayAttendance->status == 'on_time' || $todayAttendance->status == 'present') bg-green-100 text-green-700
                                @elseif($todayAttendance->status == 'late') bg-yellow-100 text-yellow-700
                                @else bg-gray-100 text-gray-600 @endif">
                                @if($todayAttendance->status == 'on_time' || $todayAttendance->status == 'present')
                                    Tepat Waktu
                                @elseif($todayAttendance->status == 'late')
                                    Terlambat
                                @else
                                    {{ ucfirst($todayAttendance->status) }}
                                @endif
                            </span>
                        @endif
                    </div>

                    <!-- Check Out -->
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-600">Jam Pulang</span>
                        </div>
                        <p class="text-xl font-bold text-gray-800">
                            {{ $todayAttendance->check_out_time ? $todayAttendance->check_out_time->format('H:i') : '-' }}
                        </p>
                        @if($todayAttendance->check_out_time)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium mt-1 bg-green-100 text-green-700">
                                Sudah Pulang
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium mt-1 bg-gray-100 text-gray-600">
                                Belum Pulang
                            </span>
                        @endif
                    </div>

                    <!-- Status -->
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-600">Status</span>
                        </div>
                        <p class="text-xl font-bold
                            @if($todayAttendance->status == 'present' || $todayAttendance->status == 'on_time') text-green-600
                            @elseif($todayAttendance->status == 'late') text-yellow-600
                            @elseif($todayAttendance->status == 'absent') text-red-600
                            @else text-gray-600 @endif">
                            @if($todayAttendance->status == 'present' || $todayAttendance->status == 'on_time')
                                Hadir
                            @elseif($todayAttendance->status == 'late')
                                Terlambat
                            @elseif($todayAttendance->status == 'absent')
                                Absen
                            @elseif($todayAttendance->status == 'permission')
                                Izin
                            @else
                                {{ ucfirst($todayAttendance->status) }}
                            @endif
                        </p>
                        <span class="text-xs text-gray-500 mt-1 block">Status Kehadiran</span>
                    </div>
                </div>
            @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
                    <div class="w-12 h-12 mx-auto mb-3 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-gray-800 font-medium">Belum Ada Data Kehadiran</p>
                    <p class="text-sm text-gray-500 mt-1">Anda belum melakukan absensi hari ini.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Employee Information -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 lg:p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-800">Informasi Karyawan</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Status -->
                <div class="bg-gray-50 rounded-xl p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-600">Status</span>
                    </div>
                    <p class="text-base font-semibold text-gray-800">{{ $employeeInfo['status_kepegawaian'] }}</p>
                </div>

                <!-- Unit Kerja -->
                <div class="bg-gray-50 rounded-xl p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-600">Unit Kerja</span>
                    </div>
                    <p class="text-base font-semibold text-gray-800">{{ $employeeInfo['unit_kerja'] }}</p>
                </div>

                <!-- NIP -->
                <div class="bg-gray-50 rounded-xl p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3 3 0 00-3 3m3-3a3 3 0 01-3 3"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-600">NIP</span>
                    </div>
                    <p class="text-base font-semibold text-gray-800">{{ $employeeInfo['nip'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
            <!-- Attendance Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 lg:p-5 hover:shadow-md transition-all duration-300 group cursor-pointer"
                 @click="window.location.href='{{ route('staff.attendance.index') }}'">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-5 h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full">Bulan Ini</span>
                </div>
                <p class="text-2xl lg:text-3xl font-bold text-gray-800">{{ $quickStats['attendance_days_this_month'] ?? 0 }}</p>
                <p class="text-xs lg:text-sm text-gray-500 mt-1">Hari Hadir</p>
            </div>

            <!-- Salary Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 lg:p-5 hover:shadow-md transition-all duration-300 group cursor-pointer"
                 @click="window.location.href='{{ route('staff.penggajian.index') }}'">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-5 h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-indigo-600 bg-indigo-50 px-2 py-1 rounded-full">Gaji Terakhir</span>
                </div>
                <p class="text-xl lg:text-2xl font-bold text-gray-800 truncate">
                    @if(!is_null($quickStats['latest_net_salary'] ?? null))
                        Rp {{ number_format($quickStats['latest_net_salary'], 0, ',', '.') }}
                    @else
                        <span class="text-gray-400 text-sm">{{ $quickStats['salary_error_message'] ?? '-' }}</span>
                    @endif
                </p>
                @if(!is_null($quickStats['latest_net_salary'] ?? null) && !empty($quickStats['latest_salary_period']))
                    <p class="text-xs lg:text-sm text-gray-500 mt-1">
                        {{ \Carbon\Carbon::parse($quickStats['latest_salary_period'] . '-01')->locale('id')->isoFormat('MMMM YYYY') }}
                    </p>
                @endif
            </div>

            <!-- Announcements Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 lg:p-5 hover:shadow-md transition-all duration-300 group cursor-pointer"
                 @click="window.location.href='{{ route('staff.pengumuman.index') }}'">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-5 h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                    </div>
                    @if(($quickStats['unread_announcements'] ?? 0) > 0)
                        <span class="text-xs font-medium text-white bg-red-500 px-2 py-1 rounded-full animate-pulse">{{ $quickStats['unread_announcements'] }} baru</span>
                    @endif
                </div>
                <p class="text-2xl lg:text-3xl font-bold text-gray-800">{{ $quickStats['unread_announcements'] ?? 0 }}</p>
                <p class="text-xs lg:text-sm text-gray-500 mt-1">Pengumuman</p>
            </div>

            <!-- Profile Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 lg:p-5 hover:shadow-md transition-all duration-300 group cursor-pointer"
                 @click="window.location.href='{{ route('staff.profile') }}'">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-5 h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full">Lihat</span>
                </div>
                <p class="text-lg lg:text-xl font-bold text-gray-800 truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs lg:text-sm text-gray-500 mt-1">Profil Saya</p>
            </div>
        </div>
    </div>

    <!-- Main Menu Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Menu Cepat</h2>
        
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
            <!-- Absensi Menu -->
            <a href="{{ route('staff.attendance.index') }}" 
               class="group relative bg-white rounded-2xl p-5 lg:p-6 border border-gray-100 shadow-sm hover:shadow-lg hover:border-blue-200 transition-all duration-300 overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-full -mr-12 -mt-12 opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative">
                    <div class="w-12 h-12 lg:w-14 lg:h-14 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center mb-4 shadow-lg group-hover:shadow-xl group-hover:scale-105 transition-all duration-300">
                        <svg class="w-6 h-6 lg:w-7 lg:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-1">Absensi</h3>
                    <p class="text-xs text-gray-500 line-clamp-2">Lihat riwayat kehadiran dan status absensi bulanan</p>
                </div>
            </a>

            <!-- Penggajian Menu -->
            <a href="{{ route('staff.penggajian.index') }}" 
               class="group relative bg-white rounded-2xl p-5 lg:p-6 border border-gray-100 shadow-sm hover:shadow-lg hover:border-indigo-200 transition-all duration-300 overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-full -mr-12 -mt-12 opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative">
                    <div class="w-12 h-12 lg:w-14 lg:h-14 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 text-white flex items-center justify-center mb-4 shadow-lg group-hover:shadow-xl group-hover:scale-105 transition-all duration-300">
                        <svg class="w-6 h-6 lg:w-7 lg:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-1">Penggajian</h3>
                    <p class="text-xs text-gray-500 line-clamp-2">Unduh slip gaji dan lihat ringkasan penghasilan</p>
                </div>
            </a>

            <!-- Pengumuman Menu -->
            <a href="{{ route('staff.pengumuman.index') }}" 
               class="group relative bg-white rounded-2xl p-5 lg:p-6 border border-gray-100 shadow-sm hover:shadow-lg hover:border-sky-200 transition-all duration-300 overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-sky-50 rounded-full -mr-12 -mt-12 opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative">
                    <div class="w-12 h-12 lg:w-14 lg:h-14 rounded-xl bg-gradient-to-br from-sky-500 to-sky-600 text-white flex items-center justify-center mb-4 shadow-lg group-hover:shadow-xl group-hover:scale-105 transition-all duration-300">
                        <svg class="w-6 h-6 lg:w-7 lg:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-1">Pengumuman</h3>
                    <p class="text-xs text-gray-500 line-clamp-2">Baca informasi terbaru dan pengumuman penting</p>
                </div>
            </a>

            <!-- Profil Menu -->
            <a href="{{ route('staff.profile') }}" 
               class="group relative bg-white rounded-2xl p-5 lg:p-6 border border-gray-100 shadow-sm hover:shadow-lg hover:border-blue-200 transition-all duration-300 overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-full -mr-12 -mt-12 opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative">
                    <div class="w-12 h-12 lg:w-14 lg:h-14 rounded-xl bg-gradient-to-br from-blue-600 to-blue-700 text-white flex items-center justify-center mb-4 shadow-lg group-hover:shadow-xl group-hover:scale-105 transition-all duration-300">
                        <svg class="w-6 h-6 lg:w-7 lg:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-1">Profil</h3>
                    <p class="text-xs text-gray-500 line-clamp-2">Kelola data diri dan informasi pribadi</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Announcements Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-800">Pengumuman Terbaru</h2>
            <a href="{{ route('staff.pengumuman.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700 flex items-center gap-1">
                Lihat Semua
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <div class="space-y-3">
            @forelse($announcements->take(3) as $announcement)
                <div class="bg-white rounded-xl p-4 lg:p-5 border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300 {{ !$announcement['is_read'] ? 'border-l-4 border-l-blue-500' : '' }}">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg {{ $announcement['type_color'] ?? 'bg-blue-100 text-blue-600' }} flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-medium text-gray-500">{{ $announcement['type'] ?? 'Umum' }}</span>
                                @if($announcement['is_pinned'])
                                    <span class="text-xs font-medium text-amber-600 bg-amber-50 px-2 py-0.5 rounded">Pinned</span>
                                @endif
                                @if(!$announcement['is_read'])
                                    <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                @endif
                            </div>
                            <h3 class="font-semibold text-gray-800 text-sm lg:text-base line-clamp-1">{{ $announcement['title'] }}</h3>
                            <p class="text-xs text-gray-500 mt-1">{{ \Carbon\Carbon::parse($announcement['created_at'])->diffForHumans() }}</p>
                        </div>
                        <a href="{{ route('staff.pengumuman.show', $announcement['id']) }}" class="flex-shrink-0 p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="bg-gray-100 rounded-xl p-8 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-200 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                    </div>
                    <p class="text-gray-600 font-medium">Tidak ada pengumuman</p>
                    <p class="text-sm text-gray-500 mt-1">Belum ada pengumuman terbaru saat ini</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
function staffDashboard() {
    return {
        init() {
            // Initialize any Alpine.js reactive data here
        }
    }
}
</script>
@endpush
@endsection