@extends('layouts.app')

@section('page-title', 'Profil Saya')

@section('breadcrumb')
    <nav class="flex overflow-x-auto pb-1 invisible-scrollbar" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2 whitespace-nowrap">
            <li class="inline-flex items-center">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors">
                    <x-lucide-home class="w-4 h-4 sm:mr-2" />
                    <span class="hidden sm:inline">Dashboard</span>
                </a>
                <x-lucide-chevron-right class="w-4 h-4 text-gray-400 mx-1 sm:mx-2" />
            </li>
            <li>
                <span class="text-sm font-semibold text-gray-900">
                    Profil
                </span>
            </li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl shadow-lg p-8 text-white relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
            <div class="flex items-center space-x-6">
                <div class="h-20 w-20 rounded-2xl bg-white/20 backdrop-blur-sm border border-white/30 flex items-center justify-center text-3xl font-bold">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div class="space-y-1">
                    <h1 class="text-3xl font-bold">{{ $employee?->nama_lengkap_with_gelar ?? $user->name }}</h1>
                    <p class="text-blue-100 flex items-center">
                        <x-lucide-badge-check class="w-4 h-4 mr-2" />
                        {{ $employee?->status_kepegawaian ?? 'Pegawai' }} • NIP: {{ $user->nip }}
                    </p>
                </div>
            </div>
            <div class="flex flex-shrink-0">
                <a href="{{ route('staff.profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/30 rounded-lg text-sm font-semibold transition-all">
                    <x-lucide-pencil class="w-4 h-4 mr-2" />
                    Ubah Profil
                </a>
            </div>
        </div>
        <div class="absolute top-0 right-0 -mr-8 -mt-8 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sidebar Info -->
        <div class="space-y-6">
            <!-- Account Status -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">Status Akun</h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Peran</span>
                        <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 text-xs font-bold uppercase">{{ getActiveRole() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Email Terverifikasi</span>
                        @if($user->email_verified_at)
                            <x-lucide-check-circle class="w-5 h-5 text-emerald-500" />
                        @else
                            <x-lucide-x-circle class="w-5 h-5 text-rose-500" />
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Fingerprint</span>
                        <span class="px-2.5 py-0.5 rounded-full bg-{{ $user->fingerprint_status_color }}-50 text-{{ $user->fingerprint_status_color }}-700 text-xs font-bold uppercase">
                            {{ $user->fingerprint_status_label }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Contact Sync -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">Sistem Terintegrasi</h2>
                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                    <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg mr-3">
                        <x-lucide-database class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-900">Database SDM</p>
                        <p class="text-[10px] text-gray-500">Sinkronisasi Otomatis</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Informasi Dasar</h2>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Nama Pengguna</dt>
                            <dd class="text-sm font-bold text-gray-900">{{ $user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Alamat Email</dt>
                            <dd class="text-sm font-bold text-gray-900">{{ $user->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Satuan Kerja</dt>
                            <dd class="text-sm font-bold text-gray-900">{{ $employee?->satuan_kerja ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Jabatan</dt>
                            <dd class="text-sm font-bold text-gray-900">{{ $employee?->jabatan ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Employee Meta -->
            @if($employee)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Detail Kepegawaian</h2>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">NIDN / NUP</dt>
                            <dd class="text-sm font-bold text-gray-900">{{ $employee->nidn_nup ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Pangkat / Golongan</dt>
                            <dd class="text-sm font-bold text-gray-900">{{ $employee->pangkat_golongan ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Jenis Kelamin</dt>
                            <dd class="text-sm font-bold text-gray-900">{{ $employee->jenis_kelamin }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Homebase</dt>
                            <dd class="text-sm font-bold text-gray-900">{{ $employee->homebase ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
