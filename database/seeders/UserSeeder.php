<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default super-admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@usb.ac.id'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // Assign super-admin role
        $superAdmin->assignRole('super-admin');

        // Create a sample admin-sdm user
        $adminSdm = User::firstOrCreate(
            ['email' => 'admin.sdm@usb.ac.id'],
            [
                'name' => 'Admin SDM',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // Assign admin-sdm role
        $adminSdm->assignRole('admin-sdm');

        // Create a sample staff user
        $staff = User::firstOrCreate(
            ['email' => 'staff@usb.ac.id'],
            [
                'name' => 'Staff User',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // Assign staff role
        $staff->assignRole('staff');

        $this->command->info('Default users seeded successfully!');
        $this->command->info('Super Admin: superadmin@usb.ac.id / password123');
        $this->command->info('Admin SDM: admin.sdm@usb.ac.id / password123');
        $this->command->info('Staff: staff@usb.ac.id / password123');
    }
}
