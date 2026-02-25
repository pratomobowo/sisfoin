@props([
    'breadcrumbs' => [],
    'items' => [],
])

@section('breadcrumb')
    <x-breadcrumb :breadcrumbs="$breadcrumbs" :items="$items" />
@endsection
