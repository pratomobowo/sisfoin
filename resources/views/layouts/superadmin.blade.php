@extends('layouts.app')

@section('breadcrumb')
    <x-superadmin.breadcrumb-topbar :items="getBreadcrumb()" />
@endsection

@section('content')
<div class="container-fluid px-4">

    <!-- Page Header -->
    @hasSection('page-header')
        <div class="mb-6">
            @yield('page-header')
        </div>
    @endif

    <!-- Flash Messages -->
    @if(session()->has('success') || session()->has('error') || session()->has('info') || session()->has('warning'))
        <div class="mb-6">
            @if(session('success'))
                <x-alert type="success" :message="session('success')" dismissible />
            @endif
            
            @if(session('error'))
                <x-alert type="error" :message="session('error')" dismissible />
            @endif
            
            @if(session('info'))
                <x-alert type="info" :message="session('info')" dismissible />
            @endif
            
            @if(session('warning'))
                <x-alert type="warning" :message="session('warning')" dismissible />
            @endif
        </div>
    @endif

    <!-- Main Content -->
    <div class="row">
        <div class="col-12">
            @hasSection('main-content')
                @yield('main-content')
            @else
                <x-card>
                    @yield('content-body')
                </x-card>
            @endif
        </div>
    </div>
</div>

@push('scripts')
    @stack('page-scripts')
@endpush
@endsection
