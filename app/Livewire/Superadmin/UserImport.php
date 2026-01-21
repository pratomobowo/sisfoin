<?php

namespace App\Livewire\Superadmin;

use App\Models\Dosen;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class UserImport extends Component
{
    public $showImportModal = false;
    public $importCounts = [];

    protected $listeners = [
        'showUserImportModal' => 'showImportUsers',
        'closeUserImportModal' => 'closeImportModal',
    ];

    public function render()
    {
        return view('livewire.superadmin.user-import');
    }

    public function showImportUsers()
    {
        $this->prepareImportData();
        $this->showImportModal = true;
    }

    public function closeImportModal()
    {
        $this->showImportModal = false;
        $this->importCounts = [];
    }

    public function prepareImportData()
    {
        // Try to detect the correct name column for employees
        $employeeNameColumn = $this->detectNameColumn(new Employee);
        $lecturerNameColumn = $this->detectNameColumn(new Dosen);
        
        // Count employees with email, name, and NIP (valid for import) and active status
        $employeesValid = Employee::whereNotNull('email')->where('status_aktif', 'Aktif')->whereNotNull('nip')->where('nip', '!=', '');
        if ($employeeNameColumn) {
            $employeesValid = $employeesValid->whereNotNull($employeeNameColumn)
                                             ->where($employeeNameColumn, '!=', '');
        }
        $employeesValid = $employeesValid->count();
        
        $employeesWithEmail = Employee::whereNotNull('email')->where('status_aktif', 'Aktif')->count();
        $employeesExisting = User::whereIn('email', Employee::whereNotNull('email')->where('status_aktif', 'Aktif')->pluck('email'))->count();
        $employeesNew = $employeesValid - min($employeesExisting, $employeesValid);

        // Count lecturers with email, name, and NIP (valid for import) and active status
        $lecturersValid = Dosen::whereNotNull('email')->where('status_aktif', 'Aktif')->whereNotNull('nip')->where('nip', '!=', '');
        if ($lecturerNameColumn) {
            $lecturersValid = $lecturersValid->whereNotNull($lecturerNameColumn)
                                            ->where($lecturerNameColumn, '!=', '');
        }
        $lecturersValid = $lecturersValid->count();
        
        $lecturersWithEmail = Dosen::whereNotNull('email')->where('status_aktif', 'Aktif')->count();
        $lecturersExisting = User::whereIn('email', Dosen::whereNotNull('email')->where('status_aktif', 'Aktif')->pluck('email'))->count();
        $lecturersNew = $lecturersValid - min($lecturersExisting, $lecturersValid);

        $this->importCounts = [
            'karyawan_total' => $employeesWithEmail,
            'karyawan_valid' => $employeesValid,
            'karyawan_existing' => $employeesExisting,
            'karyawan_new' => $employeesNew,
            'dosen_total' => $lecturersWithEmail,
            'dosen_valid' => $lecturersValid,
            'dosen_existing' => $lecturersExisting,
            'dosen_new' => $lecturersNew,
            'employee_name_column' => $employeeNameColumn,
            'lecturer_name_column' => $lecturerNameColumn,
        ];
    }

    private function detectNameColumn($model)
    {
        $possibleColumns = ['nama_lengkap', 'full_name', 'nama', 'name', 'nama_karyawan', 'nama_dosen', 'employee_name', 'lecturer_name'];
        
        foreach ($possibleColumns as $column) {
            try {
                // Try to select the column to see if it exists
                $test = $model->select($column)->first();
                if ($test !== null) {
                    return $column;
                }
            } catch (\Exception $e) {
                // Column doesn't exist, continue to next
                continue;
            }
        }
        
        return null; // No name column found
    }

    public function importUsers()
    {
        $staffRole = Role::where('name', 'staff')->first();
        if (! $staffRole) {
            return;
        }

        $importedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $errorMessages = [];

        // Detect the correct name columns
        $employeeNameColumn = $this->detectNameColumn(new Employee);
        $lecturerNameColumn = $this->detectNameColumn(new Dosen);

        // Import employees - only those with email, name (if name column exists), NIP, and active status
        $employeesQuery = Employee::whereNotNull('email')->where('status_aktif', 'Aktif')->whereNotNull('nip')->where('nip', '!=', '');
        if ($employeeNameColumn) {
            $employeesQuery = $employeesQuery->whereNotNull($employeeNameColumn)
                                              ->where($employeeNameColumn, '!=', '');
        }
        $employees = $employeesQuery->get();
        
        foreach ($employees as $employee) {
            try {
                $user = User::where('email', $employee->email)->first();

                if (! $user) {
                    // Create new user
                    $user = new User;
                    // Use detected name column or fallback to email if no name column
                    $userName = $employeeNameColumn ? trim($employee->{$employeeNameColumn}) : explode('@', $employee->email)[0];
                    $user->name = $userName ?: 'Unknown';
                    $user->email = $employee->email;
                    $user->password = Hash::make('password123');
                    $user->save();

                    // Assign staff role
                    $user->roles()->attach($staffRole->id);
                    $importedCount++;
                } else {
                    // Update existing user name if name column exists and name is different
                    if ($employeeNameColumn) {
                        $newName = trim($employee->{$employeeNameColumn});
                        if ($newName && $user->name !== $newName) {
                            $user->name = $newName;
                            $user->save();
                            $updatedCount++;
                        }
                    }
                }
            } catch (\Exception $e) {
                $skippedCount++;
                $errorMessages[] = "Gagal import karyawan {$employee->email}: " . $e->getMessage();
            }
        }

        // Import lecturers - only those with email, name (if name column exists), NIP, and active status
        $lecturersQuery = Dosen::whereNotNull('email')->where('status_aktif', 'Aktif')->whereNotNull('nip')->where('nip', '!=', '');
        if ($lecturerNameColumn) {
            $lecturersQuery = $lecturersQuery->whereNotNull($lecturerNameColumn)
                                             ->where($lecturerNameColumn, '!=', '');
        }
        $lecturers = $lecturersQuery->get();
        
        foreach ($lecturers as $lecturer) {
            try {
                $user = User::where('email', $lecturer->email)->first();

                if (! $user) {
                    // Create new user
                    $user = new User;
                    // Use detected name column or fallback to email if no name column
                    $userName = $lecturerNameColumn ? trim($lecturer->{$lecturerNameColumn}) : explode('@', $lecturer->email)[0];
                    $user->name = $userName ?: 'Unknown';
                    $user->email = $lecturer->email;
                    $user->password = Hash::make('password123');
                    $user->save();

                    // Assign staff role
                    $user->roles()->attach($staffRole->id);
                    $importedCount++;
                } else {
                    // Update existing user name if name column exists and name is different
                    if ($lecturerNameColumn) {
                        $newName = trim($lecturer->{$lecturerNameColumn});
                        if ($newName && $user->name !== $newName) {
                            $user->name = $newName;
                            $user->save();
                            $updatedCount++;
                        }
                    }
                }
            } catch (\Exception $e) {
                $skippedCount++;
                $errorMessages[] = "Gagal import dosen {$lecturer->email}: " . $e->getMessage();
            }
        }

        $this->showImportModal = false;

        // Log error messages if any
        if (!empty($errorMessages)) {
            \Log::error('Import users errors:', $errorMessages);
        }

        $this->dispatch('usersImported');
    }
}
