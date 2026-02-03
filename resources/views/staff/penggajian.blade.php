@extends('layouts.app')

@section('breadcrumb')
    <nav class="flex overflow-x-auto pb-1 invisible-scrollbar" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2 whitespace-nowrap">
            <li class="inline-flex items-center">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors">
                    <x-lucide-home class="w-4 h-4 sm:mr-2" />
                    <span class="hidden sm:inline">Dashboard</span>
                </a>
                <x-lucide-chevron-right class="w-4 h-4 text-gray-400 mx-1 sm:mx-2" />
            </li>
            <li>
                <span class="text-sm font-semibold text-gray-900">
                    Penggajian
                </span>
            </li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Info Card --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 sm:p-6">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-xs sm:text-sm font-medium text-blue-800">Informasi Slip Gaji</h3>
                <p class="mt-1 text-xs sm:text-sm text-blue-700">
                    Berikut adalah ringkasan penggajian Anda. Slip gaji yang sudah tersedia dapat diunduh dalam format PDF.
                    Jika slip gaji belum tersedia, silakan hubungi bagian SDM.
                </p>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    @if($availableSlips > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6">
        <!-- Gaji Bersih Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-5 lg:p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-gray-600 truncate">Total Gaji Bersih</p>
                    <p class="text-lg sm:text-xl lg:text-2xl font-bold text-green-600 mt-1 truncate">
                        Rp {{ number_format($totalGajiBersih, 0, ',', '.') }}
                    </p>
                    <p class="text-[10px] sm:text-xs text-gray-500 mt-1">
                        Dari {{ $availableSlips }} slip
                    </p>
                </div>
                <div class="flex-shrink-0 ml-3">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Potongan Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-5 lg:p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-gray-600 truncate">Total Potongan</p>
                    <p class="text-lg sm:text-xl lg:text-2xl font-bold text-red-600 mt-1 truncate">
                        Rp {{ number_format($totalPotongan, 0, ',', '.') }}
                    </p>
                    <p class="text-[10px] sm:text-xs text-gray-500 mt-1">
                        Total semua potongan
                    </p>
                </div>
                <div class="flex-shrink-0 ml-3">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Honor Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-5 lg:p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-gray-600 truncate">Total Honor</p>
                    <p class="text-lg sm:text-xl lg:text-2xl font-bold text-blue-600 mt-1 truncate">
                        Rp {{ number_format($totalHonor, 0, ',', '.') }}
                    </p>
                    <p class="text-[10px] sm:text-xs text-gray-500 mt-1 truncate">
                        Honor + tunai + insentif
                    </p>
                </div>
                <div class="flex-shrink-0 ml-3">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Pajak Kurang Potong Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-5 lg:p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-gray-600 truncate">Pajak Kurang Potong</p>
                    <p class="text-lg sm:text-xl lg:text-2xl font-bold text-yellow-600 mt-1 truncate">
                        Rp {{ number_format($totalPajakKurangPotong, 0, ',', '.') }}
                    </p>
                    <p class="text-[10px] sm:text-xs text-gray-500 mt-1 truncate">
                        PPh 21 kurang dipotong
                    </p>
                </div>
                <div class="flex-shrink-0 ml-3">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
        <form method="GET" action="{{ route('staff.penggajian.index') }}" class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        class="block w-full pl-9 sm:pl-10 pr-3 py-2 sm:py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-sm"
                        placeholder="Cari berdasarkan periode..."
                    />
                </div>
            </div>
            <button type="submit" class="hidden">Search</button>
        </form>
    </div>

    <!-- Slip Gaji List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="overflow-hidden">
            @if($slipGaji->count() > 0)
                <!-- Desktop Table View -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gaji Bersih</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Potongan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Honor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pajak Kurang Potong</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($slipGaji as $index => $slip)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $pagination->firstItem() + $index }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $slip['period_name'] }}</div>
                                    <div class="text-sm text-gray-500">{{ $slip['period'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(isset($slip['gaji_bersih']) && $slip['gaji_bersih'] > 0)
                                        <div class="text-sm font-medium text-green-600">
                                            Rp {{ number_format($slip['gaji_bersih'], 0, ',', '.') }}
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(isset($slip['total_potongan']) && $slip['total_potongan'] > 0)
                                        <div class="text-sm font-medium text-red-600">
                                            Rp {{ number_format($slip['total_potongan'], 0, ',', '.') }}
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(isset($slip['total_honor']) && $slip['total_honor'] > 0)
                                        <div class="text-sm font-medium text-blue-600">
                                            Rp {{ number_format($slip['total_honor'], 0, ',', '.') }}
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(isset($slip['pajak_kurang_potong']) && $slip['pajak_kurang_potong'] > 0)
                                        <div class="text-sm font-medium text-yellow-600">
                                            Rp {{ number_format($slip['pajak_kurang_potong'], 0, ',', '.') }}
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($slip['can_download'])
                                        <a href="{{ route('staff.penggajian.download-pdf', $slip['header_id']) }}" 
                                           class="inline-flex items-center px-3 py-1 border border-green-300 text-sm leading-4 font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" 
                                           title="Download PDF">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </a>
                                    @else
                                        <span class="text-sm text-gray-500 flex items-center justify-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            Tidak Tersedia
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="lg:hidden divide-y divide-gray-200">
                    @foreach($slipGaji as $index => $slip)
                    <div class="p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-800">
                                        #{{ $pagination->firstItem() + $index }}
                                    </span>
                                    <h3 class="text-sm font-bold text-gray-900 truncate">{{ $slip['period_name'] }}</h3>
                                </div>
                                <p class="text-xs text-gray-500">{{ $slip['period'] }}</p>
                            </div>
                            @if($slip['can_download'])
                                <a href="{{ route('staff.penggajian.download-pdf', $slip['header_id']) }}" 
                                   class="flex-shrink-0 ml-3 inline-flex items-center justify-center w-10 h-10 border border-green-300 rounded-lg text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" 
                                   title="Download PDF">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </a>
                            @endif
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wider mb-1">Gaji Bersih</p>
                                @if(isset($slip['gaji_bersih']) && $slip['gaji_bersih'] > 0)
                                    <p class="text-sm font-bold text-green-600 truncate">Rp {{ number_format($slip['gaji_bersih'], 0, ',', '.') }}</p>
                                @else
                                    <p class="text-sm text-gray-400">-</p>
                                @endif
                            </div>
                            <div>
                                <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wider mb-1">Potongan</p>
                                @if(isset($slip['total_potongan']) && $slip['total_potongan'] > 0)
                                    <p class="text-sm font-bold text-red-600 truncate">Rp {{ number_format($slip['total_potongan'], 0, ',', '.') }}</p>
                                @else
                                    <p class="text-sm text-gray-400">-</p>
                                @endif
                            </div>
                            <div>
                                <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wider mb-1">Honor</p>
                                @if(isset($slip['total_honor']) && $slip['total_honor'] > 0)
                                    <p class="text-sm font-bold text-blue-600 truncate">Rp {{ number_format($slip['total_honor'], 0, ',', '.') }}</p>
                                @else
                                    <p class="text-sm text-gray-400">-</p>
                                @endif
                            </div>
                            <div>
                                <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wider mb-1">Pajak K.P.</p>
                                @if(isset($slip['pajak_kurang_potong']) && $slip['pajak_kurang_potong'] > 0)
                                    <p class="text-sm font-bold text-yellow-600 truncate">Rp {{ number_format($slip['pajak_kurang_potong'], 0, ',', '.') }}</p>
                                @else
                                    <p class="text-sm text-gray-400">-</p>
                                @endif
                            </div>
                        </div>
                        
                        @if(!$slip['can_download'])
                            <div class="mt-3 pt-3 border-t border-gray-100">
                                <p class="text-xs text-gray-500 flex items-center">
                                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    PDF tidak tersedia
                                </p>
                            </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 sm:py-12">
                    <div class="text-gray-500">
                        <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto mb-3 sm:mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-2">Belum Ada Slip Gaji</h3>
                        <p class="text-sm sm:text-base text-gray-500 px-4">Slip gaji Anda belum tersedia. Silakan hubungi bagian SDM untuk informasi lebih lanjut.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Pagination -->
    @if($slipGaji->count() > 0 && $pagination)
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6 rounded-b-lg">
            {{ $pagination->links() }}
        </div>
    @endif
</div>
@endsection
