@extends('layouts.superadmin')

@section('page-title', 'Edit Pengguna')

@section('main-content')
    @livewire('superadmin.user-form', ['userId' => $userId])
@endsection
