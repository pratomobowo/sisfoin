<?php

namespace App\Livewire\Superadmin;

use App\Models\Role;
use Livewire\Component;

class UserImportModal extends Component
{
    public $showModal = false;
    public $importCounts = [];
    public $isLoading = false;

    protected $listeners = [
        'showUserImportModal' => 'openModal',
        'closeUserImportModal' => 'closeModal',
    ];

    public function openModal()
    {
        $this->showModal = true;
        $this->prepareImportData();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->importCounts = [];
    }

    public function prepareImportData()
    {
        $this->isLoading = true;
        
        try {
            // Get employees data with email, NIP, and active status
            $employeesQuery = \DB::table('employees')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->whereNotNull('nip')
                ->where('nip', '!=', '')
                ->where('status_aktif', 'Aktif');
            
            $employeesTotal = $employeesQuery->count();
            $employeesData = $employeesQuery->get(['id', 'nama', 'email', 'nip']);

            // Check which employees are new vs existing
            $employeesNew = 0;
            $employeesExisting = 0;
            foreach ($employeesData as $employee) {
                $existingUser = \DB::table('users')->where('email', $employee->email)->first();
                if ($existingUser) {
                    $employeesExisting++;
                } else {
                    $employeesNew++;
                }
            }

            // Get dosens data with email, NIP, and active status
            $dosensQuery = \DB::table('dosens')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->whereNotNull('nip')
                ->where('nip', '!=', '')
                ->where('status_aktif', 'Aktif');
            
            $dosensTotal = $dosensQuery->count();
            $dosensData = $dosensQuery->get(['id', 'nama', 'email', 'nidn']);

            // Check which dosens are new vs existing
            $dosensNew = 0;
            $dosensExisting = 0;
            foreach ($dosensData as $dosen) {
                $existingUser = \DB::table('users')->where('email', $dosen->email)->first();
                if ($existingUser) {
                    $dosensExisting++;
                } else {
                    $dosensNew++;
                }
            }

            $this->importCounts = [
                'employees_total' => $employeesTotal,
                'employees_new' => $employeesNew,
                'employees_existing' => $employeesExisting,
                'dosens_total' => $dosensTotal,
                'dosens_new' => $dosensNew,
                'dosens_existing' => $dosensExisting,
                'total_new' => $employeesNew + $dosensNew,
                'total_existing' => $employeesExisting + $dosensExisting,
            ];

        } catch (\Exception $e) {
            \Log::error('Error preparing import data: ' . $e->getMessage());
            $this->importCounts = [
                'employees_total' => 0,
                'employees_new' => 0,
                'employees_existing' => 0,
                'dosens_total' => 0,
                'dosens_new' => 0,
                'dosens_existing' => 0,
                'total_new' => 0,
                'total_existing' => 0,
            ];
        }

        $this->isLoading = false;
    }

    public function importUsers()
    {
        try {
            $importedCount = 0;
            $updatedCount = 0;

            // Get staff role
            $staffRole = \DB::table('roles')->where('name', 'staff')->first();
            if (!$staffRole) {
                throw new \Exception('Staff role not found');
            }

            // Import employees with email, NIP, and active status
            $employeesData = \DB::table('employees')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->whereNotNull('nip')
                ->where('nip', '!=', '')
                ->where('status_aktif', 'Aktif')
                ->get(['id', 'nama', 'email', 'nip']);

            foreach ($employeesData as $employee) {
                $existingUser = \DB::table('users')->where('email', $employee->email)->first();
                
                if ($existingUser) {
                    // Update existing user
                    \DB::table('users')
                        ->where('id', $existingUser->id)
                        ->update([
                            'name' => $employee->nama,
                            'updated_at' => now(),
                        ]);
                    $updatedCount++;
                } else {
                    // Create new user
                    $userId = \DB::table('users')->insertGetId([
                        'name' => $employee->nama,
                        'email' => $employee->email,
                        'password' => bcrypt('password123'),
                        'email_verified_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Assign staff role
                    \DB::table('model_has_roles')->insert([
                        'role_id' => $staffRole->id,
                        'model_type' => 'App\Models\User',
                        'model_id' => $userId,
                    ]);

                    $importedCount++;
                }
            }

            // Import dosens with email, NIP, and active status
            $dosensData = \DB::table('dosens')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->whereNotNull('nip')
                ->where('nip', '!=', '')
                ->where('status_aktif', 'Aktif')
                ->get(['id', 'nama', 'email', 'nidn']);

            foreach ($dosensData as $dosen) {
                $existingUser = \DB::table('users')->where('email', $dosen->email)->first();
                
                if ($existingUser) {
                    // Update existing user
                    \DB::table('users')
                        ->where('id', $existingUser->id)
                        ->update([
                            'name' => $dosen->nama,
                            'updated_at' => now(),
                        ]);
                    $updatedCount++;
                } else {
                    // Create new user
                    $userId = \DB::table('users')->insertGetId([
                        'name' => $dosen->nama,
                        'email' => $dosen->email,
                        'password' => bcrypt('password123'),
                        'email_verified_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Assign staff role
                    \DB::table('model_has_roles')->insert([
                        'role_id' => $staffRole->id,
                        'model_type' => 'App\Models\User',
                        'model_id' => $userId,
                    ]);

                    $importedCount++;
                }
            }

            $this->closeModal();
            
            $message = "Import selesai! ";
            if ($importedCount > 0) {
                $message .= "{$importedCount} pengguna baru dibuat. ";
            }
            if ($updatedCount > 0) {
                $message .= "{$updatedCount} pengguna diperbarui.";
            }
            
            session()->flash('success', $message);
            $this->dispatch('usersImported');

        } catch (\Exception $e) {
            $this->closeModal();
            session()->flash('error', 'Gagal melakukan import: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.superadmin.user-import-modal');
    }
}
