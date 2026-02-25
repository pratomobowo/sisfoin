@extends('layouts.app')

@section('page-title', 'Detail Data Absensi')

<x-breadcrumb-section :breadcrumbs="[
    'Dashboard' => route('dashboard'),
    'Manajemen Fingerprint' => null,
    'Data Absensi' => route('superadmin.fingerprint.attendance-logs.index'),
    'Detail' => null,
]" />

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Detail Data Absensi</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Detail data absensi untuk PIN {{ $attendanceLog->pin }}
                </p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-3">
                    <a href="{{ route('superadmin.fingerprint.attendance-logs.index') }}" 
                   class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Attendance Info -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Absensi</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">PIN</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $attendanceLog->pin ?? '-' }}</p>
                    </div>
                </div>
            </div>
            
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Waktu Absensi</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $attendanceLog->datetime ? $attendanceLog->datetime->format('d/m/Y H:i:s') : '-' }}</p>
                    </div>
                </div>
            </div>
            
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Status</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $attendanceLog->status ?? '-' }}</p>
                    </div>
                </div>
            </div>
            
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Verifikasi</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $attendanceLog->verify ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mesin Info -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Mesin</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Nama Mesin</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $attendanceLog->mesinFinger->nama_mesin ?? '-' }}</p>
                    </div>
                </div>
            </div>
            
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Alamat IP</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $attendanceLog->mesinFinger->ip_address ?? '-' }}:{{ $attendanceLog->mesinFinger->port ?? '-' }}</p>
                    </div>
                </div>
            </div>
            
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-10 h-10 
                        @if($attendanceLog->mesinFinger->status === 'active') bg-green-100 text-green-600
                        @elseif($attendanceLog->mesinFinger->status === 'inactive') bg-gray-100 text-gray-600
                        @elseif($attendanceLog->mesinFinger->status === 'error') bg-red-100 text-red-600
                        @else bg-yellow-100 text-yellow-600
                        @endif rounded-lg flex items-center justify-center">
                        @if($attendanceLog->mesinFinger->status === 'active')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        @elseif($attendanceLog->mesinFinger->status === 'inactive')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        @elseif($attendanceLog->mesinFinger->status === 'error')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        @endif
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Status</p>
                        <p class="text-lg font-semibold 
                            @if($attendanceLog->mesinFinger->status === 'active') text-green-600
                            @elseif($attendanceLog->mesinFinger->status === 'inactive') text-gray-600
                            @elseif($attendanceLog->mesinFinger->status === 'error') text-red-600
                            @else text-yellow-600
                            @endif">
                            {{ $attendanceLog->mesinFinger->status_label ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Raw Data -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Data Mentah</h3>
        <div class="bg-gray-50 rounded-lg p-4">
            <pre class="text-sm text-gray-700 overflow-x-auto">{{ json_encode($attendanceLog->raw_data, JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
</div>
@endsection
