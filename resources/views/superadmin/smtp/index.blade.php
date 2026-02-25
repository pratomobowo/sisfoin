@extends('layouts.superadmin')

@section('page-title', 'Konfigurasi SMTP')

<x-breadcrumb-section :items="[
    ['title' => 'Dashboard', 'url' => route('superadmin.dashboard')],
    ['title' => 'Konfigurasi SMTP', 'url' => null],
]" />

@section('page-header')
    <x-superadmin.page-header 
        title="Konfigurasi SMTP"
        description="Kelola pengaturan server email untuk mengirim notifikasi dan email dari sistem"
        icon="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
    />
@endsection

@section('content')
<livewire:superadmin.smtp-setup />
@endsection
