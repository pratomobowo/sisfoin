<?php

namespace App\Exports;

use App\Models\SlipGajiDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SlipGajiDataExport implements FromCollection, WithMapping, WithColumnFormatting, WithColumnWidths, WithHeadings, WithStyles, WithTitle
{
    protected $headerId;

    public function __construct(int $headerId)
    {
        $this->headerId = $headerId;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return SlipGajiDetail::where('header_id', $this->headerId)->get();
    }

    public function title(): string
    {
        return 'Data Slip Gaji';
    }

    public function headings(): array
    {
        return [
            'status',
            'nip',
            'nama',
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

    public function map($detail): array
    {
        return [
            $detail->status,
            $detail->nip,
            $detail->nama_from_relation,
            // PENDAPATAN
            $detail->gaji_pokok,
            $detail->honor_tetap,
            $detail->tpp,
            $detail->insentif_golongan,
            $detail->tunjangan_keluarga,
            $detail->tunjangan_kemahalan,
            $detail->tunjangan_pmb,
            $detail->tunjangan_golongan,
            $detail->tunjangan_masa_kerja,
            $detail->transport,
            $detail->tunjangan_kesehatan,
            $detail->tunjangan_rumah,
            $detail->tunjangan_pendidikan,
            $detail->tunjangan_struktural,
            $detail->tunjangan_fungsional,
            $detail->beban_manajemen,
            $detail->honor_tunai,
            $detail->penerimaan_kotor,

            // POTONGAN
            $detail->potongan_arisan,
            $detail->potongan_koperasi,
            $detail->potongan_lazmaal,
            $detail->potongan_bpjs_kesehatan,
            $detail->potongan_bpjs_ketenagakerjaan,
            $detail->potongan_bkd,

            // PAJAK
            $detail->pajak,
            $detail->pph21_terhutang,
            $detail->pph21_sudah_dipotong,
            $detail->pph21_kurang_dipotong,

            // TOTAL
            $detail->penerimaan_bersih,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style the first row (headings)
        $sheet->getStyle('A1:AF1')->applyFromArray([
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

        // Auto-size columns will be handled by WithColumnWidths mostly, but let's add borders to all data
        $rowCount = SlipGajiDetail::where('header_id', $this->headerId)->count() + 1;
        $sheet->getStyle('A1:AF'.$rowCount)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20, // status
            'B' => 15, // nip
            'C' => 30, // nama
            // PENDAPATAN
            'D' => 15, 'E' => 15, 'F' => 15, 'G' => 20, 'H' => 20, 'I' => 20, 'J' => 15, 'K' => 20,
            'L' => 20, 'M' => 12, 'N' => 20, 'O' => 18, 'P' => 20, 'Q' => 20, 'R' => 20, 'S' => 20,
            'T' => 15, 'U' => 20,

            // POTONGAN
            'V' => 20, 'W' => 20, 'X' => 15, 'Y' => 25, 'Z' => 30, 'AA' => 15,

            // PAJAK
            'AB' => 10, 'AC' => 20, 'AD' => 25, 'AE' => 25,

            // TOTAL
            'AF' => 20,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT, // NIP column as text
            'D:AF' => '#,##0.00', // Numeric values
        ];
    }
}
