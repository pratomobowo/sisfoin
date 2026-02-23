<?php

namespace App\Livewire\Superadmin;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

    public function openModal(): void
    {
        $this->showModal = true;
        $this->prepareImportData();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->importCounts = [];
    }

    public function prepareImportData(): void
    {
        $this->isLoading = true;

        try {
            $employeesTotal = DB::table('employees')->where('status_aktif', 'Aktif')->count();

            $employeesData = DB::table('employees')
                ->where('status_aktif', 'Aktif')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->whereNotNull('nip')
                ->where('nip', '!=', '')
                ->get(['id', 'nama', 'email', 'nip']);

            $employeesReady = $employeesData->count();
            $employeesSkipped = $employeesTotal - $employeesReady;

            $employeesNew = 0;
            $employeesExisting = 0;

            foreach ($employeesData as $employee) {
                $existingUser = $this->findExistingUser('employee', (int) $employee->id, $employee->nip, $employee->email);
                if ($existingUser) {
                    $employeesExisting++;
                } else {
                    $employeesNew++;
                }
            }

            $dosensTotal = DB::table('dosens')->where('status_aktif', 'Aktif')->count();

            $dosensData = DB::table('dosens')
                ->where('status_aktif', 'Aktif')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->whereNotNull('nip')->where('nip', '!=', '');
                    })->orWhere(function ($q) {
                        $q->whereNotNull('nidn')->where('nidn', '!=', '');
                    });
                })
                ->get(['id', 'nama', 'email', 'nidn', 'nip']);

            $dosensReady = $dosensData->count();
            $dosensSkipped = $dosensTotal - $dosensReady;

            $dosensNew = 0;
            $dosensExisting = 0;

            foreach ($dosensData as $dosen) {
                $identityNip = $dosen->nip ?: $dosen->nidn;
                $existingUser = $this->findExistingUser('dosen', (int) $dosen->id, $identityNip, $dosen->email);
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
            Log::error('Error preparing import data: '.$e->getMessage());
            $this->importCounts = [];
        }

        $this->isLoading = false;
    }

    public function importUsers(): void
    {
        try {
            $importedCount = 0;
            $updatedCount = 0;
            $conflictCount = 0;

            $staffRole = DB::table('roles')->where('name', 'staff')->first();
            if (! $staffRole) {
                throw new \Exception('Staff role not found');
            }

            $employeesData = DB::table('employees')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->whereNotNull('nip')
                ->where('nip', '!=', '')
                ->where('status_aktif', 'Aktif')
                ->get(['id', 'nama', 'email', 'nip']);

            foreach ($employeesData as $employee) {
                $existingUser = $this->findExistingUser('employee', (int) $employee->id, $employee->nip, $employee->email);

                $userData = [
                    'name' => $employee->nama,
                    'nip' => $employee->nip,
                    'email' => $employee->email,
                    'employee_id' => $employee->id,
                    'employee_type' => 'employee',
                    'updated_at' => now(),
                ];

                if ($existingUser) {
                    if ($this->hasConflictingOwnership($existingUser, 'employee', (int) $employee->id)) {
                        $conflictCount++;

                        continue;
                    }

                    DB::table('users')->where('id', $existingUser->id)->update($userData);
                    $this->ensureStaffRole((int) $existingUser->id, (int) $staffRole->id);
                    $updatedCount++;
                } else {
                    $createData = array_merge($userData, [
                        'password' => Hash::make('password123'),
                        'email_verified_at' => now(),
                        'created_at' => now(),
                    ]);

                    $userId = DB::table('users')->insertGetId($createData);
                    $this->ensureStaffRole((int) $userId, (int) $staffRole->id);
                    $importedCount++;
                }
            }

            $dosensData = DB::table('dosens')
                ->where('status_aktif', 'Aktif')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->whereNotNull('nip')->where('nip', '!=', '');
                    })->orWhere(function ($q) {
                        $q->whereNotNull('nidn')->where('nidn', '!=', '');
                    });
                })
                ->get(['id', 'nama', 'email', 'nidn', 'nip']);

            foreach ($dosensData as $dosen) {
                $identityNip = $dosen->nip ?: $dosen->nidn;
                $existingUser = $this->findExistingUser('dosen', (int) $dosen->id, $identityNip, $dosen->email);

                $userData = [
                    'name' => $dosen->nama,
                    'nip' => $identityNip,
                    'email' => $dosen->email,
                    'employee_id' => $dosen->id,
                    'employee_type' => 'dosen',
                    'updated_at' => now(),
                ];

                if ($existingUser) {
                    if ($this->hasConflictingOwnership($existingUser, 'dosen', (int) $dosen->id)) {
                        $conflictCount++;

                        continue;
                    }

                    DB::table('users')->where('id', $existingUser->id)->update($userData);
                    $this->ensureStaffRole((int) $existingUser->id, (int) $staffRole->id);
                    $updatedCount++;
                } else {
                    $createData = array_merge($userData, [
                        'password' => Hash::make('password123'),
                        'email_verified_at' => now(),
                        'created_at' => now(),
                    ]);

                    $userId = DB::table('users')->insertGetId($createData);
                    $this->ensureStaffRole((int) $userId, (int) $staffRole->id);
                    $importedCount++;
                }
            }

            $this->closeModal();

            $message = 'Import selesai! ';
            if ($importedCount > 0) {
                $message .= "{$importedCount} pengguna baru dibuat. ";
            }
            if ($updatedCount > 0) {
                $message .= "{$updatedCount} pengguna diperbarui.";
            }
            if ($conflictCount > 0) {
                $message .= " {$conflictCount} data dilewati karena konflik kepemilikan akun (dicek aman).";
            }

            session()->flash('success', $message);
            $this->dispatch('usersImported');
        } catch (\Exception $e) {
            $this->closeModal();
            session()->flash('error', 'Gagal melakukan import: '.$e->getMessage());
        }
    }

    private function findExistingUser(string $type, int $employeeId, ?string $nip, ?string $email): ?User
    {
        $nip = $this->normalizeIdentity($nip);
        $email = $this->normalizeIdentity($email);

        $byOwnership = User::query()
            ->where('employee_type', $type)
            ->where('employee_id', $employeeId)
            ->first();

        if ($byOwnership) {
            return $byOwnership;
        }

        if ($nip) {
            $byNip = User::query()->where('nip', $nip)->first();
            if ($byNip) {
                return $byNip;
            }
        }

        if ($email) {
            return User::query()->where('email', $email)->first();
        }

        return null;
    }

    private function hasConflictingOwnership(User $user, string $expectedType, int $expectedEmployeeId): bool
    {
        if (empty($user->employee_type) || empty($user->employee_id)) {
            return false;
        }

        return ! ($user->employee_type === $expectedType && (int) $user->employee_id === $expectedEmployeeId);
    }

    private function ensureStaffRole(int $userId, int $staffRoleId): void
    {
        $exists = DB::table('model_has_roles')
            ->where('role_id', $staffRoleId)
            ->where('model_type', 'App\\Models\\User')
            ->where('model_id', $userId)
            ->exists();

        if (! $exists) {
            DB::table('model_has_roles')->insert([
                'role_id' => $staffRoleId,
                'model_type' => 'App\\Models\\User',
                'model_id' => $userId,
            ]);
        }
    }

    private function normalizeIdentity(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    public function render()
    {
        return view('livewire.superadmin.user-import-modal');
    }
}
