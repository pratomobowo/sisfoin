@extends('layouts.superadmin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Edit Peran</h2>
                <p class="mt-1 text-sm text-gray-600">Edit peran dan hak aksesnya</p>
            </div>
            <a href="{{ route('superadmin.roles.index') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center space-x-2 inline-block">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Role Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <livewire:superadmin.role-form :roleId="$role->id" />
    </div>
</div>
@endsection
