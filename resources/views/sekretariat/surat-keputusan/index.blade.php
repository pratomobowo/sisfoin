@extends('layouts.app')

@section('title', 'Surat Keputusan')

<x-breadcrumb-section :breadcrumbs="[
    'Dashboard' => route('dashboard'),
    'Surat Keputusan' => null,
]" />

@section('content')
<div class="container mx-auto px-4 py-8">
    <livewire:sekretariat.surat-keputusan-management />
</div>
@endsection
