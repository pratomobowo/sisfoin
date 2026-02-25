@extends('layouts.app')

@section('page-title', 'Dashboard')

<x-breadcrumb-section :breadcrumbs="['Dashboard' => null]" />

@section('content')
<div class="space-y-6 sm:space-y-8">
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
</div>
@endsection
