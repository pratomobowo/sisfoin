<!-- Import Modal -->
@if($showImportModal)
<div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center p-4 z-50 modal-backdrop">
    
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col transform transition-all">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">Import Pengguna</h3>
                        <p class="text-blue-100 text-sm">Impor data pengguna dari sistem</p>
                    </div>
                </div>
                <button wire:click="closeImportModal" 
                        class="w-8 h-8 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-full flex items-center justify-center transition-all backdrop-blur-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6 flex-1 overflow-y-auto bg-gray-50">
            <!-- Info Card -->
            <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6 shadow-sm">
                <div class="flex items-start space-x-4">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-900 mb-2">Informasi Import</h4>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            Sistem akan mengimpor data pengguna dari tabel <span class="font-medium text-gray-900">Karyawan</span> dan <span class="font-medium text-gray-900">Dosen</span> yang memiliki alamat email. Pengguna baru akan dibuat dengan password default dan peran Staff.
                        </p>
                    </div>
                </div>
            </div>
            
            @if(!empty($importCounts))
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Karyawan Card -->
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283-.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h5 class="font-semibold text-lg">Karyawan</h5>
                                    <p class="text-blue-100 text-sm">Data karyawan untuk import</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="grid grid-cols-4 gap-3">
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                        <span class="text-gray-600 font-bold text-lg">{{ $importCounts['karyawan_total'] ?? 0 }}</span>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">Total</p>
                                    <p class="text-xs text-gray-500">Dengan email</p>
                                </div>
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                        <span class="text-blue-600 font-bold text-lg">{{ $importCounts['karyawan_valid'] ?? 0 }}</span>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">Valid</p>
                                    <p class="text-xs text-gray-500">Lengkap</p>
                                </div>
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                        <span class="text-green-600 font-bold text-lg">{{ $importCounts['karyawan_new'] ?? 0 }}</span>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">Baru</p>
                                    <p class="text-xs text-gray-500">Akan dibuat</p>
                                </div>
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                        <span class="text-yellow-600 font-bold text-lg">{{ $importCounts['karyawan_existing'] ?? 0 }}</span>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">Ada</p>
                                    <p class="text-xs text-gray-500">Sudah terdaftar</p>
                                </div>
                            </div>
                            @if(($importCounts['karyawan_total'] ?? 0) > ($importCounts['karyawan_valid'] ?? 0))
                                <div class="mt-3 p-2 bg-orange-50 rounded-lg border border-orange-200">
                                    <p class="text-xs text-orange-700 text-center">
                                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ ($importCounts['karyawan_total'] ?? 0) - ($importCounts['karyawan_valid'] ?? 0) }} data tidak lengkap (nama kosong)
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Dosen Card -->
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <div class="bg-gradient-to-r from-green-500 to-emerald-600 text-white p-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h5 class="font-semibold text-lg">Dosen</h5>
                                    <p class="text-green-100 text-sm">Data dosen untuk import</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="grid grid-cols-4 gap-3">
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                        <span class="text-gray-600 font-bold text-lg">{{ $importCounts['dosen_total'] ?? 0 }}</span>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">Total</p>
                                    <p class="text-xs text-gray-500">Dengan email</p>
                                </div>
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                        <span class="text-green-600 font-bold text-lg">{{ $importCounts['dosen_valid'] ?? 0 }}</span>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">Valid</p>
                                    <p class="text-xs text-gray-500">Lengkap</p>
                                </div>
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                        <span class="text-green-600 font-bold text-lg">{{ $importCounts['dosen_new'] ?? 0 }}</span>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">Baru</p>
                                    <p class="text-xs text-gray-500">Akan dibuat</p>
                                </div>
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                        <span class="text-yellow-600 font-bold text-lg">{{ $importCounts['dosen_existing'] ?? 0 }}</span>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">Ada</p>
                                    <p class="text-xs text-gray-500">Sudah terdaftar</p>
                                </div>
                            </div>
                            @if(($importCounts['dosen_total'] ?? 0) > ($importCounts['dosen_valid'] ?? 0))
                                <div class="mt-3 p-2 bg-orange-50 rounded-lg border border-orange-200">
                                    <p class="text-xs text-orange-700 text-center">
                                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ ($importCounts['dosen_total'] ?? 0) - ($importCounts['dosen_valid'] ?? 0) }} data tidak lengkap (nama kosong)
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Important Notes -->
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
                    <div class="flex items-start space-x-3">
                        <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-amber-900 mb-3">Penting untuk Diperhatikan</h4>
                            <div class="space-y-2">
                                <div class="flex items-start space-x-2">
                                    <div class="w-1.5 h-1.5 bg-amber-400 rounded-full mt-2 flex-shrink-0"></div>
                                    <p class="text-amber-800 text-sm">
                                        <span class="font-medium">Password default:</span> 
                                        <code class="bg-amber-100 px-2 py-1 rounded text-xs font-mono">password123</code>
                                    </p>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <div class="w-1.5 h-1.5 bg-amber-400 rounded-full mt-2 flex-shrink-0"></div>
                                    <p class="text-amber-800 text-sm">
                                        <span class="font-medium">Peran default:</span> 
                                        Semua pengguna baru akan mendapat peran "Staff"
                                    </p>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <div class="w-1.5 h-1.5 bg-amber-400 rounded-full mt-2 flex-shrink-0"></div>
                                    <p class="text-amber-800 text-sm">
                                        <span class="font-medium">Update data:</span> 
                                        Nama pengguna yang sudah ada akan diperbarui sesuai data terbaru
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Loading State -->
                <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="animate-spin w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Mempersiapkan Data</h4>
                    <p class="text-gray-600">Sedang menganalisis data karyawan dan dosen...</p>
                </div>
            @endif

            <!-- Modal Footer -->
            <div class="bg-white border-t border-gray-200 px-6 py-4 mt-6">
                <div class="flex items-center justify-between">
                    <button type="button" 
                            wire:click="closeImportModal"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium text-sm transition-colors flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span>Batal</span>
                    </button>
                    <button type="button" 
                            wire:click="importUsers"
                            class="px-6 py-2 bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-lg hover:from-blue-700 hover:to-indigo-800 font-medium text-sm transition-all transform hover:scale-105 flex items-center space-x-2 shadow-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <span>Mulai Import</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<style>
.modal-backdrop {
    animation: fadeIn 0.3s ease-out;
}

.modal-backdrop > div {
    animation: scaleIn 0.3s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes scaleIn {
    from { 
        opacity: 0;
        transform: scale(0.9);
    }
    to { 
        opacity: 1;
        transform: scale(1);
    }
}
</style>
