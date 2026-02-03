@extends('layouts.app')

@section('page-title', 'Ubah Profil')

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
            <li class="inline-flex items-center">
                <a href="{{ route('staff.profile') }}" class="text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors">
                    Profil
                </a>
                <x-lucide-chevron-right class="w-4 h-4 text-gray-400 mx-1 sm:mx-2" />
            </li>
            <li>
                <span class="text-sm font-semibold text-gray-900">
                    Ubah
                </span>
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
