@extends('layouts.superadmin')

@section('page-title', 'Email Log')

@section('breadcrumb')
    <x-superadmin.breadcrumb-topbar :items="[
        ['title' => 'Dashboard', 'url' => route('superadmin.dashboard')],
        ['title' => 'Email Log', 'url' => null]
    ]" />
@endsection

@section('page-header')
    <x-superadmin.page-header 
        title="Email Log"
        description="Kelola log email yang terkirim dari sistem, pantau status pengiriman dan kelola email yang gagal"
        icon="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
    />
@endsection

@section('content')
<livewire:superadmin.email-log-management />
@endsection
