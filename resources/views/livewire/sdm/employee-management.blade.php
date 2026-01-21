<div class="space-y-6">
    <x-page-header 
        title="Data Karyawan" 
        subtitle="Kelola data karyawan dan informasi kepegawaian"
        :breadcrumbs="['Dashboard' => route('dashboard'), 'SDM' => '#', 'Karyawan' => route('sdm.employees.index')]"
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

    <!-- Sync Results -->
    @if(!empty($syncResults))
        <div class="bg-green-50 border border-green-200 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-green-900">Hasil Sinkronisasi</h3>
                <span class="text-sm text-green-600">Selesai dalam {{ $syncResults['duration'] }} detik</span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Pegawai Results -->
                <div class="bg-white rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-3">Data Pegawai</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Total dari API:</span>
                            <span class="font-medium">{{ $syncResults['pegawai']['total_api'] }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Berhasil diproses:</span>
                            <span class="font-medium text-green-600">{{ $syncResults['pegawai']['total_processed'] }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Berhasil disimpan:</span>
                            <span class="font-medium text-green-600">{{ $syncResults['pegawai']['total_inserted'] }}</span>
                        </div>
                        @if($syncResults['pegawai']['total_errors'] > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Error:</span>
                                <span class="font-medium text-red-600">{{ $syncResults['pegawai']['total_errors'] }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                
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
            </div>
            
            <!-- Error Details -->
            @if(!empty($syncResults['pegawai']['errors']) || !empty($syncResults['dosen']['errors']))
                <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4">
                    <h4 class="font-semibold text-red-900 mb-2">Detail Error</h4>
                    <div class="max-h-32 overflow-y-auto space-y-1">
                        @foreach($syncResults['pegawai']['errors'] as $error)
                            <p class="text-sm text-red-700">• Pegawai: {{ $error }}</p>
                        @endforeach
                        @foreach($syncResults['dosen']['errors'] as $error)
                            <p class="text-sm text-red-700">• Dosen: {{ $error }}</p>
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
                           placeholder="Cari nama, NIP, NIK, email..."
                           class="w-full pl-10 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Unit Kerja Filter -->
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Unit Kerja</label>
                <select wire:model.live="filterUnitKerja"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Unit Kerja</option>
                    @foreach($unitKerjaList as $unit)
                        <option value="{{ $unit }}">{{ $unit }}</option>
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
                            Karyawan
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Identitas
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Unit Kerja
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
                    @forelse($employees as $employee)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $employee->nama_lengkap_with_gelar }}</div>
                                    <div class="text-sm text-gray-500">{{ $employee->email ?? $employee->email_kampus }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $employee->nip }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    @if($employee->satuan_kerja && strlen($employee->satuan_kerja) > 12)
                                        {{ substr($employee->satuan_kerja, 0, 12) }}...
                                    @else
                                        {{ $employee->satuan_kerja ?: '-' }}
                                    @endif
                                </div>
                                <div class="text-sm text-gray-500">{{ $employee->fakultas ?: '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $employee->jabatan_struktural ?: $employee->jabatan_fungsional ?: '-' }}</div>
                                <div class="text-sm text-gray-500">{{ $employee->pangkat ?: '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($employee->status_aktif === 'Aktif') bg-green-100 text-green-800
                                    @elseif($employee->status_aktif === 'Tidak Aktif') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ $employee->status_aktif }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end">
                                    <!-- View -->
                                    <button wire:click="view({{ $employee->id }})" 
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
                                    <h3 class="text-sm font-medium text-gray-900 mb-1">Tidak ada data karyawan ditemukan</h3>
                                    <p class="text-sm text-gray-500">Coba ubah filter pencarian atau sinkronisasi data dari Sevima</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($employees->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-white">
                <x-superadmin.pagination 
                    :currentPage="$employees->currentPage()"
                    :lastPage="$employees->lastPage()"
                    :total="$employees->total()"
                    :perPage="$employees->perPage()"
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
    @if($showViewModal && $viewEmployee)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeViewModal"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Detail Data Karyawan
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
                                            <p class="text-sm text-gray-900">{{ $viewEmployee->nama_lengkap_with_gelar }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Nama Panggilan:</span>
                                            <p class="text-sm text-gray-900">-</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Gelar Depan:</span>
                                            <p class="text-sm text-gray-900">{{ $viewEmployee->gelar_depan ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Gelar Belakang:</span>
                                            <p class="text-sm text-gray-900">{{ $viewEmployee->gelar_belakang ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">NIK:</span>
                                            <p class="text-sm text-gray-900">{{ $viewEmployee->nik ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Jenis Kelamin:</span>
                                            <p class="text-sm text-gray-900">{{ $viewEmployee->jenis_kelamin === 'L' ? 'Laki-laki' : ($viewEmployee->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Tempat, Tanggal Lahir:</span>
                                            <p class="text-sm text-gray-900">{{ $viewEmployee->tempat_lahir ? $viewEmployee->tempat_lahir . ', ' : '' }}{{ $viewEmployee->tanggal_lahir ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Agama:</span>
                                            <p class="text-sm text-gray-900">{{ $viewEmployee->agama ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Status Perkawinan:</span>
                                            <p class="text-sm text-gray-900">{{ $viewEmployee->status_nikah === 'S' ? 'Single' : ($viewEmployee->status_nikah === 'M' ? 'Menikah' : ($viewEmployee->status_nikah === 'D' ? 'Duda' : ($viewEmployee->status_nikah === 'J' ? 'Janda' : '-'))) }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Kewarganegaraan:</span>
                                            <p class="text-sm text-gray-900">{{ $viewEmployee->kewarganegaraan ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Golongan Darah:</span>
                                            <p class="text-sm text-gray-900">-</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Employment Information -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Informasi Kepegawaian</h4>
                                    <div class="space-y-2">
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">NIP:</span>
                                            <p class="text-sm text-gray-900">{{ $viewEmployee->nip ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Status Kepegawaian:</span>
                                            <p class="text-sm text-gray-900">{{ $viewEmployee->status_kepegawaian ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Jenis Pegawai:</span>
                                            <p class="text-sm text-gray-900">-</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Tanggal Masuk:</span>
                                            <p class="text-sm text-gray-900">{{ $viewEmployee->tanggal_masuk ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Tanggal Keluar:</span>
                                            <p class="text-sm text-gray-900">-</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Status Aktif:</span>
                                            <p class="text-sm text-gray-900">
                                                @if($viewEmployee->status_aktif === 'Aktif')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                        {{ $viewEmployee->status_aktif }}
                                                    </span>
                                                @elseif($viewEmployee->status_aktif === 'Tidak Aktif')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                        {{ $viewEmployee->status_aktif }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        {{ $viewEmployee->status_aktif ?: '-' }}
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Pangkat:</span>
                                            <p class="text-sm text-gray-900">-</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Jabatan Struktural:</span>
                                            <p class="text-sm text-gray-900">{{ $viewEmployee->jabatan_struktural ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Jabatan Fungsional:</span>
                                            <p class="text-sm text-gray-900">{{ $viewEmployee->jabatan_fungsional ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Unit Kerja:</span>
                                            <p class="text-sm text-gray-900">{{ $viewEmployee->satuan_kerja ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Fakultas:</span>
                                            <p class="text-sm text-gray-900">-</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Prodi:</span>
                                            <p class="text-sm text-gray-900">-</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Information -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Informasi Kontak</h4>
                                    <div class="space-y-2">
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Email:</span>
                                            <p class="text-sm text-gray-900">{{ $viewEmployee->email ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Email Kampus:</span>
                                            <p class="text-sm text-gray-900">{{ $viewEmployee->email_kampus ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Telepon:</span>
                                            <p class="text-sm text-gray-900">{{ $viewEmployee->telepon ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">HP:</span>
                                            <p class="text-sm text-gray-900">{{ $viewEmployee->hp ?: '-' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Address Information -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Alamat</h4>
                                    <div class="space-y-3">
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Alamat KTP:</span>
                                            <p class="text-sm text-gray-900">{{ $viewEmployee->alamat_ktp ?: '-' }}</p>
                                            @if($viewEmployee->rt_ktp || $viewEmployee->rw_ktp || $viewEmployee->kelurahan_ktp || $viewEmployee->kecamatan_ktp || $viewEmployee->kota_ktp || $viewEmployee->provinsi_ktp || $viewEmployee->kode_pos_ktp)
                                                <p class="text-xs text-gray-600">
                                                    RT{{ $viewEmployee->rt_ktp }} RW{{ $viewEmployee->rw_ktp }}, {{ $viewEmployee->kelurahan_ktp }}, {{ $viewEmployee->kecamatan_ktp }}, {{ $viewEmployee->kota_ktp }}, {{ $viewEmployee->provinsi_ktp }} {{ $viewEmployee->kode_pos_ktp }}
                                                </p>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Alamat Domisili:</span>
                                            <p class="text-sm text-gray-900">{{ $viewEmployee->alamat_domisili ?: '-' }}</p>
                                            @if($viewEmployee->rt_domisili || $viewEmployee->rw_domisili || $viewEmployee->kelurahan_domisili || $viewEmployee->kecamatan_domisili || $viewEmployee->kota_domisili || $viewEmployee->provinsi_domisili || $viewEmployee->kode_pos_domisili)
                                                <p class="text-xs text-gray-600">
                                                    RT{{ $viewEmployee->rt_domisili }} RW{{ $viewEmployee->rw_domisili }}, {{ $viewEmployee->kelurahan_domisili }}, {{ $viewEmployee->kecamatan_domisili }}, {{ $viewEmployee->kota_domisili }}, {{ $viewEmployee->provinsi_domisili }} {{ $viewEmployee->kode_pos_domisili }}
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
                                            <p class="text-sm text-gray-900">{{ $viewEmployee->pendidikan_terakhir ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Jurusan:</span>
                                            <p class="text-sm text-gray-900">-</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Universitas:</span>
                                            <p class="text-sm text-gray-900">-</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Tahun Lulus:</span>
                                            <p class="text-sm text-gray-900">-</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Banking Information -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Informasi Perbankan</h4>
                                    <div class="space-y-2">
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Nama Bank:</span>
                                            <p class="text-sm text-gray-900">-</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Nomor Rekening:</span>
                                            <p class="text-sm text-gray-900">-</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Nama Rekening:</span>
                                            <p class="text-sm text-gray-900">-</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">BPJS Kesehatan:</span>
                                            <p class="text-sm text-gray-900">-</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">BPJS Ketenagakerjaan:</span>
                                            <p class="text-sm text-gray-900">-</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">NPWP:</span>
                                            <p class="text-sm text-gray-900">-</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Status Pajak:</span>
                                            <p class="text-sm text-gray-900">-</p>
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
