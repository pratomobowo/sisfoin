<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'id_pegawai' => (string) $this->faker->unique()->numberBetween(100000, 999999),
            'nip' => $this->faker->unique()->numerify('19########'),
            'nama' => $this->faker->name(),
            'satuan_kerja' => $this->faker->randomElement(['IT', 'HR', 'Finance', 'Akademik']),
            'status_aktif' => 'Aktif',
        ];
    }
}
