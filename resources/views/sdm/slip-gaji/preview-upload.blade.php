@extends('layouts.app')

@section('page-title', 'Preview Import Slip Gaji')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Preview Import Slip Gaji</h2>
                <p class="mt-1 text-sm text-gray-600">Periksa seluruh data sebelum disimpan ke database final.</p>
                <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold">
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-700">Periode: {{ $preview->periode }}</span>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-700">Mode: {{ strtoupper(str_replace('_', ' ', $preview->mode)) }}</span>
                    <span class="rounded-full {{ $preview->status === 'ready' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} px-3 py-1">Status: {{ ucfirst($preview->status) }}</span>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('sdm.slip-gaji.upload') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Upload Ulang</a>
                <form action="{{ route('sdm.slip-gaji.upload.preview.cancel', $preview->token) }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-lg border border-red-300 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Batalkan Preview</button>
                </form>
                <form action="{{ route('sdm.slip-gaji.upload.preview.confirm', $preview->token) }}" method="POST">
                    @csrf
                    <button type="submit" @disabled($preview->status !== 'ready' || $preview->error_count > 0) class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">Simpan ke Database</button>
                </form>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total Baris</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($preview->row_count) }}</p>
        </div>
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-green-700">Valid</p>
            <p class="mt-2 text-2xl font-bold text-green-800">{{ number_format(($preview->summary_json['valid_count'] ?? 0)) }}</p>
        </div>
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-red-700">Error</p>
            <p class="mt-2 text-2xl font-bold text-red-800">{{ number_format($preview->error_count) }}</p>
        </div>
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Total Bersih</p>
            <p class="mt-2 text-xl font-bold text-blue-800">Rp {{ number_format($preview->summary_json['total_net'] ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>

    @if($preview->error_count > 0)
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            Ada data bermasalah. Tombol simpan dinonaktifkan sampai file diperbaiki dan diupload ulang.
        </div>
    @endif

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 p-4">
            <form method="GET" class="flex flex-col gap-3 sm:flex-row">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari NIP atau nama..." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">Cari</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Baris</th>
                        <th class="px-4 py-3 text-left">NIP</th>
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-right">Gaji Pokok</th>
                        <th class="px-4 py-3 text-right">Potongan</th>
                        <th class="px-4 py-3 text-right">Gaji Bersih</th>
                        <th class="px-4 py-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($rows as $row)
                        <tr>
                            <td class="px-4 py-3 text-gray-500">{{ $row->row_number }}</td>
                            <td class="px-4 py-3 font-mono text-gray-900">{{ $row->nip }}</td>
                            <td class="px-4 py-3 text-gray-900">{{ $row->nama ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($row->data_json['gaji_pokok'] ?? 0, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($row->deduction_amount ?? 0, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($row->net_amount ?? 0, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                @if($row->validation_status === 'valid')
                                    <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-700">Valid</span>
                                @else
                                    <div class="space-y-1">
                                        <span class="rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-700">Error</span>
                                        <div class="text-xs text-red-700">{{ implode(', ', $row->validation_errors_json ?? []) }}</div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Tidak ada data preview.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-200 p-4">
            {{ $rows->links() }}
        </div>
    </div>
</div>
@endsection
