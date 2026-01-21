@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">Detail Peran</h2>
                    <p class="text-gray-600">Lihat detail peran dan izinnya</p>
                </div>
                <a href="{{ route('superadmin.roles.index') }}" 
                   class="px-4 py-2 bg-gray-600 text-white rounded-xl shadow-sm hover:bg-gray-700 transition">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </div>
        </div>

        <!-- Detail will be handled by Livewire component -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-gray-600">Fitur ini diintegrasikan dalam halaman manajemen peran utama.</p>
            <a href="{{ route('superadmin.roles.index') }}" class="text-blue-600 hover:text-blue-700 font-medium">
                Kembali ke Manajemen Peran →
            </a>
        </div>
    </div>
@endsection