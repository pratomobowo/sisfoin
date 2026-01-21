@extends('layouts.superadmin')

@section('page-title', 'Manajemen Peran')

@php
    $breadcrumbs = [
        ['title' => 'Manajemen Peran']
    ];
@endphp

@section('main-content')
    <livewire:superadmin.role-management />
@endsection