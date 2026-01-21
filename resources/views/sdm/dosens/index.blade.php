@extends('layouts.app')

@section('page-title', 'Data Dosen')


@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="space-y-6">
                <!-- Dosen Management Component -->
                <livewire:sdm.dosen-management />
            </div>
        </div>
    </div>
</div>
@endsection