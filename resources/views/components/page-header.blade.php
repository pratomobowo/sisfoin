@props([
    'title',
    'subtitle' => null,
    'breadcrumbs' => []
])

<div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
    @if(!empty($breadcrumbs))
        <x-breadcrumb-section :breadcrumbs="$breadcrumbs" />
    @endif
            
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">
                {{ $title }}
            </h1>
            @if($subtitle)
                <p class="mt-1 text-sm text-gray-500 font-medium">
                    {{ $subtitle }}
                </p>
            @endif
        </div>
        
        @if(isset($actions))
            <div class="flex items-center gap-3">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
