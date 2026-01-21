<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class TestEmployeeDosenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test employees
        $employees = [
            [
                'nip' => '198501012010011001',
                'nama_lengkap' => 'Dr. Ahmad Wijaya, S.Kom., M.T.',
                'jabatan_fungsional' => 'Dosen Tetap',
                'unit_kerja' => 'Fakultas Teknik Informatika',
                'status_aktif' => 'Aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nip' => '198702152011012002',
                'nama_lengkap' => 'Siti Nurhaliza, S.E., M.M.',
                'jabatan_fungsional' => 'Staff Administrasi',
                'unit_kerja' => 'Bagian Keuangan',
                'status_aktif' => 'Aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nip' => '199003202015031003',
                'nama_lengkap' => 'Budi Santoso, S.T., M.Eng.',
                'jabatan_fungsional' => 'Teknisi IT',
                'unit_kerja' => 'Unit IT',
                'status_aktif' => 'Aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($employees as $employee) {
            Employee::updateOrCreate(
                ['nip' => $employee['nip']],
                $employee
            );
        }

        // Create test dosen
        $dosens = [
            [
                'nip' => '198501012010011001',
                'nama' => 'Dr. Ahmad Wijaya, S.Kom., M.T.',
                'jabatan_fungsional' => 'Dosen Tetap',
                'satuan_kerja' => 'Fakultas Teknik Informatika',
                'status_aktif' => 'Aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nip' => '198804252012012004',
                'nama' => 'Prof. Dr. Indira Sari, S.Si., M.Sc.',
                'jabatan_fungsional' => 'Guru Besar',
                'satuan_kerja' => 'Fakultas MIPA',
                'status_aktif' => 'Aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($dosens as $dosen) {
            Dosen::updateOrCreate(
                ['nip' => $dosen['nip']],
                $dosen
            );
        }

        $this->command->info('Test Employee and Dosen data seeded successfully!');
    }
}
