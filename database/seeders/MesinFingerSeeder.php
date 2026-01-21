<?php

namespace Database\Seeders;

use App\Models\MesinFinger;
use Illuminate\Database\Seeder;

class MesinFingerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MesinFinger::create([
            'nama_mesin' => 'Mesin Absen Lobby',
            'ip_address' => '192.168.1.100',
            'port' => 4370,
            'lokasi' => 'Lobby Utama',
            'status' => 'inactive',
            'keterangan' => 'Mesin absen utama di lobby',
        ]);

        MesinFinger::create([
            'nama_mesin' => 'Mesin Absen Perpustakaan',
            'ip_address' => '192.168.1.101',
            'port' => 4370,
            'lokasi' => 'Perpustakaan',
            'status' => 'inactive',
            'keterangan' => 'Mesin absen di perpustakaan',
        ]);

        MesinFinger::create([
            'nama_mesin' => 'Mesin Absen Laboratorium',
            'ip_address' => '192.168.1.102',
            'port' => 4370,
            'lokasi' => 'Laboratorium',
            'status' => 'inactive',
            'keterangan' => 'Mesin absen di laboratorium komputer',
        ]);
    }
}
