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
            // Employees
            $employeesTotal = \DB::table('employees')->where('status_aktif', 'Aktif')->count();
            
            $employeesQuery = \DB::table('employees')
                ->where('status_aktif', 'Aktif')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->whereNotNull('nip')
                ->where('nip', '!=', '');
            
            $employeesReady = $employeesQuery->count();
            $employeesSkipped = $employeesTotal - $employeesReady; // Skipped due to missing email/nip
            
            $employeesData = $employeesQuery->get(['id', 'nama', 'email', 'nip']);

            // Check new vs existing
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

            // Dosens
            $dosensTotal = \DB::table('dosens')->where('status_aktif', 'Aktif')->count();
            
            $dosensQuery = \DB::table('dosens')
                ->where('status_aktif', 'Aktif')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->whereNotNull('nip')
                ->where('nip', '!=', '');
            
            $dosensReady = $dosensQuery->count();
            $dosensSkipped = $dosensTotal - $dosensReady;

            $dosensData = $dosensQuery->get(['id', 'nama', 'email', 'nidn']);

            // Check new vs existing
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
                'employees_ready' => $employeesReady,
                'employees_skipped' => $employeesSkipped,
                'employees_new' => $employeesNew,
                'employees_existing' => $employeesExisting,
                
                'dosens_total' => $dosensTotal,
                'dosens_ready' => $dosensReady,
                'dosens_skipped' => $dosensSkipped,
                'dosens_new' => $dosensNew,
                'dosens_existing' => $dosensExisting,
                
                'total_new' => $employeesNew + $dosensNew,
                'total_existing' => $employeesExisting + $dosensExisting,
            ];

        } catch (\Exception $e) {
            \Log::error('Error preparing import data: ' . $e->getMessage());
            $this->importCounts = [];
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

            // Import employees
            foreach ($employeesData as $employee) {
                $existingUser = \DB::table('users')->where('email', $employee->email)->first();
                
                $userData = [
                    'name' => $employee->nama,
                    'employee_id' => $employee->id,
                    'employee_type' => 'employee',
                    'updated_at' => now(),
                ];

                if ($existingUser) {
                    \DB::table('users')->where('id', $existingUser->id)->update($userData);
                    $updatedCount++;
                } else {
                    $userData = array_merge($userData, [
                        'email' => $employee->email,
                        'password' => bcrypt('password123'),
                        'email_verified_at' => now(),
                        'created_at' => now(),
                    ]);
                    $userId = \DB::table('users')->insertGetId($userData);
                    
                    \DB::table('model_has_roles')->insert([
                        'role_id' => $staffRole->id,
                        'model_type' => 'App\Models\User',
                        'model_id' => $userId,
                    ]);
                    $importedCount++;
                }
            }

            // Import dosens
            $dosensData = \DB::table('dosens')
                ->where('status_aktif', 'Aktif')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->where(function($query) {
                    $query->where(function($q) {
                        $q->whereNotNull('nip')->where('nip', '!=', '');
                    })->orWhere(function($q) {
                        $q->whereNotNull('nidn')->where('nidn', '!=', '');
                    });
                })
                ->get(['id', 'nama', 'email', 'nidn', 'nip']);

            foreach ($dosensData as $dosen) {
                $existingUser = \DB::table('users')->where('email', $dosen->email)->first();
                
                $userData = [
                    'name' => $dosen->nama,
                    'employee_id' => $dosen->id,
                    'employee_type' => 'dosen', // Assuming 'dosen' or 'lecturer'? Let's check User model/enum if possible. Usually 'dosen' based on previous context.
                    'updated_at' => now(),
                ];

                if ($existingUser) {
                    \DB::table('users')->where('id', $existingUser->id)->update($userData);
                    $updatedCount++;
                } else {
                    $userData = array_merge($userData, [
                        'email' => $dosen->email,
                        'password' => bcrypt('password123'),
                        'email_verified_at' => now(),
                        'created_at' => now(),
                    ]);
                    $userId = \DB::table('users')->insertGetId($userData);

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
