@extends('layouts.staff')

@section('page-title', 'Ubah Profil')

@section('content')
<div class="space-y-6">
    {{-- Header Card --}}
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl shadow-lg shadow-blue-200 overflow-hidden">
        <div class="px-5 py-6 lg:px-6 lg:py-7">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 text-blue-100 text-xs font-semibold uppercase tracking-wide mb-2">
                        <a href="{{ route('staff.profile') }}" class="inline-flex items-center hover:text-white transition-colors">
                            <x-lucide-chevron-left class="w-4 h-4 mr-1" />
                            Profil
                        </a>
                        <span>/</span>
                        <span>Edit</span>
                    </div>
                    <h1 class="text-2xl lg:text-3xl font-bold text-white">Ubah Profil</h1>
                    <p class="text-blue-100 mt-1">Perbarui informasi akun Anda</p>
                </div>
                <a href="{{ route('staff.profile') }}" class="inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white text-sm font-semibold rounded-xl transition-all">
                    <x-lucide-arrow-left class="w-4 h-4 mr-2" />
                    Kembali
                </a>
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-white/20 text-white text-xs font-semibold">
                    <x-lucide-user-round-cog class="w-3.5 h-3.5 mr-1.5" />
                    Pembaruan Data Akun
                </span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-white/20 text-white text-xs font-semibold">
                    <x-lucide-mail-check class="w-3.5 h-3.5 mr-1.5" />
                    Nama & Email
                </span>
            </div>
        </div>
    </div>

    @if (session('status') === 'profile-updated')
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-start">
            <div class="flex-shrink-0 w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center mr-4">
                <x-lucide-check-circle class="w-5 h-5 text-emerald-600" />
            </div>
            <div>
                <h3 class="text-sm font-bold text-emerald-900">Berhasil!</h3>
                <p class="text-sm text-emerald-700 mt-1">Profil berhasil diperbarui.</p>
            </div>
        </div>
    @endif

    {{-- Profile Form Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center gap-4">
            <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                <x-lucide-user class="w-5 h-5 text-blue-600" />
            </div>
            <div>
                <h2 class="text-base font-bold text-gray-900">Informasi Profil</h2>
                <p class="text-xs text-gray-500">Perbarui informasi akun Anda</p>
            </div>
        </div>
        <div class="p-6">
            <form method="post" action="{{ route('staff.profile.update') }}" class="space-y-6">
                @csrf
                @method('put')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Name Field --}}
                    <div class="space-y-2">
                        <label for="name" class="block text-sm font-semibold text-gray-700">
                            Nama Pengguna
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <x-lucide-user class="w-5 h-5 text-gray-400" />
                            </div>
                            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus
                                   class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all">
                        </div>
                        @error('name')
                            <p class="text-xs text-rose-600 font-medium flex items-center mt-1">
                                <x-lucide-alert-circle class="w-3 h-3 mr-1" />
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Email Field --}}
                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-semibold text-gray-700">
                            Alamat Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <x-lucide-mail class="w-5 h-5 text-gray-400" />
                            </div>
                            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                                   class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all">
                        </div>
                        @error('email')
                            <p class="text-xs text-rose-600 font-medium flex items-center mt-1">
                                <x-lucide-alert-circle class="w-3 h-3 mr-1" />
                                {{ $message }}
                            </p>
                        @enderror

                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-xl">
                                <div class="flex items-start gap-2">
                                    <x-lucide-alert-circle class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" />
                                    <div class="text-xs text-amber-700">
                                        Email Anda belum diverifikasi.
                                        <button form="send-verification" class="font-bold text-amber-800 hover:underline ml-1">Klik di sini untuk mengirim ulang.</button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="flex items-center gap-4 pt-4 border-t border-gray-100">
                    <button type="submit" class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-200 transition-all focus:ring-4 focus:ring-blue-200">
                        <x-lucide-save class="w-4 h-4 mr-2" />
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('staff.profile') }}" class="inline-flex items-center justify-center px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold rounded-xl transition-all">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Info Card --}}
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0 w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                <x-lucide-info class="w-5 h-5 text-amber-600" />
            </div>
            <div>
                <h3 class="text-sm font-bold text-amber-900 mb-1">Pemberitahuan</h3>
                <p class="text-sm text-amber-800 leading-relaxed">
                    Detail kepegawaian seperti Jabatan, NIP, dan Unit Kerja dikelola oleh Biro SDM. Jika terdapat ketidaksesuaian data, silakan hubungi bagian administrasi SDM.
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Hidden form for email verification --}}
@if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
    <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="hidden">
        @csrf
    </form>
@endif
@endsection
