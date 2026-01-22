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
