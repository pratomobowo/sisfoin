<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create additional test users for different roles
        
        // Additional Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin2@usbypkp.ac.id'],
            [
                'name' => 'Super Admin 2',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );
        $superAdmin->assignRole('super-admin');

        // Additional Sekretariat user
        $sekretariat = User::firstOrCreate(
            ['email' => 'sekretariat@usbypkp.ac.id'],
            [
                'name' => 'Sekretariat User',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );
        $sekretariat->assignRole('sekretariat');

        // Additional Admin Sekretariat user
        $adminSekretariat = User::firstOrCreate(
            ['email' => 'admin-sekretariat@usbypkp.ac.id'],
            [
                'name' => 'Admin Sekretariat',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );
        $adminSekretariat->assignRole('admin-sekretariat');

        // Additional Admin Sarpras user
        $adminSarpras = User::firstOrCreate(
            ['email' => 'admin-sarpras@usbypkp.ac.id'],
            [
                'name' => 'Admin Sarpras',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );
        $adminSarpras->assignRole('admin-sarpras');

        // Additional Admin SDM user
        $adminSdm = User::firstOrCreate(
            ['email' => 'admin-sdm2@usbypkp.ac.id'],
            [
                'name' => 'Admin SDM 2',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );
        $adminSdm->assignRole('admin-sdm');

        // Additional Employee user
        $employee = User::firstOrCreate(
            ['email' => 'employee@usbypkp.ac.id'],
            [
                'name' => 'Employee User',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );
        $employee->assignRole('employee');

        // Additional Staff user
        $staff = User::firstOrCreate(
            ['email' => 'staff2@usbypkp.ac.id'],
            [
                'name' => 'Staff User 2',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );
        $staff->assignRole('staff');

        $this->command->info('Test users seeded successfully!');
        $this->command->info('Super Admin 2: superadmin2@usbypkp.ac.id / password123');
        $this->command->info('Sekretariat: sekretariat@usbypkp.ac.id / password123');
        $this->command->info('Admin Sekretariat: admin-sekretariat@usbypkp.ac.id / password123');
        $this->command->info('Admin Sarpras: admin-sarpras@usbypkp.ac.id / password123');
        $this->command->info('Admin SDM 2: admin-sdm2@usbypkp.ac.id / password123');
        $this->command->info('Employee: employee@usbypkp.ac.id / password123');
        $this->command->info('Staff 2: staff2@usbypkp.ac.id / password123');
    }
}