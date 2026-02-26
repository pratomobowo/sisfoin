<?php

namespace App\Imports;

use App\Models\SlipGajiDetail;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SlipGajiImport implements ToModel, WithBatchInserts, WithChunkReading, WithColumnFormatting, WithHeadingRow
{
    protected $headerId;

    protected $errors = [];

    protected $rowCount = 0;

    public function __construct($headerId)
    {
        $this->headerId = $headerId;
    }

    public function headings(): array
    {
        return [
            'STATUS',
            'NIP',
            // PENDAPATAN
            'GAJI POKOK',
            'TPP',
            'TUNJANGAN KELUARGA',
            'TUNJANGAN KEMAHALAN',
            'TUNJANGAN KESEHATAN',
            'TUNJANGAN KENAIKAN MAHASISWA',
            'TUNJANGAN GOLONGAN',
            'MASA KERJA',
            'TRANSPORT',
            'TUNJANGAN RUMAH',
            'TUNJANGAN JABATAN',
            'TUNJANGAN PENDIDIKAN',
            'HONOR TETAP',
            'INSENTIF GOLONGAN',
            'TUNJANGAN FUNGSIONAL',
            'TUNJANGAN STRUKTURAL',
            'BEBAN MANAJEMEN',
            'HONOR TUNAI',
            'PENERIMAAN KOTOR',

            // POTONGAN
            'POTONGAN ARISAN',
            'POTONGAN KOPERASI',
            'POTONGAN LAZMAAL',
            'POTONGAN BPJS KESEHATAN',
            'POTONGAN BPJS KETENAGAKERJAAN',
            'POTONGAN BKD',

            // PAJAK
            'PPh 21 TERHUTANG',
            'PPh 21 SUDAH DIPOTONG',
            'PPh 21 KURANG DIPOTONG',
            'PAJAK',

            // TOTAL
            'GAJI BERSIH',
        ];
    }

    /**
     * @return SlipGajiDetail|null
     */
    public function model(array $row)
    {
        $this->rowCount++;

        try {
            // Validate required fields - NIP is required
            $nipValue = $this->getValueFromRow($row, 'nip');
            if (empty($nipValue)) {
                // Don't add error for completely empty rows
                if (count(array_filter($row)) === 0) {
                    return null; // Skip empty rows
                }

                $this->errors[] = "Row {$this->rowCount}: NIP is required";

                return null;
            }

            $detail = new SlipGajiDetail([
                'header_id' => $this->headerId,
                'status' => $this->getValueFromRow($row, 'status'),
                'nip' => $this->formatNip($this->getValueFromRow($row, 'nip')),

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
            ]);

            return $detail;

        } catch (\Exception $e) {
            $this->errors[] = "Row {$this->rowCount}: ".$e->getMessage();
            Log::error("Import error at row {$this->rowCount}: ".$e->getMessage(), ['row' => $row]);

            return null;
        }
    }

    /**
     * Get value from row with flexible key matching
     *
     * @return mixed
     */
    private function getValueFromRow(array $row, string $key)
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

    /**
     * Parse numeric values from Excel cells
     * Handles various formats including currency and formatted numbers
     *
     * @param  mixed  $value
     * @return float|string
     */
    private function parseNumeric($value)
    {
        // Handle null or empty values
        if ($value === null || $value === '' || $value === 'NULL') {
            return 0.0;
        }

        // If it's already a string that looks like a large integer, keep it as is
        if (is_string($value) && ctype_digit($value) && strlen($value) > 15) {
            return $value;
        }

        // Convert to string for processing
        $stringValue = (string) $value;

        // If the value looks like scientific notation for a large integer,
        // try to convert it back to full number string
        if (preg_match('/^(\d+)(?:\.(\d+))?E\+(\d+)$/', $stringValue, $matches)) {
            $base = $matches[1];
            $decimal = isset($matches[2]) ? $matches[2] : '';
            $exponent = (int) $matches[3];

            // If this is likely an integer that lost precision
            if (strlen($base.$decimal) <= 15 && $exponent > 15) {
                // Reconstruct the full number
                $fullNumber = $base.$decimal;
                $zerosToAdd = $exponent - strlen($fullNumber);
                if ($zerosToAdd > 0) {
                    $fullNumber .= str_repeat('0', $zerosToAdd);

                    return $fullNumber;
                }
            }
        }

        // Remove currency symbols, commas, and spaces
        $stringValue = preg_replace('/[^\d.,\-]/', '', $stringValue);

        // Handle different decimal separators
        // If there's a comma and it appears after any dot, it's likely a thousands separator
        if (strpos($stringValue, ',') !== false && strpos($stringValue, '.') !== false) {
            if (strrpos($stringValue, ',') > strrpos($stringValue, '.')) {
                // Comma is decimal separator
                $stringValue = str_replace('.', '', $stringValue);
                $stringValue = str_replace(',', '.', $stringValue);
            } else {
                // Dot is decimal separator
                $stringValue = str_replace(',', '', $stringValue);
            }
        } elseif (strpos($stringValue, ',') !== false) {
            // Only comma present, check if it's likely a decimal separator
            if (strlen(substr($stringValue, strrpos($stringValue, ',') + 1)) <= 2) {
                // Likely decimal separator
                $stringValue = str_replace(',', '.', $stringValue);
            } else {
                // Likely thousands separator
                $stringValue = str_replace(',', '', $stringValue);
            }
        }

        // Remove any remaining non-numeric characters except decimal point and minus
        $stringValue = preg_replace('/[^\d.\-]/', '', $stringValue);

        // Handle empty string after cleaning
        if ($stringValue === '' || $stringValue === '-' || $stringValue === '.' || $stringValue === '-.') {
            return 0.0;
        }

        // For very large numbers that might lose precision as float,
        // return as string if they appear to be integer-like
        if (strlen($stringValue) > 15 && strpos($stringValue, '.') === false) {
            // This looks like a large integer that might lose precision as float
            // Return as string to preserve all digits
            return $stringValue;
        }

        // Special handling for numbers that are represented in scientific notation
        // but are actually large integers
        if (strpos($stringValue, 'E') !== false || strpos($stringValue, 'e') !== false) {
            $floatVal = (float) $stringValue;
            // If the float value is a whole number and large, return as string
            if ($floatVal == floor($floatVal) && $floatVal > 999999999999999) {
                return number_format($floatVal, 0, '', '');
            }
        }

        return (float) $stringValue;
    }

    /**
     * Format NIP to ensure it preserves all digits
     *
     * @param  mixed  $nip
     */
    private function formatNip($nip): ?string
    {
        if ($nip === null || $nip === '') {
            return null;
        }

        // Convert to string and normalize whitespace/quotes from Excel exports
        $nipString = trim((string) $nip);
        $nipString = ltrim($nipString, "'\"");

        // If already pure digits, return as-is to preserve exact value.
        // NEVER cast long identifiers to float (precision loss on 16+ digits).
        if ($nipString !== '' && ctype_digit($nipString)) {
            return $nipString;
        }

        // If it's in scientific notation, convert it back
        if (preg_match('/^(\d+)(?:\.(\d+))?E\+(\d+)$/i', $nipString, $matches)) {
            $base = $matches[1];
            $decimal = isset($matches[2]) ? $matches[2] : '';
            $exponent = (int) $matches[3];

            // Reconstruct the full NIP
            $fullNip = $base.$decimal;
            $zerosToAdd = $exponent - strlen($fullNip);
            if ($zerosToAdd > 0) {
                $fullNip .= str_repeat('0', $zerosToAdd);
            }

            return $fullNip;
        }

        // Fallback: keep digits only for mixed artifacts (spaces, separators, etc.)
        $digitsOnly = preg_replace('/\D+/', '', $nipString);
        if (! empty($digitsOnly)) {
            return $digitsOnly;
        }

        return $nipString;
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    /**
     * Format columns to ensure NIP is treated as text
     */
    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT, // NIP column as text
        ];
    }

    /**
     * Get any errors that occurred during import
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get the row count
     */
    public function getRowCount(): int
    {
        return $this->rowCount;
    }
}
