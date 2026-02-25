@extends('layouts.app')

@section('page-title', 'Edit Slip Gaji')

<x-breadcrumb-section :items="[
    ['title' => 'SDM', 'url' => route('sdm.employees.index')],
    ['title' => 'Slip Gaji', 'url' => route('sdm.slip-gaji.index')],
    ['title' => 'Detail', 'url' => route('sdm.slip-gaji.show', $detail->header)],
    ['title' => 'Edit', 'url' => null],
]" />


@section('content')
    <div class="space-y-6">

    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Edit Slip Gaji</h2>
                <p class="mt-1 text-sm text-gray-600">NIP: {{ $detail->nip }} | Periode: {{ \Carbon\Carbon::parse($detail->header->periode)->format('F Y') }}</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('sdm.slip-gaji.show', $detail->header) }}" 
                   class="border border-gray-500 text-gray-500 hover:bg-gray-50 px-4 py-2 rounded-lg transition-colors flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Kembali</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form action="{{ route('sdm.slip-gaji.update', $detail) }}" method="POST">
            @csrf
            @method('PUT')
            
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

            <!-- Basic Information -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Dasar</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="nip" class="block text-sm font-medium text-gray-700 mb-1">
                            NIP <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="nip" 
                               name="nip" 
                               value="{{ old('nip', $detail->nip) }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('nip') border-red-300 @enderror"
                               required>
                        @error('nip')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                            Status
                        </label>
                        <select id="status" 
                                name="status" 
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <option value="KARYAWAN_TETAP" {{ old('status', $detail->status) == 'KARYAWAN_TETAP' ? 'selected' : '' }}>Karyawan Tetap</option>
                            <option value="KARYAWAN_KONTRAK" {{ old('status', $detail->status) == 'KARYAWAN_KONTRAK' ? 'selected' : '' }}>Karyawan Kontrak</option>
                            <option value="KARYAWAN_MAGANG" {{ old('status', $detail->status) == 'KARYAWAN_MAGANG' ? 'selected' : '' }}>Karyawan Magang</option>
                            <option value="DOSEN_TETAP" {{ old('status', $detail->status) == 'DOSEN_TETAP' ? 'selected' : '' }}>Dosen Tetap</option>
                            <option value="DOSEN_DPK" {{ old('status', $detail->status) == 'DOSEN_DPK' ? 'selected' : '' }}>Dosen DPK</option>
                            <option value="DOSEN_PK" {{ old('status', $detail->status) == 'DOSEN_PK' ? 'selected' : '' }}>Dosen Perjanjian Khusus</option>
                            <option value="DOSEN_GURU_BESAR" {{ old('status', $detail->status) == 'DOSEN_GURU_BESAR' ? 'selected' : '' }}>Guru Besar</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Pendapatan Section -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Pendapatan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label for="gaji_pokok" class="block text-sm font-medium text-gray-700 mb-1">GAJI POKOK</label>
                        <input type="number" 
                               id="gaji_pokok" 
                               name="gaji_pokok" 
                               value="{{ old('gaji_pokok', $detail->gaji_pokok) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('gaji_pokok') border-red-300 @enderror">
                        @error('gaji_pokok')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="honor_tetap" class="block text-sm font-medium text-gray-700 mb-1">HONOR TETAP</label>
                        <input type="number" 
                               id="honor_tetap" 
                               name="honor_tetap" 
                               value="{{ old('honor_tetap', $detail->honor_tetap) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('honor_tetap') border-red-300 @enderror">
                        @error('honor_tetap')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="tpp" class="block text-sm font-medium text-gray-700 mb-1">TPP</label>
                        <input type="number" 
                               id="tpp" 
                               name="tpp" 
                               value="{{ old('tpp', $detail->tpp) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('tpp') border-red-300 @enderror">
                        @error('tpp')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="insentif_golongan" class="block text-sm font-medium text-gray-700 mb-1">INSENTIF GOLONGAN</label>
                        <input type="number" 
                               id="insentif_golongan" 
                               name="insentif_golongan" 
                               value="{{ old('insentif_golongan', $detail->insentif_golongan) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('insentif_golongan') border-red-300 @enderror">
                        @error('insentif_golongan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="tunjangan_keluarga" class="block text-sm font-medium text-gray-700 mb-1">TUNJANGAN KELUARGA</label>
                        <input type="number" 
                               id="tunjangan_keluarga" 
                               name="tunjangan_keluarga" 
                               value="{{ old('tunjangan_keluarga', $detail->tunjangan_keluarga) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('tunjangan_keluarga') border-red-300 @enderror">
                        @error('tunjangan_keluarga')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="tunjangan_kemahalan" class="block text-sm font-medium text-gray-700 mb-1">TUNJANGAN KEMAHALAN</label>
                        <input type="number" 
                               id="tunjangan_kemahalan" 
                               name="tunjangan_kemahalan" 
                               value="{{ old('tunjangan_kemahalan', $detail->tunjangan_kemahalan) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('tunjangan_kemahalan') border-red-300 @enderror">
                        @error('tunjangan_kemahalan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="tunjangan_pmb" class="block text-sm font-medium text-gray-700 mb-1">TUNJANGAN PMB</label>
                        <input type="number" 
                               id="tunjangan_pmb" 
                               name="tunjangan_pmb" 
                               value="{{ old('tunjangan_pmb', $detail->tunjangan_pmb) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('tunjangan_pmb') border-red-300 @enderror">
                        @error('tunjangan_pmb')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="tunjangan_golongan" class="block text-sm font-medium text-gray-700 mb-1">TUNJANGAN GOLONGAN</label>
                        <input type="number" 
                               id="tunjangan_golongan" 
                               name="tunjangan_golongan" 
                               value="{{ old('tunjangan_golongan', $detail->tunjangan_golongan) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('tunjangan_golongan') border-red-300 @enderror">
                        @error('tunjangan_golongan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="tunjangan_masa_kerja" class="block text-sm font-medium text-gray-700 mb-1">TUNJANGAN MASA KERJA</label>
                        <input type="number" 
                               id="tunjangan_masa_kerja" 
                               name="tunjangan_masa_kerja" 
                               value="{{ old('tunjangan_masa_kerja', $detail->tunjangan_masa_kerja) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('tunjangan_masa_kerja') border-red-300 @enderror">
                        @error('tunjangan_masa_kerja')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="transport" class="block text-sm font-medium text-gray-700 mb-1">TRANSPORT</label>
                        <input type="number" 
                               id="transport" 
                               name="transport" 
                               value="{{ old('transport', $detail->transport) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('transport') border-red-300 @enderror">
                        @error('transport')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="tunjangan_kesehatan" class="block text-sm font-medium text-gray-700 mb-1">TUNJANGAN KESEHATAN</label>
                        <input type="number" 
                               id="tunjangan_kesehatan" 
                               name="tunjangan_kesehatan" 
                               value="{{ old('tunjangan_kesehatan', $detail->tunjangan_kesehatan) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('tunjangan_kesehatan') border-red-300 @enderror">
                        @error('tunjangan_kesehatan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="tunjangan_rumah" class="block text-sm font-medium text-gray-700 mb-1">TUNJANGAN RUMAH</label>
                        <input type="number" 
                               id="tunjangan_rumah" 
                               name="tunjangan_rumah" 
                               value="{{ old('tunjangan_rumah', $detail->tunjangan_rumah) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('tunjangan_rumah') border-red-300 @enderror">
                        @error('tunjangan_rumah')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="tunjangan_pendidikan" class="block text-sm font-medium text-gray-700 mb-1">TUNJANGAN PENDIDIKAN</label>
                        <input type="number" 
                               id="tunjangan_pendidikan" 
                               name="tunjangan_pendidikan" 
                               value="{{ old('tunjangan_pendidikan', $detail->tunjangan_pendidikan) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('tunjangan_pendidikan') border-red-300 @enderror">
                        @error('tunjangan_pendidikan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="tunjangan_struktural" class="block text-sm font-medium text-gray-700 mb-1">TUNJANGAN STRUKTURAL</label>
                        <input type="number" 
                               id="tunjangan_struktural" 
                               name="tunjangan_struktural" 
                               value="{{ old('tunjangan_struktural', $detail->tunjangan_struktural) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('tunjangan_struktural') border-red-300 @enderror">
                        @error('tunjangan_struktural')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="tunjangan_fungsional" class="block text-sm font-medium text-gray-700 mb-1">TUNJANGAN FUNGSIONAL</label>
                        <input type="number" 
                               id="tunjangan_fungsional" 
                               name="tunjangan_fungsional" 
                               value="{{ old('tunjangan_fungsional', $detail->tunjangan_fungsional) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('tunjangan_fungsional') border-red-300 @enderror">
                        @error('tunjangan_fungsional')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="beban_manajemen" class="block text-sm font-medium text-gray-700 mb-1">BEBAN MANAJEMEN</label>
                        <input type="number" 
                               id="beban_manajemen" 
                               name="beban_manajemen" 
                               value="{{ old('beban_manajemen', $detail->beban_manajemen) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('beban_manajemen') border-red-300 @enderror">
                        @error('beban_manajemen')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="honor_tunai" class="block text-sm font-medium text-gray-700 mb-1">HONOR TUNAI</label>
                        <input type="number" 
                               id="honor_tunai" 
                               name="honor_tunai" 
                               value="{{ old('honor_tunai', $detail->honor_tunai) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('honor_tunai') border-red-300 @enderror">
                        @error('honor_tunai')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-2 lg:col-span-3">
                        <label for="penerimaan_kotor" class="block text-sm font-medium text-gray-700 mb-1">PENERIMAAN KOTOR</label>
                        <input type="number" 
                               id="penerimaan_kotor" 
                               name="penerimaan_kotor" 
                               value="{{ old('penerimaan_kotor', $detail->penerimaan_kotor) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('penerimaan_kotor') border-red-300 @enderror">
                        @error('penerimaan_kotor')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Potongan Section -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Potongan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label for="potongan_arisan" class="block text-sm font-medium text-gray-700 mb-1">POTONGAN ARISAN</label>
                        <input type="number" 
                               id="potongan_arisan" 
                               name="potongan_arisan" 
                               value="{{ old('potongan_arisan', $detail->potongan_arisan) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('potongan_arisan') border-red-300 @enderror">
                        @error('potongan_arisan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="potongan_koperasi" class="block text-sm font-medium text-gray-700 mb-1">POTONGAN KOPERASI</label>
                        <input type="number" 
                               id="potongan_koperasi" 
                               name="potongan_koperasi" 
                               value="{{ old('potongan_koperasi', $detail->potongan_koperasi) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('potongan_koperasi') border-red-300 @enderror">
                        @error('potongan_koperasi')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="potongan_lazmaal" class="block text-sm font-medium text-gray-700 mb-1">POTONGAN LAZMAAL</label>
                        <input type="number" 
                               id="potongan_lazmaal" 
                               name="potongan_lazmaal" 
                               value="{{ old('potongan_lazmaal', $detail->potongan_lazmaal) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('potongan_lazmaal') border-red-300 @enderror">
                        @error('potongan_lazmaal')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="potongan_bpjs_kesehatan" class="block text-sm font-medium text-gray-700 mb-1">POTONGAN BPJS KESEHATAN</label>
                        <input type="number" 
                               id="potongan_bpjs_kesehatan" 
                               name="potongan_bpjs_kesehatan" 
                               value="{{ old('potongan_bpjs_kesehatan', $detail->potongan_bpjs_kesehatan) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('potongan_bpjs_kesehatan') border-red-300 @enderror">
                        @error('potongan_bpjs_kesehatan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="potongan_bpjs_ketenagakerjaan" class="block text-sm font-medium text-gray-700 mb-1">POTONGAN BPJS KETENAGAKERJAAN</label>
                        <input type="number" 
                               id="potongan_bpjs_ketenagakerjaan" 
                               name="potongan_bpjs_ketenagakerjaan" 
                               value="{{ old('potongan_bpjs_ketenagakerjaan', $detail->potongan_bpjs_ketenagakerjaan) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('potongan_bpjs_ketenagakerjaan') border-red-300 @enderror">
                        @error('potongan_bpjs_ketenagakerjaan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="potongan_bkd" class="block text-sm font-medium text-gray-700 mb-1">POTONGAN BKD</label>
                        <input type="number" 
                               id="potongan_bkd" 
                               name="potongan_bkd" 
                               value="{{ old('potongan_bkd', $detail->potongan_bkd) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('potongan_bkd') border-red-300 @enderror">
                        @error('potongan_bkd')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Pajak Section -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Pajak</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label for="pajak" class="block text-sm font-medium text-gray-700 mb-1">PAJAK</label>
                        <input type="number" 
                               id="pajak" 
                               name="pajak" 
                               value="{{ old('pajak', $detail->pajak) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('pajak') border-red-300 @enderror">
                        @error('pajak')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="pph21_terhutang" class="block text-sm font-medium text-gray-700 mb-1">PPh 21 TERHUTANG</label>
                        <input type="number" 
                               id="pph21_terhutang" 
                               name="pph21_terhutang" 
                               value="{{ old('pph21_terhutang', $detail->pph21_terhutang) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('pph21_terhutang') border-red-300 @enderror">
                        @error('pph21_terhutang')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="pph21_sudah_dipotong" class="block text-sm font-medium text-gray-700 mb-1">PPh 21 SUDAH DIPOTONG</label>
                        <input type="number" 
                               id="pph21_sudah_dipotong" 
                               name="pph21_sudah_dipotong" 
                               value="{{ old('pph21_sudah_dipotong', $detail->pph21_sudah_dipotong) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('pph21_sudah_dipotong') border-red-300 @enderror">
                        @error('pph21_sudah_dipotong')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="pph21_kurang_dipotong" class="block text-sm font-medium text-gray-700 mb-1">PPh 21 KURANG DIPOTONG</label>
                        <input type="number" 
                               id="pph21_kurang_dipotong" 
                               name="pph21_kurang_dipotong" 
                               value="{{ old('pph21_kurang_dipotong', $detail->pph21_kurang_dipotong) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('pph21_kurang_dipotong') border-red-300 @enderror">
                        @error('pph21_kurang_dipotong')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Total Section -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Total</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="penerimaan_bersih" class="block text-sm font-medium text-gray-700 mb-1">PENERIMAAN BERSIH</label>
                        <input type="number" 
                               id="penerimaan_bersih" 
                               name="penerimaan_bersih" 
                               value="{{ old('penerimaan_bersih', $detail->penerimaan_bersih) }}"
                               step="0.01"
                               min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('penerimaan_bersih') border-red-300 @enderror">
                        @error('penerimaan_bersih')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-between">
                <a href="{{ route('sdm.slip-gaji.show', $detail->header) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Batal
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-6 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
