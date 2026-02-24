@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-gray-50 pb-24 lg:pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl shadow-lg shadow-blue-200 overflow-hidden">
            <div class="px-5 py-6 lg:px-6 lg:py-7">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 text-blue-100 text-xs font-semibold uppercase tracking-wide mb-2">
                            <a href="{{ route('staff.dashboard') }}" class="inline-flex items-center hover:text-white transition-colors">
                                <x-lucide-chevron-left class="w-4 h-4 mr-1" />
                                Dashboard
                            </a>
                            <span>/</span>
                            <span>Profil</span>
                        </div>
                        <h1 class="text-2xl lg:text-3xl font-bold text-white">Profil Saya</h1>
                        <p class="text-blue-100 mt-1">Informasi pribadi dan akun</p>
                    </div>
                    <a href="{{ route('staff.profile.edit') }}" class="inline-flex items-center px-3 py-2 bg-white/20 hover:bg-white/30 text-white text-sm font-semibold rounded-xl transition-colors">
                        <x-lucide-pencil class="w-4 h-4 mr-1.5" />
                        Edit
                    </a>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-white/20 text-white text-xs font-semibold">
                        <x-lucide-id-card class="w-3.5 h-3.5 mr-1.5" />
                        NIP: {{ $user->nip ?? '-' }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-white/20 text-white text-xs font-semibold">
                        <x-lucide-shield-check class="w-3.5 h-3.5 mr-1.5" />
                        Role: {{ ucfirst(str_replace('-', ' ', getActiveRole() ?? 'staff')) }}
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Profile Header Card -->
        <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl shadow-lg p-6 lg:p-8 text-white">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                <!-- Avatar -->
                <div class="w-24 h-24 lg:w-32 lg:h-32 rounded-2xl bg-white/20 backdrop-blur-sm border-2 border-white/30 flex items-center justify-center text-4xl font-bold">
                    {{ substr($user->name, 0, 1) }}
                </div>

                <div class="flex-1 text-center sm:text-left">
                    <h2 class="text-xl lg:text-2xl font-bold">{{ $user->name }}</h2>
                    <p class="text-blue-100 mt-1">{{ $employee?->status_kepegawaian ?? 'Staff' }}</p>
                    
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 mt-4">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white/20 rounded-full text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3 3 0 00-3 3m3-3a3 3 0 01-3 3"/>
                            </svg>
                            NIP: {{ $user->nip ?? '-' }}
                        </span>
                        
                        @if($employee?->satuan_kerja)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white/20 rounded-full text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                {{ $employee->satuan_kerja }}
                            </span>
                        @endif
                    </div>
                </div>

                <a href="{{ route('staff.profile.edit') }}" 
                   class="flex items-center gap-2 px-4 py-2 bg-white text-blue-600 rounded-xl font-medium hover:bg-blue-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    Edit Profil
                </a>
            </div>
        </div>

        <!-- Account Info -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Informasi Akun</h3>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Nama Pengguna</p>
                            <p class="font-medium text-gray-800">{{ $user->name }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="font-medium text-gray-800">{{ $user->email }}</p>
                        </div>
                    </div>
                    
                    @if($user->email_verified_at)
                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-100 text-emerald-600 rounded-lg text-xs font-medium">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Terverifikasi
                        </span>
                    @endif
                </div>

                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Role Aktif</p>
                            <p class="font-medium text-gray-800">{{ ucfirst(str_replace('-', ' ', getActiveRole() ?? 'Staff')) }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Fingerprint</p>
                            <p class="font-medium {{ $user->fingerprint_pin ? 'text-emerald-600' : 'text-gray-400' }}">
                                {{ $user->fingerprint_pin ? 'Terdaftar' : 'Belum Terdaftar' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($employee)
            <!-- Employee Info -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Informasi Kepegawaian</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-sm text-gray-500 mb-1">Satuan Kerja</p>
                        <p class="font-medium text-gray-800">{{ $employee->satuan_kerja ?? '-' }}</p>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-sm text-gray-500 mb-1">Status Kepegawaian</p>
                        <p class="font-medium text-gray-800">{{ $employee->status_kepegawaian ?? '-' }}</p>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-sm text-gray-500 mb-1">Jabatan Struktural</p>
                        <p class="font-medium text-gray-800">{{ $employee->jabatan_struktural ?? '-' }}</p>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-sm text-gray-500 mb-1">Jabatan Fungsional</p>
                        <p class="font-medium text-gray-800">{{ $employee->jabatan_fungsional ?? '-' }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Security Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Keamanan</h3>
            
            <a href="{{ route('password.request') }}" 
               class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-800">Ubah Password</p>
                        <p class="text-sm text-gray-500">Perbarui password akun Anda</p>
                    </div>
                </div>
                
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

    </div>
</div>
@endsection
