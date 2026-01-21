@props([
    'title',
    'subtitle' => null,
    'breadcrumbs' => []
])

<div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
    @if(!empty($breadcrumbs))
        @section('breadcrumb')
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2">
                    @foreach($breadcrumbs as $label => $url)
                        <li class="inline-flex items-center">
                            @if(!$loop->last)
                                <a href="{{ $url }}" class="text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors whitespace-nowrap">
                                    {{ $label }}
                                </a>
                                <span class="text-gray-400 mx-2">&gt;</span>
                            @else
                                <span class="text-sm font-semibold text-gray-400 whitespace-nowrap">
                                    {{ $label }}
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endsection
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
