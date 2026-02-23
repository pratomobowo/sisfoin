<div class="space-y-6" wire:poll.15s>
    <x-page-header 
        title="Data Dosen" 
        subtitle="Kelola data dosen dan informasi akademik"
        :breadcrumbs="['Dashboard' => route('dashboard'), 'SDM' => '#', 'Dosen' => route('sdm.dosens.index')]"
    >
        <x-slot name="actions">
            <x-button variant="success" wire:click="syncSevima" wire:loading.attr="disabled" :loading="$isSyncing">
                <x-lucide-refresh-cw wire:loading.remove class="h-4 w-4 mr-2" />
                <span wire:loading.remove>Sinkronisasi Data Sevima</span>
                <span wire:loading>Memproses...</span>
            </x-button>
        </x-slot>
    </x-page-header>

    <!-- Sync Progress -->
    @if($isSyncing)
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-blue-900">Sinkronisasi Berlangsung</h3>
                <span class="text-sm text-blue-600">{{ $syncProgress }}%</span>
            </div>
            
            <!-- Progress Bar -->
            <div class="w-full bg-blue-200 rounded-full h-3 mb-4">
                <div class="bg-blue-600 h-3 rounded-full transition-all duration-300" style="width: {{ $syncProgress }}%"></div>
            </div>
            
            <p class="text-sm text-blue-700">{{ $syncMessage }}</p>
        </div>
    @endif

    @if(!$isSyncing && !empty($syncMessage))
        <div class="rounded-xl p-4 border
            @if(str_contains(strtolower($syncMessage), 'gagal')) bg-red-50 border-red-200 text-red-700
            @elseif($syncProgress === 100) bg-green-50 border-green-200 text-green-700
            @else bg-blue-50 border-blue-200 text-blue-700 @endif">
            <div class="flex items-start gap-2">
                <x-lucide-info class="w-4 h-4 mt-0.5" />
                <div>
                    <p class="text-sm font-medium">{{ $syncMessage }}</p>
                    <p class="text-xs opacity-80 mt-1">Status detail ada di panel "Status Sinkronisasi Terakhir" di bawah dan akan refresh otomatis.</p>
                </div>
            </div>
        </div>
    @endif

    @if($latestSyncRun)
        <div class="bg-white border border-gray-200 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Status Sinkronisasi Terakhir</h3>
                <span class="text-xs px-2.5 py-1 rounded-full
                    @if($latestSyncRun->status === 'completed') bg-green-100 text-green-700
                    @elseif($latestSyncRun->status === 'completed_with_warning') bg-yellow-100 text-yellow-800
                    @elseif($latestSyncRun->status === 'failed') bg-red-100 text-red-700
                    @elseif($latestSyncRun->status === 'running') bg-blue-100 text-blue-700
                    @else bg-gray-100 text-gray-700 @endif">
                    {{ strtoupper(str_replace('_', ' ', $latestSyncRun->status)) }}
                </span>
            </div>

            <div class="flex flex-wrap gap-2 mb-4 text-xs">
                <span class="px-2 py-1 rounded bg-green-100 text-green-700">COMPLETED = bersih</span>
                <span class="px-2 py-1 rounded bg-yellow-100 text-yellow-800">COMPLETED WITH WARNING = ada warning/error</span>
                <span class="px-2 py-1 rounded bg-red-100 text-red-700">FAILED = proses gagal</span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-sm mb-4">
                <div class="bg-gray-50 rounded-lg p-3"><p class="text-gray-500">Fetched</p><p class="font-semibold">{{ $latestSyncRun->fetched_count }}</p></div>
                <div class="bg-gray-50 rounded-lg p-3"><p class="text-gray-500">Processed</p><p class="font-semibold">{{ $latestSyncRun->processed_count }}</p></div>
                <div class="bg-gray-50 rounded-lg p-3"><p class="text-gray-500">Inserted</p><p class="font-semibold text-green-700">{{ $latestSyncRun->inserted_count }}</p></div>
                <div class="bg-gray-50 rounded-lg p-3"><p class="text-gray-500">Updated</p><p class="font-semibold">{{ $latestSyncRun->updated_count }}</p></div>
                <div class="bg-gray-50 rounded-lg p-3"><p class="text-gray-500">Failed</p><p class="font-semibold text-red-700">{{ $latestSyncRun->failed_count }}</p></div>
            </div>

            @if(($latestSyncRun->error_summary['reconcile']['linked_count'] ?? 0) || ($latestSyncRun->error_summary['reconcile']['conflict_count'] ?? 0))
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-blue-900 font-medium">Ringkasan Rekonsiliasi User Link</p>
                    <div class="mt-2 flex flex-wrap gap-4 text-sm">
                        <span class="text-blue-700">Linked: <strong>{{ $latestSyncRun->error_summary['reconcile']['linked_count'] ?? 0 }}</strong></span>
                        <span class="text-red-700">Conflict: <strong>{{ $latestSyncRun->error_summary['reconcile']['conflict_count'] ?? 0 }}</strong></span>
                    </div>
                </div>
            @endif

            @if($latestSyncRunItems->isNotEmpty())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-sm font-semibold text-red-900 mb-2">Detail Warning/Error</p>
                    <div class="space-y-1 max-h-40 overflow-y-auto">
                        @foreach($latestSyncRunItems as $item)
                            <p class="text-sm {{ $item->level === 'warning' ? 'text-amber-700' : 'text-red-700' }}">• [{{ strtoupper($item->entity_type) }}][{{ strtoupper($item->level) }}] {{ $item->message }}</p>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Sync Results -->
    @if(!empty($syncResults))
        <div class="bg-green-50 border border-green-200 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-green-900">Hasil Sinkronisasi</h3>
                <span class="text-sm text-green-600">Selesai dalam {{ $syncResults['duration'] }} detik</span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Dosen Results -->
                <div class="bg-white rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-3">Data Dosen</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Total dari API:</span>
                            <span class="font-medium">{{ $syncResults['dosen']['total_api'] }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Berhasil diproses:</span>
                            <span class="font-medium text-green-600">{{ $syncResults['dosen']['total_processed'] }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Berhasil disimpan:</span>
                            <span class="font-medium text-green-600">{{ $syncResults['dosen']['total_inserted'] }}</span>
                        </div>
                        @if($syncResults['dosen']['total_errors'] > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Error:</span>
                                <span class="font-medium text-red-600">{{ $syncResults['dosen']['total_errors'] }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Summary Results -->
                <div class="bg-white rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-3">Ringkasan</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Total Data:</span>
                            <span class="font-medium">{{ $syncResults['dosen']['total_api'] }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Success Rate:</span>
                            <span class="font-medium text-green-600">
                                @if($syncResults['dosen']['total_api'] > 0)
                                    {{ round(($syncResults['dosen']['total_inserted'] / $syncResults['dosen']['total_api']) * 100, 1) }}%
                                @else
                                    0%
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Waktu Eksekusi:</span>
                            <span class="font-medium">{{ $syncResults['duration'] }} detik</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Status:</span>
                            <span class="font-medium text-green-600">Selesai</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Error Details -->
            @if(!empty($syncResults['dosen']['errors']))
                <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4">
                    <h4 class="font-semibold text-red-900 mb-2">Detail Error</h4>
                    <div class="max-h-32 overflow-y-auto space-y-1">
                        @foreach($syncResults['dosen']['errors'] as $error)
                            <p class="text-sm text-red-700">• {{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <!-- Search -->
            <div class="md:col-span-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-lucide-search class="w-4 h-4 text-gray-400" />
                    </div>
                    <input wire:model.live.debounce.300ms="search"
                           type="text"
                           placeholder="Cari nama, NIP, NIDN, email..."
                           class="w-full pl-10 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Satuan Kerja Filter -->
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Satuan Kerja</label>
                <select wire:model.live="filterSatuanKerja"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Satuan Kerja</option>
                    @foreach($satuanKerjaList as $satuanKerja)
                        <option value="{{ $satuanKerja }}">{{ $satuanKerja }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Aktif Filter -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status Aktif</label>
                <select wire:model.live="filterStatusAktif"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Status</option>
                    <option value="Aktif">Aktif</option>
                    <option value="Tidak Aktif">Tidak Aktif</option>
                    <option value="Mengundurkan diri">Mengundurkan diri</option>
                    <option value="Kontrak Habis">Kontrak Habis</option>
                    <option value="Pensiun Dini">Pensiun Dini</option>
                </select>
            </div>
            
            <!-- Reset Filter Button -->
            <div class="md:col-span-1 flex items-end">
                <button wire:click="resetFilters"
                        class="w-10 h-10 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition-colors flex items-center justify-center"
                        title="Reset Filter">
                    <x-lucide-rotate-ccw class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>


    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Dosen
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Identitas
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Satuan Kerja
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Jabatan
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($dosens as $dosen)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $dosen->nama_lengkap_with_gelar }}</div>
                                    <div class="text-sm text-gray-500">{{ $dosen->email_kampus ?? $dosen->email }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $dosen->nip ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    @if($dosen->satuan_kerja && strlen($dosen->satuan_kerja) > 12)
                                        {{ substr($dosen->satuan_kerja, 0, 12) }}...
                                    @else
                                        {{ $dosen->satuan_kerja ?: '-' }}
                                    @endif
                                </div>
                                <div class="text-sm text-gray-500">{{ $dosen->home_base ?: '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $dosen->jabatan_fungsional ?: $dosen->jabatan_struktural ?: '-' }}</div>
                                <div class="text-sm text-gray-500">{{ $dosen->status_kepegawaian ?: '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($dosen->status_aktif === 'Aktif') bg-green-100 text-green-800
                                    @elseif($dosen->status_aktif === 'Tidak Aktif') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ $dosen->status_aktif }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end">
                                    <!-- View -->
                                    <button wire:click="view({{ $dosen->id }})" 
                                            title="Lihat Detail" aria-label="Lihat Detail"
                                            class="inline-flex p-2 rounded-lg hover:bg-gray-100 text-blue-600">
                                        <x-lucide-eye class="w-5 h-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <x-lucide-users class="w-12 h-12 text-gray-400 mb-4" />
                                    <h3 class="text-sm font-medium text-gray-900 mb-1">Tidak ada data dosen ditemukan</h3>
                                    <p class="text-sm text-gray-500">Coba ubah filter pencarian atau sinkronisasi data dari Sevima</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($dosens->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-white">
                <x-superadmin.pagination 
                    :currentPage="$dosens->currentPage()"
                    :lastPage="$dosens->lastPage()"
                    :total="$dosens->total()"
                    :perPage="$dosens->perPage()"
                    :showPageInfo="true"
                    :showPerPage="true"
                    :perPageOptions="[10, 25, 50, 100]"
                    :alignment="'justify-between'"
                    perPageWireModel="perPage"
                    previousPageWireModel="previousPage"
                    nextPageWireModel="nextPage"
                    gotoPageWireModel="gotoPage" />
            </div>
        @endif
    </div>

    <!-- View Modal -->
    @if($showViewModal && $viewDosen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeViewModal"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Detail Data Dosen
                            </h3>
                            <button type="button" wire:click="closeViewModal" class="text-gray-400 hover:text-gray-600">
                                <x-lucide-x class="w-6 h-6" />
                            </button>
                        </div>

                        <div class="max-h-[70vh] overflow-y-auto">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Personal Information -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Informasi Personal</h4>
                                    <div class="space-y-2">
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Nama Lengkap:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->nama_lengkap_with_gelar }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">NIP:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->nip ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">NIDN:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->nidn ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">NIK:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->nik ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Jenis Kelamin:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->jenis_kelamin === 'L' ? 'Laki-laki' : ($viewDosen->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Tempat, Tanggal Lahir:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->tempat_lahir ? $viewDosen->tempat_lahir . ', ' : '' }}{{ $viewDosen->tanggal_lahir?->format('d M Y') ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Agama:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->agama ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Status Perkawinan:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->status_nikah ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Kewarganegaraan:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->kewarganegaraan ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Golongan Darah:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->golongan_darah ?: '-' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Academic Information -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Informasi Akademik</h4>
                                    <div class="space-y-2">
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Satuan Kerja:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->satuan_kerja ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Home Base:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->home_base ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Jabatan Fungsional:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->jabatan_fungsional ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Jabatan Struktural:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->jabatan_struktural ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Status Kepegawaian:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->status_kepegawaian ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Jenis Pegawai:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->jenis_pegawai ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Tanggal Masuk:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->tanggal_masuk?->format('d M Y') ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Tanggal Keluar:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->tanggal_keluar?->format('d M Y') ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Status Aktif:</span>
                                            <p class="text-sm text-gray-900">
                                                @if($viewDosen->status_text === 'Aktif')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                        {{ $viewDosen->status_text }}
                                                    </span>
                                                @elseif($viewDosen->status_text === 'Tidak Aktif')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                        {{ $viewDosen->status_text }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        {{ $viewDosen->status_text ?: '-' }}
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Tanggal Sertifikasi:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->tanggal_sertifikasi_dosen?->format('d M Y') ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Pangkat:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->pangkat ?: '-' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Information -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Informasi Kontak</h4>
                                    <div class="space-y-2">
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Email:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->email ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Email Kampus:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->email_kampus ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Telepon:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->telepon ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">HP:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->nomor_hp ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Telepon Kantor:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->telepon_kantor ?: '-' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Address Information -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Alamat</h4>
                                    <div class="space-y-3">
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Alamat Domisili:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->alamat_domisili ?: '-' }}</p>
                                            @if($viewDosen->kota_domisili || $viewDosen->provinsi_domisili || $viewDosen->kode_pos_domisili)
                                                <p class="text-xs text-gray-600">
                                                    {{ $viewDosen->kota_domisili }}, {{ $viewDosen->provinsi_domisili }} {{ $viewDosen->kode_pos_domisili }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Education Information -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Pendidikan</h4>
                                    <div class="space-y-2">
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Pendidikan Terakhir:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->pendidikan_terakhir ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Jurusan:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->jurusan ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Universitas:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->universitas ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Tahun Lulus:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->tahun_lulus ?: '-' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Banking Information -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Informasi Perbankan</h4>
                                    <div class="space-y-2">
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Nama Bank:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->nama_bank ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Nomor Rekening:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->nomor_rekening ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Nama Rekening:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->nama_rekening ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">BPJS Kesehatan:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->bpjs_kesehatan ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">BPJS Ketenagakerjaan:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->bpjs_ketenagakerjaan ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">NPWP:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->npwp ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Status Pajak:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->status_pajak ?: '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="closeViewModal" 
                                class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
