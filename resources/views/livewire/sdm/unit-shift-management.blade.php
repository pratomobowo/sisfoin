<div class="space-y-6">
    <!-- Header -->
    <x-page-header 
        title="Kelola Shift Kerja" 
        subtitle="Kelola pengecualian shift untuk unit atau pegawai tertentu. Jika tidak didaftarkan, berlaku jam kerja global."
        :breadcrumbs="['Biro SDM' => '#', 'Kelola Shift Unit' => route('sdm.absensi.kelola-shift')]"
    />

    <!-- Search & Add -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-end">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Cari Unit Terdaftar</label>
                <div class="relative">
                    <input type="text" 
                        wire:model.live.debounce.300ms="search"
                        placeholder="Contoh: Biro Akademik..."
                        class="w-full border border-gray-200 rounded-lg pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-blue-500">
                    <div class="absolute left-3 top-3 text-gray-400">
                        <x-lucide-search class="w-4 h-4" />
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Mulai Kelola Unit Baru</label>
                <div class="flex gap-2">
                    <div class="flex-1">
                        <select wire:model="selectedUnitToAdd" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Unit...</option>
                            @foreach($this->allAvailableUnits as $unitName)
                                <option value="{{ $unitName }}">{{ $unitName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button wire:click="addUnit" class="inline-flex items-center px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold shadow-sm transition-all whitespace-nowrap">
                        Kelola
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Unit Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($this->units as $unit)
            <a href="{{ route('sdm.absensi.unit-detail', ['unit' => $unit['slug']]) }}" 
               class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-lg hover:border-blue-200 transition-all group flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-lg mb-4 group-hover:scale-110 transition-transform flex-shrink-0">
                            {{ strtoupper(substr($unit['name'], 0, 1)) }}
                        </div>
                        <h3 class="font-bold text-gray-900 group-hover:text-blue-600 transition-colors break-words leading-tight">{{ $unit['name'] }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $unit['employee_count'] }} pegawai terdaftar</p>
                    </div>
                    <div class="mt-1 flex-shrink-0">
                        <x-lucide-chevron-right class="w-5 h-5 text-gray-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all" />
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <x-lucide-building-2 class="w-12 h-12 mx-auto text-gray-300 mb-4" />
                <p class="font-medium text-gray-500">Tidak ada unit ditemukan</p>
            </div>
        @endforelse
    </div>

    <!-- Info -->
    <div class="bg-slate-800 rounded-xl p-5 text-white">
        <h4 class="font-bold mb-3 flex items-center">
            <x-lucide-info class="w-5 h-5 mr-2 text-blue-400" />
            Cara Penggunaan
        </h4>
        <div class="text-sm text-slate-300">
            <ol class="list-decimal list-inside space-y-1">
                <li>Klik unit kerja yang ingin dikelola</li>
                <li>Di halaman detail, tambahkan assignment shift untuk setiap pegawai</li>
                <li>Set tanggal mulai dan selesai untuk setiap assignment</li>
            </ol>
        </div>
    </div>
</div>
