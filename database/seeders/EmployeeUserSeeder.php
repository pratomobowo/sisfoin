<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a sample employee user
        $employee = User::firstOrCreate(
            ['email' => 'employee@usb.ac.id'],
            [
                'name' => 'Karyawan Test',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // Assign employee role
        $employee->assignRole('employee');

        $this->command->info('Employee user seeded successfully!');
        $this->command->info('Employee: employee@usb.ac.id / password123');
    }
}
