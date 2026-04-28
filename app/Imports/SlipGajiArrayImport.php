<?php

namespace App\Imports;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SlipGajiArrayImport implements ToArray, WithHeadingRow
{
    protected array $errors = [];

    protected int $rowCount = 0;

    public function array(array $rows): array
    {
        $data = [];

        foreach ($rows as $index => $row) {
            $this->rowCount++;
            $rowNumber = $index + 2; // +2 karena heading row di baris 1

            try {
                // Skip completely empty rows
                if (count(array_filter($row)) === 0) {
                    continue;
                }

                $nipValue = $this->getValueFromRow($row, 'nip');
                if (empty($nipValue)) {
                    $this->errors[] = "Baris {$rowNumber}: NIP wajib diisi";
                    continue;
                }

                $data[] = [
                    'status' => $this->getValueFromRow($row, 'status'),
                    'nip' => $this->formatNip($nipValue),

                    // PENDAPATAN
                    'gaji_pokok' => $this->parseNumeric($this->getValueFromRow($row, 'gaji_pokok') ?? 0),
                    'honor_tetap' => $this->parseNumeric($this->getValueFromRow($row, 'honor_tetap') ?? 0),
                    'tpp' => $this->parseNumeric($this->getValueFromRow($row, 'tpp') ?? 0),
                    'insentif_golongan' => $this->parseNumeric($this->getValueFromRow($row, 'insentif_golongan') ?? 0),
                    'tunjangan_keluarga' => $this->parseNumeric($this->getValueFromRow($row, 'tunjangan_keluarga') ?? 0),
                    'tunjangan_kemahalan' => $this->parseNumeric($this->getValueFromRow($row, 'tunjangan_kemahalan') ?? 0),
                    'tunjangan_pmb' => $this->parseNumeric($this->getValueFromRow($row, 'tunjangan_pmb') ?? 0),
                    'tunjangan_golongan' => $this->parseNumeric($this->getValueFromRow($row, 'tunjangan_golongan') ?? 0),
                    'tunjangan_masa_kerja' => $this->parseNumeric($this->getValueFromRow($row, 'tunjangan_masa_kerja') ?? 0),
                    'transport' => $this->parseNumeric($this->getValueFromRow($row, 'transport') ?? 0),
                    'tunjangan_kesehatan' => $this->parseNumeric($this->getValueFromRow($row, 'tunjangan_kesehatan') ?? 0),
                    'tunjangan_rumah' => $this->parseNumeric($this->getValueFromRow($row, 'tunjangan_rumah') ?? 0),
                    'tunjangan_pendidikan' => $this->parseNumeric($this->getValueFromRow($row, 'tunjangan_pendidikan') ?? 0),
                    'tunjangan_struktural' => $this->parseNumeric($this->getValueFromRow($row, 'tunjangan_struktural') ?? 0),
                    'tunjangan_fungsional' => $this->parseNumeric($this->getValueFromRow($row, 'tunjangan_fungsional') ?? 0),
                    'beban_manajemen' => $this->parseNumeric($this->getValueFromRow($row, 'beban_manajemen') ?? 0),
                    'honor_tunai' => $this->parseNumeric($this->getValueFromRow($row, 'honor_tunai') ?? 0),
                    'penerimaan_kotor' => $this->parseNumeric($this->getValueFromRow($row, 'penerimaan_kotor') ?? 0),

                    // POTONGAN
                    'potongan_arisan' => $this->parseNumeric($this->getValueFromRow($row, 'potongan_arisan') ?? 0),
                    'potongan_koperasi' => $this->parseNumeric($this->getValueFromRow($row, 'potongan_koperasi') ?? 0),
                    'potongan_lazmaal' => $this->parseNumeric($this->getValueFromRow($row, 'potongan_lazmaal') ?? 0),
                    'potongan_bpjs_kesehatan' => $this->parseNumeric($this->getValueFromRow($row, 'potongan_bpjs_kesehatan') ?? 0),
                    'potongan_bpjs_ketenagakerjaan' => $this->parseNumeric($this->getValueFromRow($row, 'potongan_bpjs_ketenagakerjaan') ?? 0),
                    'potongan_bkd' => $this->parseNumeric($this->getValueFromRow($row, 'potongan_bkd') ?? 0),

                    // PAJAK
                    'pajak' => $this->parseNumeric($this->getValueFromRow($row, 'pajak') ?? 0),
                    'pph21_terhutang' => $this->parseNumeric($this->getValueFromRow($row, 'pph21_terhutang') ?? 0),
                    'pph21_sudah_dipotong' => $this->parseNumeric($this->getValueFromRow($row, 'pph21_sudah_dipotong') ?? 0),
                    'pph21_kurang_dipotong' => $this->parseNumeric($this->getValueFromRow($row, 'pph21_kurang_dipotong') ?? 0),

                    // TOTAL
                    'penerimaan_bersih' => $this->parseNumeric($this->getValueFromRow($row, 'penerimaan_bersih') ?? 0),
                ];
            } catch (\Exception $e) {
                $this->errors[] = "Baris {$rowNumber}: " . $e->getMessage();
                Log::error("Import error at row {$rowNumber}: " . $e->getMessage(), ['row' => $row]);
            }
        }

        return $data;
    }

