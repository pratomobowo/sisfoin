@extends('layouts.app')

@section('title', 'Buat Slip Gaji Baru')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Buat Slip Gaji Baru</h3>
                    <div class="card-tools">
                        <a href="{{ route('sdm.slip-gaji.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Upload File Excel</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">Upload file Excel yang berisi data slip gaji karyawan.</p>
                                    <a href="{{ route('sdm.slip-gaji.upload') }}" class="btn btn-primary">
                                        <i class="fas fa-upload"></i> Upload File Excel
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Input Manual</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">Buat slip gaji secara manual dengan mengisi form.</p>
                                    <button class="btn btn-success" disabled>
                                        <i class="fas fa-edit"></i> Input Manual (Coming Soon)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Template Excel</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">Download template Excel untuk memudahkan input data slip gaji.</p>
                                    <a href="{{ route('sdm.slip-gaji.download-template') }}" class="btn btn-info">
                                        <i class="fas fa-download"></i> Download Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Add any JavaScript functionality here if needed
});
</script>
@endpush