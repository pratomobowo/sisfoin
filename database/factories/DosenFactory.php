<?php

namespace Database\Factories;

use App\Models\Dosen;
use Illuminate\Database\Eloquent\Factories\Factory;

class DosenFactory extends Factory
{
    protected $model = Dosen::class;

    public function definition(): array
    {
        return [
            'id_pegawai' => $this->faker->unique()->numerify('EMP#####'),
            'nip' => $this->faker->unique()->numerify('##########'),
            'nip_pns' => $this->faker->optional()->numerify('##########'),
            'nidn' => $this->faker->optional()->numerify('######'),
            'nup' => $this->faker->optional()->numerify('######'),
            'nidk' => $this->faker->optional()->numerify('######'),
            'nupn' => $this->faker->optional()->numerify('######'),
            'nik' => $this->faker->numerify('################'),
            'nama' => $this->faker->name(),
            'gelar_depan' => $this->faker->optional(0.2)->word(),
            'gelar_belakang' => $this->faker->optional(0.2)->word(),
            'jenis_kelamin' => $this->faker->randomElement(['L', 'P']),
            'id_agama' => $this->faker->numberBetween(1, 6),
            'agama' => $this->faker->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']),
            'id_kewarganegaraan' => 1,
            'kewarganegaraan' => 'WNI',
            'tanggal_lahir' => $this->faker->date(),
            'tempat_lahir' => $this->faker->city(),
            'status_nikah' => $this->faker->randomElement(['Lajang', 'Menikah', 'Duda', 'Janda']),
            'alamat_domisili' => $this->faker->address(),
            'rt_domisili' => $this->faker->numberBetween(1, 15),
            'rw_domisili' => $this->faker->numberBetween(1, 15),
            'kode_pos_domisili' => $this->faker->postcode(),
            'id_kecamatan_domisili' => $this->faker->numberBetween(1, 100),
            'kecamatan_domisili' => $this->faker->city(),
            'id_kota_domisili' => $this->faker->numberBetween(1, 100),
            'kota_domisili' => $this->faker->city(),
            'id_provinsi_domisili' => $this->faker->numberBetween(1, 34),
            'provinsi_domisili' => $this->faker->state(),
            'alamat_ktp' => $this->faker->address(),
            'rt_ktp' => $this->faker->numberBetween(1, 15),
            'rw_ktp' => $this->faker->numberBetween(1, 15),
            'kode_pos_ktp' => $this->faker->postcode(),
            'id_kecamatan_ktp' => $this->faker->numberBetween(1, 100),
            'kecamatan_ktp' => $this->faker->city(),
            'id_kota_ktp' => $this->faker->numberBetween(1, 100),
            'kota_ktp' => $this->faker->city(),
            'id_provinsi_ktp' => $this->faker->numberBetween(1, 34),
            'provinsi_ktp' => $this->faker->state(),
            'nomor_hp' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_kampus' => $this->faker->unique()->safeEmail(),
            'telepon' => $this->faker->optional()->phoneNumber(),
            'telepon_kantor' => $this->faker->optional()->phoneNumber(),
            'telepon_alternatif' => $this->faker->optional()->phoneNumber(),
            'id_satuan_kerja' => $this->faker->numberBetween(1, 20),
            'satuan_kerja' => $this->faker->jobTitle(),
            'id_home_base' => $this->faker->numberBetween(1, 20),
            'home_base' => $this->faker->word(),
            'id_pendidikan_terakhir' => $this->faker->numberBetween(1, 10),
            'tanggal_masuk' => $this->faker->date(),
            'tanggal_sertifikasi_dosen' => $this->faker->optional()->date(),
            'id_status_aktif' => 1,
            'status_aktif' => $this->faker->randomElement(['AA', 'Aktif', 'TA', 'Tidak Aktif', 'M', 'PN', 'MD']),
            'id_status_kepegawaian' => $this->faker->numberBetween(1, 5),
            'status_kepegawaian' => $this->faker->randomElement(['PNS', 'Dosen Tetap', 'Dosen Kontrak', 'Honorer']),
            'id_pangkat' => $this->faker->numberBetween(1, 16),
            'id_jabatan_fungsional' => $this->faker->numberBetween(1, 20),
            'jabatan_fungsional' => $this->faker->jobTitle(),
            'id_jabatan_sub_fungsional' => $this->faker->numberBetween(1, 30),
            'jabatan_sub_fungsional' => $this->faker->jobTitle(),
            'id_jabatan_struktural' => $this->faker->numberBetween(1, 10),
            'jabatan_struktural' => $this->faker->randomElement([
                'Rektor', 'Wakil Rektor I', 'Wakil Rektor II', 'Wakil Rektor III',
                'Dekan', 'Wakil Dekan I', 'Wakil Dekan II', 'Wakil Dekan III',
                'Ketua Program Studi', 'Kepala Bagian', 'Kepala Subbagian', 'Lainnya'
            ]),
            'is_deleted' => false,
            'id_sso' => null,
            'api_created_at' => now(),
            'api_updated_at' => now(),
            'last_synced_at' => now(),
        ];
    }
}