<?php

namespace Database\Factories;

use App\Models\KegiatanPejabat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class KegiatanPejabatFactory extends Factory
{
    protected $model = KegiatanPejabat::class;

    public function definition(): array
    {
        return [
            'nama_kegiatan' => $this->faker->sentence(4),
            'jenis_kegiatan' => $this->faker->randomElement([
                'Rapat Internal', 'Rapat Eksternal', 'Kunjungan Kerja', 
                'Upacara', 'Seminar', 'Workshop', 'Pelatihan', 'Lain-lain'
            ]),
            'tempat_kegiatan' => $this->faker->address(),
            'tanggal_mulai' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'tanggal_selesai' => $this->faker->dateTimeBetween('+2 days', '+2 months'),
            'pejabat_terkait' => [$this->faker->numberBetween(1, 100)],
            'disposisi_kepada' => $this->faker->name(),
            'keterangan' => $this->faker->optional()->paragraph(),
            'file_lampiran' => null,
            'file_name' => null,
            'file_size' => null,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}