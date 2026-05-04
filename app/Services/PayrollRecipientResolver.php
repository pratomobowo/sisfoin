<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\Employee;
use App\Models\SlipGajiDetail;
use Illuminate\Support\Collection;

class PayrollRecipientResolver
{
    public function resolve(SlipGajiDetail $detail): array
    {
        $nip = trim((string) $detail->nip);

        if ($nip === '') {
            return $this->invalid('NIP slip gaji kosong');
        }

        $matches = collect()
            ->merge($this->employeeMatches($nip)->map(fn (Employee $employee) => [
                'type' => 'employee',
                'model' => $employee,
                'email' => $employee->email_kampus ?: $employee->email,
                'name' => $employee->nama_lengkap_with_gelar,
            ]))
            ->merge($this->dosenMatches($nip)->map(fn (Dosen $dosen) => [
                'type' => 'dosen',
                'model' => $dosen,
                'email' => $dosen->email_kampus ?: $dosen->email,
                'name' => $dosen->nama_lengkap_with_gelar,
            ]));

        $uniqueMatches = $this->uniqueMatches($matches);

        if ($uniqueMatches->count() === 0) {
            return $this->invalid('Data pegawai/dosen tidak ditemukan untuk NIP '.$nip);
        }

        if ($uniqueMatches->count() > 1) {
            return $this->invalid('NIP '.$nip.' cocok ke lebih dari satu pegawai/dosen');
        }

        $recipient = $uniqueMatches->first();

        if (empty($recipient['email'])) {
            return $this->invalid('Email pegawai/dosen tidak ditemukan untuk NIP '.$nip);
        }

        return [
            'valid' => true,
            'email' => $recipient['email'],
            'name' => $recipient['name'] ?: 'Karyawan',
            'type' => $recipient['type'],
            'reason' => null,
        ];
    }

    private function employeeMatches(string $nip): Collection
    {
        return Employee::query()
            ->where('nip', $nip)
            ->orWhere('nip_pns', $nip)
            ->get();
    }

    private function dosenMatches(string $nip): Collection
    {
        return Dosen::query()
            ->where('nip', $nip)
            ->orWhere('nip_pns', $nip)
            ->get();
    }

    private function uniqueMatches(Collection $matches): Collection
    {
        return $matches->unique(function (array $match) {
            return $match['type'].':'.$match['model']->getKey();
        })->values();
    }

    private function invalid(string $reason): array
    {
        return [
            'valid' => false,
            'email' => null,
            'name' => null,
            'type' => null,
            'reason' => $reason,
        ];
    }
}
