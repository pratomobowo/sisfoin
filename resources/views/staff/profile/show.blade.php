@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-gray-50 pb-24 lg:pb-0" x-data="{ passwordModalOpen: {{ ($errors->updatePassword->any() || session('status') === 'password-updated') ? 'true' : 'false' }} }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl shadow-lg shadow-blue-200 overflow-hidden">
            <div class="px-5 py-6 lg:px-6 lg:py-7">
                <div class="flex items-start gap-4">
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
        
        <!-- Account Info -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Informasi Akun</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Nama Pengguna</p>
                        <p class="font-medium text-gray-800">{{ $user->name }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="font-medium text-gray-800">{{ $user->email }}</p>
                        @if($user->email_verified_at)
                            <p class="text-xs font-medium text-emerald-600 mt-0.5">Terverifikasi</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Role Aktif</p>
                        <p class="font-medium text-gray-800">{{ ucfirst(str_replace('-', ' ', getActiveRole() ?? 'Staff')) }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Fingerprint</p>
                        <p class="font-medium {{ $user->fingerprint_pin ? 'text-emerald-600' : 'text-gray-400' }}">{{ $user->fingerprint_pin ? 'Terdaftar' : 'Belum Terdaftar' }}</p>
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
            
            <button type="button" @click="passwordModalOpen = true"
               class="w-full flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
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
            </button>
        </div>

        <div x-show="passwordModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div @click="passwordModalOpen = false" class="absolute inset-0 bg-black/50"></div>
            <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900">Reset Password</h3>
                    <button type="button" @click="passwordModalOpen = false" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                        <x-lucide-x class="w-5 h-5" />
                    </button>
                </div>

                <form method="post" action="{{ route('password.update') }}" class="p-6 space-y-4">
                    @csrf
                    @method('put')

                    <div>
                        <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-1">Password Saat Ini</label>
                        <input id="current_password" name="current_password" type="password" autocomplete="current-password" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all">
                        @error('current_password', 'updatePassword')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Password Baru</label>
                        <input id="password" name="password" type="password" autocomplete="new-password" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all">
                        @error('password', 'updatePassword')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1">Konfirmasi Password Baru</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all">
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3">
                        <button type="button" @click="passwordModalOpen = false" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Simpan Password</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
