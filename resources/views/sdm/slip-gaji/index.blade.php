@extends('layouts.app')

@section('page-title', 'Slip Gaji')


@section('content')
    <!-- Livewire Component -->
    @livewire('sdm.slip-gaji-management')

    <!-- Include Livewire Components -->
    @livewire('sdm.slip-gaji-form')
    @livewire('sdm.slip-gaji-import')
@endsection
