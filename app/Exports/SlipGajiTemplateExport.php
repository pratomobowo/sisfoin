<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SlipGajiTemplateExport implements FromArray, WithColumnFormatting, WithColumnWidths, WithHeadings, WithStyles, WithTitle
{
    public function array(): array
    {
        // Return sample data for template
        return [
            [
                'DOSEN_PK', // status (Contoh: KARYAWAN_TETAP, DOSEN_TETAP, DOSEN_PK, dll)
                '123456789', // nip
                // PENDAPATAN
                5000000, // gaji_pokok
                2000000, // honor_tetap
                1500000, // tpp
                500000, // insentif_golongan
                1000000, // tunjangan_keluarga
                300000, // tunjangan_kemahalan
                200000, // tunjangan_pmb
                800000, // tunjangan_golongan
                500000, // tunjangan_masa_kerja
                200000, // transport
                500000, // tunjangan_kesehatan
                1200000, // tunjangan_rumah
                1500000, // tunjangan_pendidikan
                2000000, // tunjangan_struktural
                1500000, // tunjangan_fungsional
                1000000, // beban_manajemen
                500000, // honor_tunai
                16800000, // penerimaan_kotor

                // POTONGAN
                50000, // potongan_arisan
                100000, // potongan_koperasi
                30000, // potongan_lazmaal
                200000, // potongan_bpjs_kesehatan
                150000, // potongan_bpjs_ketenagakerjaan
                100000, // potongan_bkd

                // PAJAK
                100000, // pajak
                500000, // pph21_terhutang
                300000, // pph21_sudah_dipotong
                200000, // pph21_kurang_dipotong

                // TOTAL
                14000000, // penerimaan_bersih
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'status',
            'nip',
            // PENDAPATAN
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
            'penerimaan_kotor',

            // POTONGAN
            'potongan_arisan',
            'potongan_koperasi',
            'potongan_lazmaal',
            'potongan_bpjs_kesehatan',
            'potongan_bpjs_ketenagakerjaan',
            'potongan_bkd',

            // PAJAK
            'pajak',
            'pph21_terhutang',
            'pph21_sudah_dipotong',
            'pph21_kurang_dipotong',

            // TOTAL
            'penerimaan_bersih',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style the first row (headings)
        $sheet->getStyle('A1:AE1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563EB'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Style the data rows
        $sheet->getStyle('A2:AE2')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Set number format for numeric columns
        $numericColumns = [
            'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T',
            'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE',
        ];

        foreach ($numericColumns as $column) {
            $sheet->getStyle("{$column}2")->getNumberFormat()->setFormatCode('#,##0.00');
        }

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20, // status
            'B' => 15, // nip
            // PENDAPATAN
            'C' => 15, // gaji_pokok
            'D' => 15, // honor_tetap
            'E' => 15, // tpp
            'F' => 20, // insentif_golongan
            'G' => 20, // tunjangan_keluarga
            'H' => 20, // tunjangan_kemahalan
            'I' => 15, // tunjangan_pmb
            'J' => 20, // tunjangan_golongan
            'K' => 20, // tunjangan_masa_kerja
            'L' => 12, // transport
            'M' => 20, // tunjangan_kesehatan
            'N' => 18, // tunjangan_rumah
            'O' => 20, // tunjangan_pendidikan
            'P' => 20, // tunjangan_struktural
            'Q' => 20, // tunjangan_fungsional
            'R' => 20, // beban_manajemen
            'S' => 15, // honor_tunai
            'T' => 20, // penerimaan_kotor

            // POTONGAN
            'U' => 20, // potongan_arisan
            'V' => 20, // potongan_koperasi
            'W' => 15, // potongan_lazmaal
            'X' => 25, // potongan_bpjs_kesehatan
            'Y' => 30, // potongan_bpjs_ketenagakerjaan
            'Z' => 15, // potongan_bkd

            // PAJAK
            'AA' => 10, // pajak
            'AB' => 20, // pph21_terhutang
            'AC' => 25, // pph21_sudah_dipotong
            'AD' => 25, // pph21_kurang_dipotong

            // TOTAL
            'AE' => 20,  // penerimaan_bersih
        ];
    }

    public function title(): string
    {
        return 'Template Slip Gaji';
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
}
