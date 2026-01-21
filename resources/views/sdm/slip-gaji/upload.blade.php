@extends('layouts.app')

@section('page-title', 'Upload Slip Gaji')

@section('breadcrumb')
    <x-superadmin.breadcrumb-topbar 
        :items="[
            ['title' => 'SDM', 'url' => route('sdm.employees.index')],
            ['title' => 'Slip Gaji', 'url' => route('sdm.slip-gaji.index')],
            ['title' => 'Upload', 'url' => null]
        ]"
    />
@endsection


@section('content')
    <div class="space-y-6">

    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Upload Slip Gaji</h2>
                <p class="mt-1 text-sm text-gray-600">Upload data slip gaji dari file Excel</p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-2">
                <a href="{{ route('sdm.slip-gaji.download-template') }}" 
                   class="border border-green-500 text-green-500 hover:bg-green-50 px-4 py-2 rounded-lg transition-colors flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Download Template</span>
                </a>
                <a href="{{ route('sdm.slip-gaji.index') }}" 
                   class="border border-gray-500 text-gray-500 hover:bg-gray-50 px-4 py-2 rounded-lg transition-colors flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Kembali</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Upload Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form action="{{ route('sdm.slip-gaji.upload.process') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <!-- Display validation errors -->
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Terdapat beberapa kesalahan:</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- File Upload -->
            <div class="mb-6">
                <label for="file" class="block text-sm font-medium text-gray-700 mb-1">
                    File Excel <span class="text-red-500">*</span>
                </label>
                <div id="dropZone" class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition-colors cursor-pointer">
                    <div class="space-y-1 text-center">
                        <svg id="uploadIcon" class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600 justify-center">
                            <label for="file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                <span id="uploadText">Upload file</span>
                                <input id="file" name="file" type="file" class="sr-only" accept=".xlsx,.xls" required>
                            </label>
                            <p class="pl-1" id="dragText">atau drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500" id="fileInfo">XLSX, XLS hingga 10MB</p>
                        <p id="fileName" class="text-sm font-medium text-blue-600 hidden"></p>
                    </div>
                </div>
                @error('file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>


            <!-- Periode Field -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Periode <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-4">
                    <!-- Bulan -->
                    <div>
                        <select id="bulan" name="bulan" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Bulan</option>
                            <option value="01">Januari</option>
                            <option value="02">Februari</option>
                            <option value="03">Maret</option>
                            <option value="04">April</option>
                            <option value="05">Mei</option>
                            <option value="06">Juni</option>
                            <option value="07">Juli</option>
                            <option value="08">Agustus</option>
                            <option value="09">September</option>
                            <option value="10">Oktober</option>
                            <option value="11">November</option>
                            <option value="12">Desember</option>
                        </select>
                        @error('bulan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Tahun -->
                    <div>
                        <select id="tahun" name="tahun" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Tahun</option>
                            @php
                                $currentYear = date('Y');
                                for ($i = 0; $i < 3; $i++) {
                                    $year = $currentYear - $i;
                                    echo "<option value=\"{$year}\">{$year}</option>";
                                }
                            @endphp
                        </select>
                        @error('tahun')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <p class="mt-1 text-sm text-gray-500">Pilih bulan dan tahun untuk slip gaji</p>
            </div>

            <!-- Mode Slip Gaji -->
            <div class="mb-6">
                <label for="mode" class="block text-sm font-medium text-gray-700 mb-1">
                    Mode Slip Gaji <span class="text-red-500">*</span>
                </label>
                <select id="mode" name="mode" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="standard">Slip Gaji Standard</option>
                    <option value="gaji_13">Slip Gaji 13</option>
                </select>
                <p class="mt-1 text-sm text-gray-500">Pilih mode slip gaji yang akan diupload</p>
                @error('mode')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Instructions -->
            <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Petunjuk Upload</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Download template terlebih dahulu untuk memastikan format yang benar</li>
                                <li>Isi data sesuai dengan kolom yang tersedia</li>
                                <li>File harus berformat .xlsx atau .xls</li>
                                <li>Ukuran file maksimal 10MB</li>
                                <li>Pastikan periode sudah diisi dengan format YYYY-MM</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end">
                <a href="{{ route('sdm.slip-gaji.index') }}" 
                   class="mr-3 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 px-4 py-2 rounded-lg transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <span>Upload Data</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('file');
    const dropZone = document.getElementById('dropZone');
    const fileName = document.getElementById('fileName');
    const uploadText = document.getElementById('uploadText');
    const dragText = document.getElementById('dragText');
    const fileInfo = document.getElementById('fileInfo');
    const uploadIcon = document.getElementById('uploadIcon');

    // Handle file selection
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            displayFileName(file.name);
        }
    });

    // Handle drag and drop
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('border-blue-500', 'bg-blue-50');
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-blue-500', 'bg-blue-50');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            displayFileName(files[0].name);
        }
    });

    // Click on drop zone to trigger file input
    dropZone.addEventListener('click', function(e) {
        if (e.target !== fileInput) {
            fileInput.click();
        }
    });

    function displayFileName(name) {
        fileName.textContent = name;
        fileName.classList.remove('hidden');
        uploadText.textContent = 'File dipilih:';
        dragText.classList.add('hidden');
        fileInfo.classList.add('hidden');
        uploadIcon.classList.add('hidden');
    }
});
</script>
@endsection
