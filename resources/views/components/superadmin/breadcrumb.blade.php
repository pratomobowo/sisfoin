@props([
    'items' => []
])

<div class="mb-4">
    <x-breadcrumb :items="$items" show-home :home-url="route('dashboard')" />
</div>
