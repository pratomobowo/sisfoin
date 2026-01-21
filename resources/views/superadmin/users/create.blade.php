@extends('layouts.superadmin')

@section('page-title', 'Tambah Pengguna')

@section('page-header')
    <x-superadmin.page-header 
        title="Tambah Pengguna"
        description="Tambah pengguna baru ke sistem"
        :showBackButton="true"
        backRoute="{{ route('superadmin.users.index') }}"
        backText="Kembali"
    />
@endsection

@section('main-content')
    @livewire('superadmin.user-form')
@endsection
