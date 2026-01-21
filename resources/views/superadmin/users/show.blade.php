@extends('layouts.superadmin')

@section('page-title', 'Detail Pengguna')

@section('page-header')
    <x-superadmin.page-header 
        title="Detail Pengguna"
        description="Informasi lengkap pengguna dan data terkait"
        :showBackButton="true"
        backRoute="{{ route('superadmin.users.index') }}"
        backText="Kembali"
        :actions="[
            '<a href=\"' . route('superadmin.users.edit', $user->id) . '\" class=\"bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center space-x-2\">' . 
                '<svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">' . 
                    '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z\"></path>' . 
                '</svg>' . 
                '<span>Edit</span>' . 
            '</a>' . ($user->id !== auth()->id() ? 
                '<button onclick=\"confirmDelete(' . $user->id . ', \\'' . addslashes($user->name) . '\\')\" class=\"bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center space-x-2\">' . 
                    '<svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">' . 
                        '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16\"></path>' . 
                    '</svg>' . 
                    '<span>Hapus</span>' . 
                '</button>' : '')
        ]"
    />
@endsection

@section('main-content')
    <x-card>
        <div class="p-6">
        @if(session('info'))
            <div class="mb-4 p-4 bg-blue-50 border border-blue-200 text-blue-700 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-800">{{ session('info') }}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <div class="-mx-1.5 -my-1.5">
                            <button type="button" onclick="this.parentElement.parentElement.parentElement.parentElement.remove()" class="inline-flex bg-blue-50 rounded-md p-1.5 text-blue-500 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-50 focus:ring-blue-600">
                                <span class="sr-only">Dismiss</span>
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-1">
                <div class="text-center">
                    <div class="mx-auto flex justify-center">
                        <div class="user-avatar-lg bg-blue-600 rounded-full flex items-center justify-center text-white">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    </div>
                    <h3 class="mt-4 text-xl font-semibold text-gray-900">{{ $user->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    <div class="mt-4 flex flex-wrap justify-center gap-2">
                        @foreach($user->roles as $role)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                {{ $role->name === 'super-admin' ? 'bg-red-100 text-red-800' : (str_contains($role->name, 'admin') ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
                                {{ ucfirst(str_replace('-', ' ', $role->name)) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <div class="md:col-span-2">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">ID Pengguna</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->id }}</dd>
                    </div>
                    
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Nama Lengkap</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->name }}</dd>
                    </div>
                    
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->email }}</dd>
                    </div>
                    
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">NIP</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->nip ?? '-' }}</dd>
                    </div>
                    
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Tipe Karyawan</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($user->employee_type === 'employee')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Karyawan
                                </span>
                            @elseif($user->employee_type === 'dosen')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Dosen
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    -
                                </span>
                            @endif
                        </dd>
                    </div>
                    
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">ID Karyawan</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->employee_id ?? '-' }}</dd>
                    </div>
                    
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Tanggal Bergabung</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('d F Y') }}</dd>
                    </div>
                    
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Pembaruan Terakhir</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->updated_at->format('d F Y H:i') }}</dd>
                    </div>
                    
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Peran</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <div class="flex flex-wrap gap-2">
                                @foreach($user->roles as $role)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                        {{ $role->name === 'super-admin' ? 'bg-red-100 text-red-800' : (str_contains($role->name, 'admin') ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
                                        {{ ucfirst(str_replace('-', ' ', $role->name)) }}
                                    </span>
                                @endforeach
                            </div>
                        </dd>
                    </div>
                    
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Status Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($user->email_verified_at)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Terverifikasi
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    Belum Terverifikasi
                                </span>
                            @endif
                        </dd>
                    </div>
                </dl>
                
                @if($user->employeeData)
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h4 class="text-md font-medium text-gray-900 mb-4">Informasi {{ $user->employee_type === 'employee' ? 'Karyawan' : 'Dosen' }}</h4>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            @if($user->employee_type === 'employee')
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">NIK</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->employee->nik }}</dd>
                                </div>
                                
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Tempat, Tanggal Lahir</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->employee->tempat_lahir }}, {{ $user->employee->tanggal_lahir->format('d F Y') }}</dd>
                                </div>
                                
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Jenis Kelamin</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->employee->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</dd>
                                </div>
                                
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Unit Kerja</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->employee->unit_kerja }}</dd>
                                </div>
                                
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Jabatan</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->employee->jabatan_struktural ?: '-' }}</dd>
                                </div>
                            @else
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">NIDN</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->dosen->nidn }}</dd>
                                </div>
                                
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Nama</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->dosen->nama }}</dd>
                                </div>
                                
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Fakultas</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->dosen->fakultas }}</dd>
                                </div>
                                
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Program Studi</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->dosen->prodi }}</dd>
                                </div>
                                
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Jabatan</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->dosen->jabatan_fungsional ?: '-' }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endif
            </div>
        </div>
    </x-card>
@endsection

@push('styles')
<style>
    .user-avatar-lg {
        width: 120px;
        height: 120px;
        font-size: 48px;
    }
</style>
@endpush

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmationModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeDeleteModal()"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Hapus Pengguna
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Apakah Anda yakin ingin menghapus <span id="userNameToDelete" class="font-semibold"></span>? Tindakan ini tidak dapat dibatalkan.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <form id="deleteForm" action="{{ route('superadmin.users.destroy', $user->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Hapus
                    </button>
                </form>
                <button onclick="closeDeleteModal()" type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function confirmDelete(userId, userName) {
        document.getElementById('userNameToDelete').textContent = userName;
        document.getElementById('deleteForm').action = "/superadmin/users/" + userId;
        document.getElementById('deleteConfirmationModal').classList.remove('hidden');
    }
    
    function closeDeleteModal() {
        document.getElementById('deleteConfirmationModal').classList.add('hidden');
    }
</script>
@endpush
