<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\Employee;
use App\Models\SlipGajiDetail;
use App\Models\SlipGajiHeader;
use Illuminate\Database\Seeder;

class TestSlipGajiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get test employees and dosens
        $employee = Employee::where('nip', '198501012010011001')->first();
        $dosen = Dosen::where('nip', '198804252012012004')->first();

        if (! $employee || ! $dosen) {
            $this->command->error('Test Employee or Dosen not found. Please run TestEmployeeDosenSeeder first.');

            return;
        }

        // Create test slip gaji header
        $slipGajiHeader = SlipGajiHeader::updateOrCreate(
            ['periode' => '2024-01'],
            [
                'periode' => '2024-01',
                'file_original' => 'test_slip_gaji_2024_01.xlsx',
                'uploaded_by' => 1, // Assuming user ID 1 exists
                'uploaded_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Create slip gaji details for employee
        SlipGajiDetail::updateOrCreate(
            [
                'header_id' => $slipGajiHeader->id,
                'nip' => $employee->nip,
            ],
            [
                'header_id' => $slipGajiHeader->id,
                'nip' => $employee->nip,
                'nama' => $employee->nama_lengkap, // This should be overridden by accessor
                'status' => 'KARYAWAN_TETAP',
                'gaji_pokok' => 5000000,
                'tunjangan_struktural' => 1500000,
                'tunjangan_keluarga' => 500000,
                'tunjangan_fungsional' => 300000,
                'pajak' => 750000,
                'potongan_bpjs_kesehatan' => 200000,
                'potongan_koperasi' => 100000,
                'gaji_bersih' => 6250000,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Create slip gaji details for dosen
        SlipGajiDetail::updateOrCreate(
            [
                'header_id' => $slipGajiHeader->id,
                'nip' => $dosen->nip,
            ],
            [
                'header_id' => $slipGajiHeader->id,
                'nip' => $dosen->nip,
                'nama' => $dosen->nama, // This should be overridden by accessor
                'status' => 'DOSEN_TETAP',
                'gaji_pokok' => 6000000,
                'tunjangan_struktural' => 2000000,
                'tunjangan_keluarga' => 600000,
                'tunjangan_fungsional' => 400000,
                'pajak' => 900000,
                'potongan_bpjs_kesehatan' => 250000,
                'potongan_koperasi' => 150000,
                'gaji_bersih' => 7700000,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info('Test SlipGaji data seeded successfully!');
    }
}
