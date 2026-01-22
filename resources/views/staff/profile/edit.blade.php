@extends('layouts.app')

@section('page-title', 'Ubah Profil')

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
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <a href="{{ route('staff.profile') }}" class="ml-1 text-sm font-medium text-gray-500 hover:text-blue-600 md:ml-2">Profil</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Ubah</span>
                </div>
            </li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Ubah Pengaturan Profil</h1>
            <p class="text-sm text-gray-500">Perbarui informasi akun Anda.</p>
        </div>
        <a href="{{ route('staff.profile') }}" class="inline-flex items-center text-sm font-semibold text-gray-500 hover:text-gray-700">
            <x-lucide-arrow-left class="w-4 h-4 mr-2" />
            Kembali
        </a>
    </div>

    @if (session('status') === 'profile-updated')
        <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-xl flex items-center text-emerald-700 text-sm font-medium">
            <x-lucide-check-circle class="w-5 h-5 mr-3" />
            Profil berhasil diperbarui.
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-900">Informasi Profil</h2>
            <p class="text-xs text-gray-500">Informasi ini akan sinkron dengan sistem SDM.</p>
        </div>
        <div class="p-6">
            <form method="post" action="{{ route('staff.profile.update') }}" class="space-y-6">
                @csrf
                @method('put')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div class="space-y-2">
                        <label for="name" class="text-xs font-bold text-gray-700 uppercase tracking-wider">Nama Pengguna</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus
                               class="w-full rounded-lg border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm font-medium">
                        @error('name')
                            <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <label for="email" class="text-xs font-bold text-gray-700 uppercase tracking-wider">Alamat Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                               class="w-full rounded-lg border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm font-medium">
                        @error('email')
                            <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror

                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <div class="mt-2 text-xs text-gray-600">
                                Email Anda belum diverifikasi.
                                <button form="send-verification" class="text-blue-600 font-bold hover:underline">Klik di sini untuk mengirim ulang.</button>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-4 border-t border-gray-50">
                    <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg shadow-sm transition-all focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Security Info / Note -->
    <div class="bg-amber-50 border border-amber-100 rounded-xl p-6">
        <div class="flex">
            <x-lucide-info class="w-5 h-5 text-amber-600 mr-4 flex-shrink-0" />
            <div>
                <h3 class="text-sm font-bold text-amber-900">Pemberitahuan</h3>
                <p class="text-sm text-amber-800 mt-1">
                    Detail kepegawaian seperti Jabatan, NIP, dan Unit Kerja dikelola oleh Biro SDM. Jika terdapat ketidaksesuaian data, silakan hubungi bagian administrasi SDM.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