    private function getValueFromRow(array $row, string $key): mixed
    {
        // Exact match first
        if (array_key_exists($key, $row)) {
            return $row[$key];
        }

        // Try case-insensitive match
        foreach ($row as $rowKey => $value) {
            if (strtolower($rowKey) === strtolower($key)) {
                return $value;
            }
        }

        // Try fuzzy match (remove spaces, underscores, hyphens)
        foreach ($row as $rowKey => $value) {
            $cleanRowKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $rowKey));
            $cleanKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $key));
            if ($cleanRowKey === $cleanKey) {
                return $value;
            }
        }

        return null;
    }

    private function parseNumeric($value): float|string
    {
        if ($value === null || $value === '' || $value === 'NULL') {
            return 0.0;
        }

        if (is_string($value) && ctype_digit($value) && strlen($value) > 15) {
            return $value;
        }

        $stringValue = (string) $value;

        if (preg_match('/^(\d+)(?:\.(\d+))?E\+(\d+)$/i', $stringValue, $matches)) {
            $base = $matches[1];
            $decimal = $matches[2] ?? '';
            $exponent = (int) $matches[3];

            if (strlen($base . $decimal) <= 15 && $exponent > 15) {
                $fullNumber = $base . $decimal;
                $zerosToAdd = $exponent - strlen($fullNumber);
                if ($zerosToAdd > 0) {
                    $fullNumber .= str_repeat('0', $zerosToAdd);
                    return $fullNumber;
                }
            }
        }

        $stringValue = preg_replace('/[^\d.,\-]/', '', $stringValue);

        if (strpos($stringValue, ',') !== false && strpos($stringValue, '.') !== false) {
            if (strrpos($stringValue, ',') > strrpos($stringValue, '.')) {
                $stringValue = str_replace('.', '', $stringValue);
                $stringValue = str_replace(',', '.', $stringValue);
            } else {
                $stringValue = str_replace(',', '', $stringValue);
            }
        } elseif (strpos($stringValue, ',') !== false) {
            if (strlen(substr($stringValue, strrpos($stringValue, ',') + 1)) <= 2) {
                $stringValue = str_replace(',', '.', $stringValue);
            } else {
                $stringValue = str_replace(',', '', $stringValue);
            }
        }

        $stringValue = preg_replace('/[^\d.\-]/', '', $stringValue);

        if ($stringValue === '' || $stringValue === '-' || $stringValue === '.' || $stringValue === '-.') {
            return 0.0;
        }

        if (strlen($stringValue) > 15 && strpos($stringValue, '.') === false) {
            return $stringValue;
        }

        if (strpos($stringValue, 'E') !== false || strpos($stringValue, 'e') !== false) {
            $floatVal = (float) $stringValue;
            if ($floatVal == floor($floatVal) && $floatVal > 999999999999999) {
                return number_format($floatVal, 0, '', '');
            }
        }

        return (float) $stringValue;
    }

    private function formatNip($nip): ?string
    {
        if ($nip === null || $nip === '') {
            return null;
        }

        $nipString = trim((string) $nip);
        $nipString = ltrim($nipString, "'\"");

        if ($nipString !== '' && ctype_digit($nipString)) {
            return $nipString;
        }

        if (preg_match('/^(\d+)(?:\.(\d+))?E\+(\d+)$/i', $nipString, $matches)) {
            $base = $matches[1];
            $decimal = $matches[2] ?? '';
            $exponent = (int) $matches[3];

            $fullNip = $base . $decimal;
            $zerosToAdd = $exponent - strlen($fullNip);
            if ($zerosToAdd > 0) {
                $fullNip .= str_repeat('0', $zerosToAdd);
            }

            return $fullNip;
        }

        $digitsOnly = preg_replace('/\D+/', '', $nipString);
        if (!empty($digitsOnly)) {
            return $digitsOnly;
        }

        return $nipString;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }
}
