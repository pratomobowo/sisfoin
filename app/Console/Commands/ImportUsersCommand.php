<?php

namespace App\Console\Commands;

use App\Models\Dosen;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ImportUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:import {--force : Force import even if users already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import employees and dosens as users with staff role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting user import process...');

        $force = $this->option('force');

        // Get staff role
        $staffRole = Role::where('name', 'staff')->first();
        if (! $staffRole) {
            $this->error('Role "staff" not found. Please create it first.');

            return 1;
        }

        $results = [
            'employees_imported' => 0,
            'dosens_imported' => 0,
            'employees_updated' => 0,
            'dosens_updated' => 0,
            'employees_skipped' => 0,
            'dosens_skipped' => 0,
            'errors' => [],
        ];

        try {
            DB::beginTransaction();

            // Import Employees
            $this->info('Importing employees...');
            $employees = Employee::whereNotNull('nip')
                ->where('nip', '!=', '')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->where('status_aktif', 'Aktif')
                ->get();

            $bar = $this->output->createProgressBar($employees->count());
            $bar->start();

            foreach ($employees as $employee) {
                try {
                    $existingUser = User::where('nip', $employee->nip)->first();

                    $name = trim($employee->nama_lengkap_with_gelar);
                    // Prioritize campus email over personal email
                    $email = $employee->email_kampus ?: ($employee->email ?: $employee->nip.'@usbypkp.ac.id');

                    if ($existingUser) {
                        if ($force) {
                            $existingUser->update([
                                'name' => $name,
                                'email' => $email,
                                'employee_type' => 'employee',
                                'employee_id' => $employee->id,
                            ]);

                            if (! $existingUser->hasRole('staff')) {
                                $existingUser->assignRole('staff');
                            }

                            $results['employees_updated']++;
                        } else {
                            $results['employees_skipped']++;
                        }
                    } else {
                        $user = User::create([
                            'name' => $name,
                            'email' => $email,
                            'nip' => $employee->nip,
                            'password' => Hash::make('ypkp@#1234'),
                            'employee_type' => 'employee',
                            'employee_id' => $employee->id,
                            'email_verified_at' => now(),
                        ]);

                        $user->assignRole('staff');
                        $results['employees_imported']++;
                    }
                } catch (\Exception $e) {
                    $results['employees_skipped']++;
                    $results['errors'][] = "Employee {$employee->nama_lengkap} (NIP: {$employee->nip}): ".$e->getMessage();
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            // Import Dosens
            $this->info('Importing dosens...');
            $dosens = Dosen::whereNotNull('nip')
                ->where('nip', '!=', '')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->where('status_aktif', 'Aktif')
                ->get();

            $bar = $this->output->createProgressBar($dosens->count());
            $bar->start();

            foreach ($dosens as $dosen) {
                try {
                    $existingUser = User::where('nip', $dosen->nip)->first();

                    $name = trim($dosen->nama_lengkap_with_gelar);
                    // Prioritize campus email over personal email
                    $email = $dosen->email_kampus ?: ($dosen->email ?: $dosen->nip.'@usbypkp.ac.id');

                    if ($existingUser) {
                        if ($force) {
                            $existingUser->update([
                                'name' => $name,
                                'email' => $email,
                                'employee_type' => 'dosen',
                                'employee_id' => $dosen->id,
                            ]);

                            if (! $existingUser->hasRole('staff')) {
                                $existingUser->assignRole('staff');
                            }

                            $results['dosens_updated']++;
                        } else {
                            $results['dosens_skipped']++;
                        }
                    } else {
                        $user = User::create([
                            'name' => $name,
                            'email' => $email,
                            'nip' => $dosen->nip,
                            'password' => Hash::make('ypkp@#1234'),
                            'employee_type' => 'dosen',
                            'employee_id' => $dosen->id,
                            'email_verified_at' => now(),
                        ]);

                        $user->assignRole('staff');
                        $results['dosens_imported']++;
                    }
                } catch (\Exception $e) {
                    $results['dosens_skipped']++;
                    $results['errors'][] = "Dosen {$dosen->nama} (NIP: {$dosen->nip}): ".$e->getMessage();
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            DB::commit();

            // Display results
            $this->info('Import completed successfully!');
            $this->table(
                ['Type', 'Imported', 'Updated', 'Skipped'],
                [
                    ['Employees', $results['employees_imported'], $results['employees_updated'], $results['employees_skipped']],
                    ['Dosens', $results['dosens_imported'], $results['dosens_updated'], $results['dosens_skipped']],
                    ['Total', $results['employees_imported'] + $results['dosens_imported'],
                        $results['employees_updated'] + $results['dosens_updated'],
                        $results['employees_skipped'] + $results['dosens_skipped']],
                ]
            );

            if (! empty($results['errors'])) {
                $this->warn('Errors encountered:');
                foreach (array_slice($results['errors'], 0, 10) as $error) {
                    $this->line('- '.$error);
                }
                if (count($results['errors']) > 10) {
                    $this->line('... and '.(count($results['errors']) - 10).' more errors');
                }
            }

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Import failed: '.$e->getMessage());

            return 1;
        }
    }
}
