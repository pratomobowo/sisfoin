<?php

return [
    'user_management' => [
        'label' => 'Manajemen Pengguna',
        'permissions' => [
            'users.view' => 'Lihat Pengguna',
            'users.create' => 'Tambah Pengguna',
            'users.edit' => 'Edit Pengguna',
            'users.delete' => 'Hapus Pengguna',
        ],
    ],
    'role_management' => [
        'label' => 'Manajemen Peran',
        'permissions' => [
            'roles.view' => 'Lihat Peran',
            'roles.create' => 'Tambah Peran',
            'roles.edit' => 'Edit Peran',
            'roles.delete' => 'Hapus Peran',
            'roles.assign' => 'Assign Peran',
        ],
    ],
    'employee_management' => [
        'label' => 'Manajemen Karyawan',
        'permissions' => [
            'employees.view' => 'Lihat Karyawan',
            'employees.create' => 'Tambah Karyawan',
            'employees.edit' => 'Edit Karyawan',
            'employees.delete' => 'Hapus Karyawan',
        ],
    ],
    'dosen_management' => [
        'label' => 'Manajemen Dosen',
        'permissions' => [
            'dosen.view' => 'Lihat Dosen',
            'dosen.create' => 'Tambah Dosen',
            'dosen.edit' => 'Edit Dosen',
            'dosen.delete' => 'Hapus Dosen',
        ],
    ],
    'payroll_management' => [
        'label' => 'Manajemen Slip Gaji',
        'permissions' => [
            'payroll.view' => 'Lihat Slip Gaji',
            'payroll.create' => 'Buat Slip Gaji',
            'payroll.edit' => 'Edit Slip Gaji',
            'payroll.delete' => 'Hapus Slip Gaji',
            'payroll.download' => 'Download Slip Gaji',
        ],
    ],
    'profile_management' => [
        'label' => 'Manajemen Profil',
        'permissions' => [
            'profile.view' => 'Lihat Profil',
            'profile.edit' => 'Edit Profil',
        ],
    ],
    'sarpras_management' => [
        'label' => 'Manajemen Sarana Prasarana',
        'permissions' => [
            'sarpras.view' => 'Lihat Sarpras',
            'sarpras.create' => 'Tambah Sarpras',
            'sarpras.edit' => 'Edit Sarpras',
            'sarpras.delete' => 'Hapus Sarpras',
        ],
    ],
    'sekretariat_management' => [
        'label' => 'Manajemen Sekretariat',
        'permissions' => [
            'sekretariat.view' => 'Lihat Sekretariat',
            'sekretariat.create' => 'Tambah Sekretariat',
            'sekretariat.edit' => 'Edit Sekretariat',
            'sekretariat.delete' => 'Hapus Sekretariat',
        ],
    ],
    'surat_keputusan_management' => [
        'label' => 'Manajemen Surat Keputusan',
        'permissions' => [
            'surat_keputusan.view' => 'Lihat Surat Keputusan',
            'surat_keputusan.create' => 'Tambah Surat Keputusan',
            'surat_keputusan.edit' => 'Edit Surat Keputusan',
            'surat_keputusan.delete' => 'Hapus Surat Keputusan',
            'surat_keputusan.download' => 'Download Surat Keputusan',
        ],
    ],
    'employee_payroll' => [
        'label' => 'Penggajian Karyawan',
        'permissions' => [
            'employee.payroll.view' => 'Lihat Penggajian',
            'employee.payroll.create' => 'Buat Penggajian',
            'employee.payroll.edit' => 'Edit Penggajian',
            'employee.payroll.delete' => 'Hapus Penggajian',
            'employee.payroll.report' => 'Laporan Penggajian',
        ],
    ],
    'employee_attendance' => [
        'label' => 'Absensi Karyawan',
        'permissions' => [
            'employee.attendance.view' => 'Lihat Absensi',
            'employee.attendance.checkin' => 'Check In',
            'employee.attendance.checkout' => 'Check Out',
            'employee.attendance.edit' => 'Edit Absensi',
            'employee.attendance.report' => 'Laporan Absensi',
        ],
    ],
    'employee_announcements' => [
        'label' => 'Pengumuman Karyawan',
        'permissions' => [
            'employee.announcements.view' => 'Lihat Pengumuman',
            'employee.announcements.create' => 'Buat Pengumuman',
            'employee.announcements.edit' => 'Edit Pengumuman',
            'employee.announcements.delete' => 'Hapus Pengumuman',
            'employee.announcements.manage' => 'Kelola Pengumuman',
        ],
    ],
];
