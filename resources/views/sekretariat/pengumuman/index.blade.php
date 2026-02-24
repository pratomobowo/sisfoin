@extends('layouts.app')

@section('page-title', 'Pengumuman Sekretariat')

@section('breadcrumb')
<nav class="flex" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                <x-lucide-house class="w-4 h-4 mr-2" />
                Dashboard
            </a>
        </li>
        <li>
            <div class="flex items-center">
                <x-lucide-chevron-right class="w-4 h-4 text-gray-400" />
                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Sekretariat</span>
            </div>
        </li>
        <li>
            <div class="flex items-center">
                <x-lucide-chevron-right class="w-4 h-4 text-gray-400" />
                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Pengumuman</span>
            </div>
        </li>
    </ol>
</nav>
@endsection

@section('content')
<div class="space-y-6" x-data="{ showCreateForm: {{ $errors->any() ? 'true' : 'false' }} }">
    <x-page-header
        title="Manajemen Pengumuman"
        subtitle="Kelola pengumuman staff dan employee dari dashboard admin"
        :breadcrumbs="['Sekretariat' => '#', 'Pengumuman' => route('sekretariat.pengumuman.index')]"
    >
        <x-slot name="actions">
            <button type="button" @click="showCreateForm = !showCreateForm" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                <x-lucide-plus class="w-4 h-4 mr-1.5" />
                Tambah Pengumuman
            </button>
        </x-slot>
    </x-page-header>

    @if ($errors->any())
        <div class="bg-rose-50 border border-rose-200 rounded-xl px-4 py-3 text-sm text-rose-700">
            <p class="font-semibold mb-1">Validasi gagal:</p>
            <ul class="list-disc ml-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <form method="GET" action="{{ route('sekretariat.pengumuman.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari judul/konten..." class="md:col-span-2 px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200 text-sm">
            <select name="type" class="px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200 text-sm">
                <option value="">Semua Jenis</option>
                @foreach($typeOptions as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="priority" class="px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200 text-sm">
                <option value="">Semua Prioritas</option>
                @foreach($priorityOptions as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['priority'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <select name="status" class="flex-1 px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200 text-sm">
                    <option value="">Semua Status</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="px-4 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold">Filter</button>
            </div>
        </form>
    </div>

    <div class="space-y-3">
        <h2 class="text-base font-bold text-gray-900">Daftar Pengumuman</h2>

        <div x-show="showCreateForm" x-cloak class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h2 class="text-base font-bold text-gray-900 mb-4">Form Pengumuman Baru</h2>
            <form method="POST" action="{{ route('sekretariat.pengumuman.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-gray-700">Judul</label>
                    <input type="text" name="title" required class="mt-1 w-full px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200 text-sm" value="{{ old('title') }}">
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-gray-700">Konten</label>
                    <textarea name="content" rows="4" required class="mt-1 w-full px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200 text-sm">{{ old('content') }}</textarea>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Jenis</label>
                    <select name="type" class="mt-1 w-full px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200 text-sm">
                        @foreach($typeOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('type', 'general') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Prioritas</label>
                    <select name="priority" class="mt-1 w-full px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200 text-sm">
                        @foreach($priorityOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('priority', 'normal') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Status</label>
                    <select name="status" class="mt-1 w-full px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200 text-sm">
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', 'published') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Berlaku Sampai</label>
                    <input type="datetime-local" name="expires_at" class="mt-1 w-full px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200 text-sm" value="{{ old('expires_at') }}">
                </div>
                <div class="md:col-span-2 flex flex-wrap items-center gap-4 text-sm">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned'))>
                        <span>Sematkan pengumuman</span>
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="audience_staff" value="1" @checked(old('audience_staff', true))>
                        <span>Audience: Staff</span>
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="audience_employee" value="1" @checked(old('audience_employee', true))>
                        <span>Audience: Employee</span>
                    </label>
                </div>
                <div class="md:col-span-2 flex items-center gap-2">
                    <button class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Simpan Pengumuman</button>
                    <button type="button" @click="showCreateForm = false" class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold">Batal</button>
                </div>
            </form>
        </div>

        @forelse($announcements as $announcement)
            <div x-data="{ editOpen: false }" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700">{{ $announcement->type_label }}</span>
                            <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700">{{ $announcement->priority_label }}</span>
                            <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700">{{ $announcement->status_label }}</span>
                            @if($announcement->is_pinned)
                                <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-amber-50 text-amber-700">Pinned</span>
                            @endif
                        </div>
                        <h3 class="text-base font-bold text-gray-900">{{ $announcement->title }}</h3>
                        <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags($announcement->content), 180) }}</p>
                        <p class="text-xs text-gray-400 mt-2">
                            {{ $announcement->creator?->name ?? 'System' }} - {{ optional($announcement->published_at)->format('d M Y H:i') ?? '-' }}
                        </p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <form method="POST" action="{{ route('sekretariat.pengumuman.toggle-pin', $announcement->id) }}">
                            @csrf
                            @method('PATCH')
                            <button class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $announcement->is_pinned ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ $announcement->is_pinned ? 'Unpin' : 'Pin' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('sekretariat.pengumuman.toggle-status', $announcement->id) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="{{ $announcement->status === 'published' ? 'archived' : 'published' }}">
                            <button class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700">
                                {{ $announcement->status === 'published' ? 'Arsipkan' : 'Publish' }}
                            </button>
                        </form>
                    </div>
                </div>

                <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/50" @click="editOpen = false"></div>
                    <div class="relative w-full max-w-2xl bg-white rounded-2xl border border-gray-200 shadow-2xl max-h-[88vh] overflow-y-auto">
                        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="text-base font-bold text-gray-900">Edit Pengumuman</h3>
                            <button type="button" @click="editOpen = false" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                                <x-lucide-x class="w-5 h-5" />
                            </button>
                        </div>

                        <form method="POST" action="{{ route('sekretariat.pengumuman.update', $announcement->id) }}" class="p-5 grid grid-cols-1 md:grid-cols-2 gap-3">
                            @csrf
                            @method('PUT')
                            <input type="text" name="title" value="{{ $announcement->title }}" class="md:col-span-2 px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200 text-sm" required>
                            <textarea name="content" rows="4" class="md:col-span-2 px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200 text-sm" required>{{ $announcement->content }}</textarea>
                            <select name="type" class="px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200 text-sm">
                                @foreach($typeOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($announcement->type === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <select name="priority" class="px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200 text-sm">
                                @foreach($priorityOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($announcement->priority === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <select name="status" class="px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200 text-sm">
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($announcement->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <input type="datetime-local" name="expires_at" value="{{ optional($announcement->expires_at)->format('Y-m-d\TH:i') }}" class="px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200 text-sm">
                            @php
                                $audience = is_array($announcement->target_audience) ? $announcement->target_audience : [];
                            @endphp
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="checkbox" name="is_pinned" value="1" @checked($announcement->is_pinned)>
                                <span>Sematkan</span>
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="checkbox" name="audience_staff" value="1" @checked(in_array('staff', $audience, true))>
                                <span>Audience Staff</span>
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="checkbox" name="audience_employee" value="1" @checked(in_array('employee', $audience, true))>
                                <span>Audience Employee</span>
                            </label>
                            <div class="md:col-span-2 flex items-center gap-2 pt-1">
                                <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Update</button>
                                <button type="button" @click="editOpen = false" class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="mt-3 pt-3 border-t border-gray-100">
                    <div class="flex items-center gap-2">
                        <button type="button" @click="editOpen = true" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-sky-100 text-sky-700">
                            Edit
                        </button>
                        <form method="POST" action="{{ route('sekretariat.pengumuman.destroy', $announcement->id) }}" onsubmit="return confirm('Hapus pengumuman ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-rose-100 text-rose-700">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 text-center text-gray-500">
                Belum ada pengumuman.
            </div>
        @endforelse
    </div>

    <div>
        {{ $announcements->links() }}
    </div>
</div>
@endsection
