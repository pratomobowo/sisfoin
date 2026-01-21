<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define module-based permissions
        $modules = [
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
                'label' => 'Penggajihan Karyawan',
                'permissions' => [
                    'employee.payroll.view' => 'Lihat Penggajihan',
                    'employee.payroll.create' => 'Buat Penggajihan',
                    'employee.payroll.edit' => 'Edit Penggajihan',
                    'employee.payroll.delete' => 'Hapus Penggajihan',
                    'employee.payroll.report' => 'Laporan Penggajihan',
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

        // Create all permissions
        foreach ($modules as $moduleKey => $moduleData) {
            foreach ($moduleData['permissions'] as $permissionName => $permissionLabel) {
                Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => 'web',
                ]);
            }
        }

        // Create special permissions
        $specialPermissions = [
            'superadmin.access' => 'Akses Superadmin',
        ];

        foreach ($specialPermissions as $permissionName => $permissionLabel) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        // Update cache after creating permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define roles with their module access
        $rolesConfig = [
            'super-admin' => [
                'display_name' => 'Super Admin',
                'description' => 'Akses penuh ke semua modul sistem',
                'modules' => array_keys($modules), // All modules
                'special_permissions' => ['superadmin.access'],
            ],
            'admin-sdm' => [
                'display_name' => 'Admin SDM',
                'description' => 'Akses ke modul SDM dan karyawan',
                'modules' => ['employee_management', 'dosen_management', 'payroll_management', 'profile_management'],
                'special_permissions' => [],
            ],
            'sekretariat' => [
                'display_name' => 'Sekretariat',
                'description' => 'Akses ke modul sekretariat dan surat keputusan',
                'modules' => ['sekretariat_management', 'surat_keputusan_management', 'profile_management'],
                'special_permissions' => [],
            ],
            'admin-sekretariat' => [
                'display_name' => 'Admin Sekretariat',
                'description' => 'Akses ke modul sekretariat',
                'modules' => ['sekretariat_management', 'profile_management'],
                'special_permissions' => [],
            ],
            'admin-sarpras' => [
                'display_name' => 'Admin Sarana Prasarana',
                'description' => 'Akses ke modul sarana prasarana',
                'modules' => ['sarpras_management', 'profile_management'],
                'special_permissions' => [],
            ],
            'employee' => [
                'display_name' => 'Karyawan',
                'description' => 'Akses ke modul karyawan (penggajihan, absensi, pengumuman)',
                'modules' => ['employee_payroll', 'employee_attendance', 'employee_announcements', 'profile_management'],
                'special_permissions' => [],
            ],
            'staff' => [
                'display_name' => 'Staff',
                'description' => 'Akses terbatas untuk staff',
                'modules' => ['profile_management'],
                'special_permissions' => [],
            ],
        ];

        // Create roles and assign permissions
        foreach ($rolesConfig as $roleName => $roleConfig) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            // Collect permissions for this role
            $rolePermissions = [];

            // Add module permissions
            foreach ($roleConfig['modules'] as $moduleKey) {
                if (isset($modules[$moduleKey])) {
                    $rolePermissions = array_merge($rolePermissions, array_keys($modules[$moduleKey]['permissions']));
                }
            }

            // Add special permissions
            $rolePermissions = array_merge($rolePermissions, $roleConfig['special_permissions']);

            // For super-admin, give all permissions
            if ($roleName === 'super-admin') {
                $role->syncPermissions(Permission::all());
            } else {
                // For staff role, only give view permissions for payroll and profile edit
                if ($roleName === 'staff') {
                    $staffPermissions = ['profile.view', 'profile.edit', 'payroll.view'];
                    $role->syncPermissions($staffPermissions);
                } else {
                    $role->syncPermissions($rolePermissions);
                }
            }
        }

        // Store module configuration for use in UI
        $moduleConfigPath = storage_path('app/modules_config.json');
        file_put_contents($moduleConfigPath, json_encode($modules, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
