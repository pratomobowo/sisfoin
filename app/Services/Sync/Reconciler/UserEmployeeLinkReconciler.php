<?php

namespace App\Services\Sync\Reconciler;

use App\Models\Dosen;
use App\Models\Employee;
use App\Models\User;

class UserEmployeeLinkReconciler
{
    /**
     * @return array{linked_count:int, conflict_count:int, conflicts:array<int, array<string, mixed>>}
     */
    public function reconcile(string $mode): array
    {
        $normalizedMode = strtolower($mode);

        return match ($normalizedMode) {
            'employee' => $this->reconcileEmployees(),
            'dosen' => $this->reconcileDosens(),
            default => ['linked_count' => 0, 'conflict_count' => 0, 'conflicts' => []],
        };
    }

    /**
     * @return array{linked_count:int, conflict_count:int, conflicts:array<int, array<string, mixed>>}
     */
    private function reconcileEmployees(): array
    {
        $linkedCount = 0;
        $conflicts = [];

        $users = User::query()
            ->where('employee_type', 'employee')
            ->whereNotNull('nip')
            ->get();

        foreach ($users as $user) {
            $matches = Employee::query()->where('nip', $user->nip)->pluck('id');
            $matchCount = $matches->count();

            if ($matchCount === 1) {
                $targetId = (string) $matches->first();
                if ((string) ($user->employee_id ?? '') !== $targetId) {
                    $user->update(['employee_id' => $targetId]);
                }

                $linkedCount++;

                continue;
            }

            if ($matchCount > 1) {
                $conflicts[] = [
                    'user_id' => $user->id,
                    'employee_type' => 'employee',
                    'nip' => $user->nip,
                    'candidate_ids' => $matches->map(fn ($id): string => (string) $id)->all(),
                ];
            }
        }

        return [
            'linked_count' => $linkedCount,
            'conflict_count' => count($conflicts),
            'conflicts' => $conflicts,
        ];
    }

    /**
     * @return array{linked_count:int, conflict_count:int, conflicts:array<int, array<string, mixed>>}
     */
    private function reconcileDosens(): array
    {
        $linkedCount = 0;
        $conflicts = [];

        $users = User::query()
            ->where('employee_type', 'dosen')
            ->whereNotNull('nip')
            ->get();

        foreach ($users as $user) {
            $matches = Dosen::query()->where('nip', $user->nip)->pluck('id');
            $matchCount = $matches->count();

            if ($matchCount === 1) {
                $targetId = (string) $matches->first();
                if ((string) ($user->employee_id ?? '') !== $targetId) {
                    $user->update(['employee_id' => $targetId]);
                }

                $linkedCount++;

                continue;
            }

            if ($matchCount > 1) {
                $conflicts[] = [
                    'user_id' => $user->id,
                    'employee_type' => 'dosen',
                    'nip' => $user->nip,
                    'candidate_ids' => $matches->map(fn ($id): string => (string) $id)->all(),
                ];
            }
        }

        return [
            'linked_count' => $linkedCount,
            'conflict_count' => count($conflicts),
            'conflicts' => $conflicts,
        ];
    }
}
