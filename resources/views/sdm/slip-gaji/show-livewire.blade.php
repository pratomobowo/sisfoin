@extends('layouts.app')

@section('page-title', 'Detail Slip Gaji')

@section('breadcrumb')
    <x-superadmin.breadcrumb-topbar 
        :items="[
            ['title' => 'SDM', 'url' => route('sdm.employees.index')],
            ['title' => 'Slip Gaji', 'url' => route('sdm.slip-gaji.index')],
            ['title' => 'Detail', 'url' => null]
        ]"
    />
@endsection

@section('content')
    <div class="space-y-6">
        @livewire('sdm.slip-gaji-detail', ['header' => $header])
    </div>
@endsection
