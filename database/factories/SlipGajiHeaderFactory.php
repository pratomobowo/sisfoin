<?php

namespace Database\Factories;

use App\Models\SlipGajiHeader;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SlipGajiHeaderFactory extends Factory
{
    protected $model = SlipGajiHeader::class;

    public function definition(): array
    {
        return [
            'periode' => $this->faker->date('Y-m'),
            'mode' => $this->faker->randomElement(['standard', 'gaji_13', 'thr']),
            'file_original' => $this->faker->word.'.xlsx',
            'uploaded_by' => User::factory(),
            'uploaded_at' => now(),
            'status' => 'draft',
            'published_at' => null,
            'published_by' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => now(),
            'published_by' => User::factory(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
            'published_by' => null,
        ]);
    }
}
