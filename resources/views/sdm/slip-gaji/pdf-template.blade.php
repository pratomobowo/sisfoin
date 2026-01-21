<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $detail->nama_from_relation }}</title>
    <style>
        @page {
            margin: 20mm;
            size: A4;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .header h2 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 11px;
            color: #666;
        }
        
        .employee-info {
            margin-bottom: 25px;
        }
        
        .employee-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .employee-info td {
            padding: 4px 8px;
            vertical-align: top;
        }
        
        .employee-info .label {
            width: 120px;
            font-weight: bold;
        }
        
        .employee-info .colon {
            width: 10px;
        }
        
        .salary-details {
            margin-bottom: 20px;
        }
        
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .salary-table th,
        .salary-table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        
        .salary-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }
        
        .salary-table .amount {
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        
        .summary {
            margin-top: 20px;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .summary-table td {
            padding: 8px;
            border: 1px solid #333;
        }
        
        .summary-table .label {
            background-color: #f5f5f5;
            font-weight: bold;
            width: 200px;
        }
        
        .summary-table .amount {
            text-align: right;
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }
        
        .net-salary {
            background-color: #e8f5e8 !important;
            font-size: 14px;
        }
        
        .footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature {
            text-align: center;
            width: 200px;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 60px;
            padding-top: 5px;
        }
        
        .print-info {
            margin-top: 30px;
            font-size: 10px;
            color: #666;
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 10px;
            padding: 5px 0;
            border-bottom: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ config('app.company_name', 'PT. PERUSAHAAN') }}</h1>
        <h2>SLIP GAJI KARYAWAN</h2>
        <p>Periode: {{ $detail->header->periode }}</p>
    </div>

    <!-- Employee Information -->
    <div class="employee-info">
        <div class="section-title">INFORMASI KARYAWAN</div>
        <table>
            <tr>
                <td class="label">NIP</td>
                <td class="colon">:</td>
                <td>{{ $detail->nip }}</td>
                <td class="label">Nama</td>
                <td class="colon">:</td>
                <td>{{ $detail->nama_from_relation }}</td>
            </tr>
            <tr>
                <td class="label">Jabatan</td>
                <td class="colon">:</td>
                <td>{{ $detail->jabatan }}</td>
                <td class="label">Unit Kerja</td>
                <td class="colon">:</td>
                <td>{{ $detail->unit_kerja ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td class="colon">:</td>
                <td>{{ $detail->status_karyawan ?? '-' }}</td>
                <td class="label">Golongan</td>
                <td class="colon">:</td>
                <td>{{ $detail->golongan ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <!-- Salary Details -->
    <div class="salary-details">
        <div class="section-title">RINCIAN GAJI</div>
        
        <!-- Penerimaan -->
        <table class="salary-table">
            <thead>
                <tr>
                    <th colspan="2">PENERIMAAN</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Gaji Pokok</td>
                    <td class="amount">{{ number_format($detail->gaji_pokok, 0, ',', '.') }}</td>
                </tr>
                @if($detail->tunjangan_jabatan > 0)
                <tr>
                    <td>Tunjangan Jabatan</td>
                    <td class="amount">{{ number_format($detail->tunjangan_jabatan, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($detail->tunjangan_keluarga > 0)
                <tr>
                    <td>Tunjangan Keluarga</td>
                    <td class="amount">{{ number_format($detail->tunjangan_keluarga, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($detail->tunjangan_transport > 0)
                <tr>
                    <td>Tunjangan Transport</td>
                    <td class="amount">{{ number_format($detail->tunjangan_transport, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($detail->tunjangan_makan > 0)
                <tr>
                    <td>Tunjangan Makan</td>
                    <td class="amount">{{ number_format($detail->tunjangan_makan, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($detail->tunjangan_lainnya > 0)
                <tr>
                    <td>Tunjangan Lainnya</td>
                    <td class="amount">{{ number_format($detail->tunjangan_lainnya, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($detail->lembur > 0)
                <tr>
                    <td>Lembur</td>
                    <td class="amount">{{ number_format($detail->lembur, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($detail->bonus > 0)
                <tr>
                    <td>Bonus</td>
                    <td class="amount">{{ number_format($detail->bonus, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($detail->insentif > 0)
                <tr>
                    <td>Insentif</td>
                    <td class="amount">{{ number_format($detail->insentif, 0, ',', '.') }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        <!-- Potongan -->
        @if($detail->total_potongan > 0)
        <table class="salary-table">
            <thead>
                <tr>
                    <th colspan="2">POTONGAN</th>
                </tr>
            </thead>
            <tbody>
                @if($detail->potongan_bpjs_kesehatan > 0)
                <tr>
                    <td>BPJS Kesehatan</td>
                    <td class="amount">{{ number_format($detail->potongan_bpjs_kesehatan, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($detail->potongan_bpjs_ketenagakerjaan > 0)
                <tr>
                    <td>BPJS Ketenagakerjaan</td>
                    <td class="amount">{{ number_format($detail->potongan_bpjs_ketenagakerjaan, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($detail->potongan_pph21 > 0)
                <tr>
                    <td>PPh 21</td>
                    <td class="amount">{{ number_format($detail->potongan_pph21, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($detail->potongan_pinjaman > 0)
                <tr>
                    <td>Pinjaman</td>
                    <td class="amount">{{ number_format($detail->potongan_pinjaman, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($detail->potongan_absensi > 0)
                <tr>
                    <td>Potongan Absensi</td>
                    <td class="amount">{{ number_format($detail->potongan_absensi, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($detail->potongan_lainnya > 0)
                <tr>
                    <td>Potongan Lainnya</td>
                    <td class="amount">{{ number_format($detail->potongan_lainnya, 0, ',', '.') }}</td>
                </tr>
                @endif
            </tbody>
        </table>
        @endif
    </div>

    <!-- Summary -->
    <div class="summary">
        <table class="summary-table">
            <tr>
                <td class="label">Total Penerimaan</td>
                <td class="amount">Rp {{ number_format($detail->total_penerimaan, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Total Potongan</td>
                <td class="amount">Rp {{ number_format($detail->total_potongan, 0, ',', '.') }}</td>
            </tr>
            <tr class="net-salary">
                <td class="label">GAJI BERSIH</td>
                <td class="amount">Rp {{ number_format($detail->gaji_bersih, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="signature">
            <div>Mengetahui,</div>
            <div>HRD</div>
            <div class="signature-line">
                <div>{{ config('app.hrd_name', '(Nama HRD)') }}</div>
            </div>
        </div>
        
        <div class="signature">
            <div>{{ now()->format('d F Y') }}</div>
            <div>Karyawan</div>
            <div class="signature-line">
                <div>{{ $detail->nama_from_relation }}</div>
            </div>
        </div>
    </div>

    <!-- Print Info -->
    <div class="print-info">
        <p>Slip gaji ini dicetak secara otomatis pada {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Dokumen ini sah tanpa tanda tangan basah</p>
    </div>
</body>
</html>