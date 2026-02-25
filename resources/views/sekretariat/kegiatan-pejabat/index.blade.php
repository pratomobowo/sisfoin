@extends('layouts.app')

@section('title', 'Kegiatan Pejabat')

<x-breadcrumb-section :breadcrumbs="[
    'Dashboard' => route('dashboard'),
    'Sekretariat' => null,
    'Kegiatan Pejabat' => null,
]" />

@section('content')
<div class="container mx-auto px-4 py-8">
    <livewire:sekretariat.kegiatan-pejabat-management />
</div>
@endsection
