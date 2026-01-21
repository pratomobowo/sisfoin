<div>
    <!-- User Import Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center p-4 z-[50]" wire:click.self="closeModal">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md max-h-[90vh] overflow-hidden flex flex-col">
                <!-- Modal Header -->
                <div class="bg-blue-600 text-white p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <h3 class="text-lg font-semibold">Import Pengguna</h3>
                        </div>
                        <button wire:click="closeModal" class="text-white hover:text-gray-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="p-4 flex-1 overflow-y-auto">
                    @if($isLoading)
                        <!-- Loading State -->
                        <div class="text-center py-8">
                            <div class="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                            <p class="text-gray-600">Memuat data...</p>
                        </div>
                    @else
                        <!-- Import Summary -->
                        <div class="space-y-4">
                            <!-- Info -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                <p class="text-sm text-blue-800">
                                    <strong>Informasi:</strong> Import pengguna dari data karyawan dan dosen yang aktif, memiliki email, dan memiliki NIP.
                                </p>
                            </div>

                            <!-- Stats -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-center">
                                    <div class="text-2xl font-bold text-gray-900">{{ $importCounts['employees_total'] ?? 0 }}</div>
                                    <div class="text-xs text-gray-600">Karyawan</div>
                                </div>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-center">
                                    <div class="text-2xl font-bold text-gray-900">{{ $importCounts['dosens_total'] ?? 0 }}</div>
                                    <div class="text-xs text-gray-600">Dosen</div>
                                </div>
                                <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-center">
                                    <div class="text-2xl font-bold text-green-600">{{ $importCounts['total_new'] ?? 0 }}</div>
                                    <div class="text-xs text-green-700">Pengguna Baru</div>
                                </div>
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-center">
                                    <div class="text-2xl font-bold text-yellow-600">{{ $importCounts['total_existing'] ?? 0 }}</div>
                                    <div class="text-xs text-yellow-700">Update Data</div>
                                </div>
                            </div>

                            <!-- Important Notes -->
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                                <h4 class="font-medium text-amber-900 mb-2">Penting:</h4>
                                <ul class="text-xs text-amber-800 space-y-1">
                                    <li>• Password default: <code class="bg-amber-100 px-1 rounded">password123</code></li>
                                    <li>• Peran default: Staff</li>
                                    <li>• Hanya data dengan status aktif, email, dan NIP yang diimport</li>
                                </ul>
                                
                                <div class="mt-3 pt-3 border-t border-amber-200">
                                    <h5 class="font-medium text-amber-900 mb-2">Command CLI:</h5>
                                    <div class="bg-amber-100 rounded p-2">
                                        <code class="text-xs text-amber-900 font-mono break-all">php artisan users:import</code>
                                    </div>
                                    <p class="text-xs text-amber-700 mt-1">
                                        Command ini dapat dijalankan via terminal untuk import pengguna dari data karyawan dan dosen yang aktif, memiliki email, dan NIP.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="border-t border-gray-200 p-4 bg-gray-50">
                    <div class="flex space-x-2">
                        <button wire:click="closeModal" 
                                class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 text-sm font-medium transition-colors">
                            Batal
                        </button>
                        <button wire:click="importUsers" 
                                wire:loading.attr="disabled"
                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove>Mulai Import</span>
                            <span wire:loading>Memproses...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
