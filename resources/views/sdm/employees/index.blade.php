@extends('layouts.app')

@section('page-title', 'Data Karyawan')


@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="space-y-6">
                <!-- Employee Management Component -->
                <livewire:sdm.employee-management />
            </div>
        </div>
    </div>
</div>
@endsection
