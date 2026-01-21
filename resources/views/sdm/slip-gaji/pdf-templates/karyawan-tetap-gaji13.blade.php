<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji 13 - {{ $detail->nama_from_relation }}</title>
    <style>
        @page {
            margin: 12mm 10mm;
            size: A4;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        
        .slip-container {
            width: 100%;
            max-width: 100%;
            padding: 8px 12px;
            margin: 0 auto;
            box-sizing: border-box;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .kop-image {
            width: auto;
            height: auto;
            max-width: 100%;
            max-height: 200px;
            object-fit: contain;
            margin-bottom: 10px;
        }
        
        .slip-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .period {
            font-size: 12px;
            color: #666;
        }
        
        /* Employee Info */
        .employee-info {
            margin-bottom: 20px;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-table td {
            padding: 4px 8px;
            vertical-align: top;
        }
        
        .info-table td:first-child {
            width: 120px;
            font-weight: 600;
            color: #374151;
        }
        
        .info-table td:nth-child(2) {
            width: 10px;
        }
        
        /* Salary Table */
        .salary-table {
            width: 60%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .salary-table td {
            padding: 5px 8px;
            vertical-align: top;
            border-bottom: 0.5px solid #f0f0f0;
        }
        
        .salary-table .label {
            width: 5%;
        }
        
        .salary-table .item-name {
            width: 45%;
        }
        
        .salary-table .currency {
            width: auto;
            text-align: right;
            white-space: nowrap;
            padding-right: 3px;
        }
        
        .salary-table .amount {
            width: 1%;
            text-align: right;
            font-family: 'Courier New', monospace;
            padding-left: 2px;
            padding-right: 2px;
            font-weight: bold;
            white-space: nowrap;
        }
        
        .salary-table .symbol {
            width: 20px;
            text-align: center;
            font-weight: bold;
            color: #666;
        }
        
        .salary-table .total {
            width: 15%;
            text-align: right;
            font-family: 'Courier New', monospace;
            padding-right: 5px;
            font-weight: bold;
        }

        .salary-table .total-border {
            border-bottom: 2px solid #333;
        }
        
        .separator-row td {
            border-bottom: 2px solid #333;
        }
        
        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        
        .total-row td {
            border-top: 2px solid #333;
            border-bottom: 2px solid #333;
            padding: 8px;
        }
        
        .net-salary-row {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            font-weight: bold;
            font-size: 11px;
        }
        
        .net-salary-row td {
            padding: 10px 8px;
        }
        
        /* Signature Section */
        .signature-section {
            margin-top: 30px;
        }
        
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .signature-box {
            width: 50%;
            text-align: center;
            padding: 10px;
        }
        
        .signature-image {
            width: auto;
            height: auto;
            max-width: 80px;
            object-fit: contain;
            margin: 10px 0;
        }
        
        .signature-name {
            margin-top: 10px;
            font-weight: bold;
        }
        
        /* Print Info */
        .print-info {
            margin-top: 20px;
            font-size: 8px;
            color: #666;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="slip-container">
        <!-- Header -->
        <div class="header">
            @if($kop_surat_data_uri)
                <img src="{{ $kop_surat_data_uri }}" alt="Kop Surat" class="kop-image">
            @endif
            <div class="slip-title">SLIP GAJI 13 - KARYAWAN TETAP</div>
            <div class="period">Periode: {{ $periode_formatted }}</div>
        </div>
        
        <!-- Employee Info -->
        <div class="employee-info">
            <table style="width: 100%;">
                <tbody>
                    <tr>
                        <td style="width: 50%; vertical-align: top; padding-right: 15px;">
                            <table class="info-table">
                                <tr><td>Nama</td><td>:</td><td>{{ $detail->nama_from_relation ?? '-' }}</td></tr>
                                <tr><td>NIP</td><td>:</td><td>{{ $detail->nip ?? '-' }}</td></tr>
                                <tr><td>Status Karyawan</td><td>:</td><td>{{ $detail->employee->status_kepegawaian ?? '-' }}</td></tr>
                            </table>
                        </td>
                        <td style="width: 50%; vertical-align: top; padding-left: 15px;">
                            <table class="info-table">
                                <tr><td>Unit Kerja</td><td>:</td><td>{{ $detail->employee->satuan_kerja ?? '-' }}</td></tr>
                                <tr><td>Jabatan</td><td>:</td><td>{{ $detail->employee->jabatan_struktural ?? $detail->employee->jabatan_fungsional ?? '-' }}</td></tr>
                                <tr><td>Golongan</td><td>:</td><td>{{ $detail->employee->id_pangkat ?? '-' }}</td></tr>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Salary Details -->
        <table class="salary-table">
            <tbody>
                <tr><td class="label">1.</td><td class="item-name">Gaji Pokok</td><td class="currency">Rp</td><td class="amount">{{ number_format($detail->gaji_pokok ?? 0, 0, ',', '.') }}</td><td class="symbol"></td><td class="total"></td></tr>
                <tr><td class="label">2.</td><td class="item-name">TPP</td><td class="currency">Rp</td><td class="amount">{{ number_format($detail->tpp ?? 0, 0, ',', '.') }}</td><td class="symbol"></td><td class="total"></td></tr>
                <tr><td class="label">3.</td><td class="item-name">Tunjangan Keluarga</td><td class="currency">Rp</td><td class="amount">{{ number_format($detail->tunjangan_keluarga ?? 0, 0, ',', '.') }}</td><td class="symbol"></td><td class="total"></td></tr>
                <tr><td class="label">4.</td><td class="item-name">Tunjangan Kemahalan</td><td class="currency">Rp</td><td class="amount">{{ number_format($detail->tunjangan_kemahalan ?? 0, 0, ',', '.') }}</td><td class="symbol"></td><td class="total"></td></tr>
                <tr><td class="label">5.</td><td class="item-name">Tunjangan Golongan</td><td class="currency">Rp</td><td class="amount">{{ number_format($detail->tunjangan_golongan ?? 0, 0, ',', '.') }}</td><td class="symbol"></td><td class="total"></td></tr>
                <tr><td class="label">6.</td><td class="item-name">Tunjangan PMB</td><td class="currency total-border">Rp</td><td class="amount total-border">{{ number_format($detail->tunjangan_pmb ?? 0, 0, ',', '.') }}</td><td class="symbol">+</td></tr>
                <tr><td class="label"></td><td class="item-name">Penerimaan Kotor</td><td class="amount"></td><td class="symbol"></td><td class="total"></td><td class="currency">Rp</td><td class="total">{{ number_format($detail->penerimaan_kotor ?? 0, 0, ',', '.') }}</td></tr>
                <tr><td class="label">7.</td><td class="item-name">Pajak</td><td class="currency total-border">Rp</td><td class="amount total-border">{{ number_format($detail->pajak ?? 0, 0, ',', '.') }}</td><td class="symbol">-</td><td class="total"></td></tr>
                <tr><td class="label"></td><td class="item-name">Penerimaan Bersih<br><small style="font-size: 8px; color: #666;">(Penerimaan Kotor - Pajak)</small></td><td class="amount"></td><td class="symbol"></td><td class="total"></td><td class="currency">Rp</td><td class="total">{{ number_format($detail->penerimaan_bersih ?? 0, 0, ',', '.') }}</td></tr>
        </table>
        
        <!-- Signature -->
        <div class="signature-section">
            <table class="signature-table">
                <tbody>
                    <tr>
                        <td class="signature-box">
                            <div>Mengetahui,</div>
                            <div><strong>Kepala Biro SDM</strong></div>
                            @if($ttd_audita_data_uri)
                                <img src="{{ $ttd_audita_data_uri }}" class="signature-image" alt="Tanda Tangan Audita">
                            @endif
                            <div class="signature-name">(Audita Setiawan, SE., MM.)</div>
                        </td>
                        <td class="signature-box">
                            <div>Disetujui,</div>
                            <div><strong>Kepala Bagian Keuangan</strong></div>
                            @if($ttd_yanti_data_uri)
                                <img src="{{ $ttd_yanti_data_uri }}" class="signature-image" alt="Tanda Tangan Yanti">
                            @endif
                            <div class="signature-name">(Yanti Hasiana)</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Print Info -->
        <div class="print-info">
            <p>Slip gaji ini dicetak secara otomatis pada {{ $generated_at }}</p>
        </div>
    </div>
</body>
</html>
