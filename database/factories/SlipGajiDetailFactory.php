<?php

namespace Database\Factories;

use App\Models\SlipGajiDetail;
use App\Models\SlipGajiHeader;
use Illuminate\Database\Eloquent\Factories\Factory;

class SlipGajiDetailFactory extends Factory
{
    protected $model = SlipGajiDetail::class;

    public function definition(): array
    {
        return [
            'header_id' => SlipGajiHeader::factory(),
            'status' => 'KARYAWAN_TETAP',
            'nip' => $this->faker->numerify('##################'),
            'gaji_pokok' => 5000000,
            'gaji_bersih' => 5000000,
        ];
    }
}
