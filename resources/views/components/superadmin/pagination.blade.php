@props([
    'paginator' => null,
    'currentPage' => 1,           // Current page number
    'lastPage' => 1,             // Total pages
    'total' => 0,                // Total records
    'perPage' => 10,             // Records per page
    'perPageOptions' => [10], // Available per page options
    'showPageInfo' => true,      // Show "Showing X to Y of Z"
    'showPerPage' => true,        // Show per page dropdown
    'alignment' => 'justify-between', // Tailwind alignment classes
    // Wire model names untuk Livewire integration
    'perPageWireModel' => 'perPage',
    'previousPageWireModel' => 'previousPage',
    'nextPageWireModel' => 'nextPage',
    'gotoPageWireModel' => 'gotoPage',
])

@php
if ($paginator) {
    $currentPage = $paginator->currentPage();
    $lastPage = $paginator->lastPage();
    $total = method_exists($paginator, 'total') ? $paginator->total() : $paginator->count();
    $perPage = $paginator->perPage();
}

// Calculate from and to for page info
$from = $total > 0 ? (($currentPage - 1) * $perPage + 1) : 0;
$to = $total > 0 ? min($currentPage * $perPage, $total) : 0;

// Generate page numbers with ellipsis for large page counts
$pageNumbers = [];
$maxPageLinks = 5;

if ($lastPage <= $maxPageLinks) {
    // Show all pages if total pages <= max links
    $pageNumbers = range(1, $lastPage);
} else {
    // Show limited pages with ellipsis
    if ($currentPage <= floor($maxPageLinks / 2) + 1) {
        // Near the beginning
        $pageNumbers = range(1, $maxPageLinks);
        if ($lastPage > $maxPageLinks) {
            array_pop($pageNumbers);
            $pageNumbers[] = '...';
            $pageNumbers[] = $lastPage;
        }
    } elseif ($currentPage >= $lastPage - floor($maxPageLinks / 2)) {
        // Near the end
        if ($lastPage > $maxPageLinks) {
            $pageNumbers = [1, '...'];
            $pageNumbers = array_merge($pageNumbers, range($lastPage - $maxPageLinks + 2, $lastPage));
        } else {
            $pageNumbers = range(1, $lastPage);
        }
    } else {
        // In the middle
        $pageNumbers = [1, '...'];
        $middleStart = $currentPage - floor($maxPageLinks / 2) + 1;
        $middleEnd = $currentPage + floor($maxPageLinks / 2) - 1;
        $pageNumbers = array_merge($pageNumbers, range($middleStart, $middleEnd));
        if ($lastPage > $maxPageLinks) {
            $pageNumbers[] = '...';
            $pageNumbers[] = $lastPage;
        }
    }
}

// Ensure perPageOptions is an array
if (is_string($perPageOptions)) {
    // Handle JSON string format
    $perPageOptions = json_decode($perPageOptions, true) ?? [10];
} elseif (!is_array($perPageOptions)) {
    // Default fallback
    $perPageOptions = [10];
}

// Determine alignment classes
$alignmentClasses = match($alignment) {
    'left' => 'justify-start',
    'center' => 'justify-center',
    'right' => 'justify-end',
    'justify-between' => 'justify-between',
    'justify-around' => 'justify-around',
    'justify-evenly' => 'justify-evenly',
    default => 'justify-between',
};
@endphp

<div class="flex flex-col gap-3 sm:flex-row sm:items-center {{ $alignmentClasses }}">
    <!-- Page Info (kiri) -->
    @if($showPageInfo)
        <div class="text-sm text-gray-600 order-2 sm:order-1">
            Menampilkan <span class="font-medium">{{ $from }}</span> hingga 
            <span class="font-medium">{{ $to }}</span> dari 
            <span class="font-medium">{{ $total }}</span> hasil
        </div>
    @endif
    
    <!-- Controls (kanan) -->
    <div class="flex flex-wrap items-center gap-3 order-1 sm:order-2">
        <!-- Per Page Dropdown -->
        @if($showPerPage)
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">Per halaman:</label>
                <select wire:model.live="{{ $perPageWireModel }}" 
                        class="border border-gray-200 bg-white rounded-md px-3 py-1.5 text-sm text-gray-700 shadow-sm focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500">
                    @foreach($perPageOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        
        <!-- Pagination Buttons -->
        <div class="flex items-center gap-1">
            <!-- Previous Button -->
            @if($currentPage <= 1)
                <button disabled 
                        class="relative inline-flex items-center px-3 py-2 border border-gray-200 bg-gray-50 text-sm font-medium text-gray-300 rounded-md cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
            @else
                <button wire:click="{{ $previousPageWireModel }}" 
                        class="relative inline-flex items-center px-3 py-2 border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-blue-50 hover:text-blue-700 rounded-md transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
            @endif
            
            <!-- Page Numbers -->
            @foreach($pageNumbers as $page)
                @if($page === '...')
                    <span class="relative inline-flex items-center px-3 py-2 text-sm text-gray-500">
                        {{ $page }}
                    </span>
                @elseif($page == $currentPage)
                    <button wire:click="{{ $gotoPageWireModel }}({{ $page }})"
                            class="relative inline-flex items-center px-3 py-2 border border-blue-600 bg-blue-600 text-sm font-semibold text-white rounded-md shadow-sm">
                        {{ $page }}
                    </button>
                @else
                    <button wire:click="{{ $gotoPageWireModel }}({{ $page }})"
                            class="relative inline-flex items-center px-3 py-2 border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-blue-50 hover:text-blue-700 rounded-md transition-colors">
                        {{ $page }}
                    </button>
                @endif
            @endforeach
            
            <!-- Next Button -->
            @if($currentPage >= $lastPage)
                <button disabled 
                        class="relative inline-flex items-center px-3 py-2 border border-gray-200 bg-gray-50 text-sm font-medium text-gray-300 rounded-md cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            @else
                <button wire:click="{{ $nextPageWireModel }}" 
                        class="relative inline-flex items-center px-3 py-2 border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-blue-50 hover:text-blue-700 rounded-md transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            @endif
        </div>
    </div>
</div>
