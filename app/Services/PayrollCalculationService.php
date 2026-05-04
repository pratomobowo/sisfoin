<?php

namespace App\Services;

class PayrollCalculationService
{
    private const EARNING_FIELDS = [
        'gaji_pokok',
        'honor_tetap',
        'tpp',
        'insentif_golongan',
        'tunjangan_keluarga',
        'tunjangan_kemahalan',
        'tunjangan_pmb',
        'tunjangan_golongan',
        'tunjangan_masa_kerja',
        'transport',
        'tunjangan_kesehatan',
        'tunjangan_rumah',
        'tunjangan_pendidikan',
        'tunjangan_struktural',
        'tunjangan_fungsional',
        'beban_manajemen',
        'honor_tunai',
    ];

    private const DEDUCTION_FIELDS = [
        'potongan_arisan',
        'potongan_koperasi',
        'potongan_lazmaal',
        'potongan_bpjs_kesehatan',
        'potongan_bpjs_ketenagakerjaan',
        'potongan_bkd',
        'pajak',
        'pph21_kurang_dipotong',
    ];

    public function reconcile(array $data): array
    {
        $gross = $this->sumFields($data, self::EARNING_FIELDS);
        $deductions = $this->sumFields($data, self::DEDUCTION_FIELDS);
        $net = max(0, $gross - $deductions);

        $data['penerimaan_kotor'] = $gross;
        $data['total_potongan'] = $deductions;
        $data['penerimaan_bersih'] = $net;
        $data['gaji_bersih'] = $net;

        return $data;
    }

    private function sumFields(array $data, array $fields): float
    {
        return array_reduce($fields, function (float $sum, string $field) use ($data) {
            return $sum + (float) ($data[$field] ?? 0);
        }, 0.0);
    }
}
