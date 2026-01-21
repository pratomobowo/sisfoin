<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $detail->nama_from_relation }}</title>
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
        
        /* Layout Table */
        .layout-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .layout-column {
            width: 50%;
            vertical-align: top;
            padding: 0 5px;
        }
        
        .layout-column:first-child {
            padding-right: 15px;
            padding-left: 5px;
        }
        
        .layout-column:last-child {
            padding-left: 15px;
            padding-right: 5px;
        }
        
        /* Section Title */
        .section-title {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #333;
            text-transform: uppercase;
        }
        
        /* Salary Table */
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .salary-table td {
            padding: 3px 4px;
            vertical-align: top;
            border-bottom: 0.5px solid #f0f0f0;
        }
        
        .salary-table .currency {
            width: 18px;
            text-align: left;
        }
        
        .salary-table .amount {
            width: 85px;
            text-align: right;
            font-family: 'Courier New', monospace;
            padding-right: 2px;
            font-weight: bold;
        }
        
        .salary-table .icon-column {
            width: 12px;
            text-align: center;
        }
        
        .plus-icon {
            display: inline-block;
            font-size: 10px;
            font-weight: bold;
            color: #333;
        }
        
        .minus-icon {
            display: inline-block;
            font-size: 10px;
            font-weight: bold;
            color: #333;
        }
        
        .total-row {
            font-weight: bold;
        }
        
        .total-row td {
            border-top: 1px solid #333;
        }
        
        .net-salary-row {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            font-weight: bold;
            font-size: 11px;
        }
        
        .summary-section {
            margin-top: 10px;
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
        
        /* Small text */
        .small-text {
            font-size: 8px;
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
            <div class="slip-title">SLIP GAJI DOSEN GURU BESAR</div>
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
                                <tr><td>Status Karyawan</td><td>:</td><td>{{ $detail->dosen->status_kepegawaian ?? '-' }}</td></tr>
                            </table>
                        </td>
                        <td style="width: 50%; vertical-align: top; padding-left: 15px;">
                            <table class="info-table">
                                <tr><td>Unit Kerja</td><td>:</td><td>{{ $detail->dosen->satuan_kerja ?? '-' }}</td></tr>
                                <tr><td>Jabatan Struktural</td><td>:</td><td>{{ $detail->dosen->jabatan_struktural ?? '-' }}</td></tr>
                                <tr><td>Golongan</td><td>:</td><td>{{ $detail->dosen->jabatan_sub_fungsional ?? '-' }}</td></tr>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Salary Details -->
        <table class="layout-table">
            <tbody>
                <tr>
                    <td class="layout-column">
                        <div class="section-title">PENERIMAAN</div>
                        <table class="salary-table">
                            <tbody>
                                <tr><td>Tunjangan Pendidikan</td><td class="currency">Rp</td><td class="amount">{{ number_format($detail->tunjangan_pendidikan ?? 0, 0, ',', '.') }}</td><td class="icon-column"></td></tr>
                                <tr><td>Tunjangan Struktural</td><td class="currency">Rp</td><td class="amount">{{ number_format($detail->tunjangan_struktural ?? 0, 0, ',', '.') }}</td><td class="icon-column"></td></tr>
                                <tr><td>Tunjangan PMB</td><td class="currency" style="border-bottom: 1.2px solid #000000ff;">Rp</td><td class="amount" style="border-bottom: 1.2px solid #000000ff;">{{ number_format($detail->tunjangan_pmb ?? 0, 0, ',', '.') }}</td><td class="icon-column" style="border-bottom: 1.2px solid #000000ff;"><span class="plus-icon">+</span></td></tr>
                                <tr class="total-row"><td><strong>Total Penerimaan Kotor</strong></td><td class="currency"><strong>Rp</strong></td><td class="amount"><strong>{{ number_format($detail->penerimaan_kotor ?? 0, 0, ',', '.') }}</strong></td><td class="icon-column"></td></tr>
                            </tbody>
                        </table>
                        <br>
                        <div class="section-title">PENERIMAAN LAIN-LAIN</div>
                        <table class="salary-table">
                            <tbody>
                                <tr><td>Total Honor<br><span class="small-text">(Penerimaan secara tunai)</span></td><td class="currency">Rp</td><td class="amount">{{ number_format(($detail->honor_tetap ?? 0) + ($detail->honor_tunai ?? 0), 0, ',', '.') }}</td><td class="icon-column"></td></tr>
                            </tbody>
                        </table>
                        <span class="small-text">Cut Off setiap tanggal 22 di bulan berjalan, Rincian Total Honor bisa menghubungi ibu Yanti Hasiana (Kepala Bagian Keuangan)</span>

                        <!-- Summary Section -->
                        <div class="summary-section">
                            <table class="salary-table" style="margin-bottom: 0; width: 100%;">
                                <tbody>
                                    <tr class="total-row" style="background-color: #f8d7da; border-bottom: 1px solid #ddd;"><td><strong>JUMLAH POTONGAN</strong><br><span class="small-text">(Total Pajak)</span></td><td class="currency"><strong>Rp</strong></td><td class="amount"><strong>{{ number_format($detail->pajak ?? 0, 0, ',', '.') }}</strong></td><td class="icon-column"></td></tr>
                                    <tr class="net-salary-row"><td><strong>PENERIMAAN BERSIH</strong></td><td class="currency"><strong>Rp</strong></td><td class="amount"><strong>{{ number_format($detail->penerimaan_bersih ?? 0, 0, ',', '.') }}</strong></td><td class="icon-column"></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                    <td class="layout-column">
                        <div class="section-title">POTONGAN</div>
                        <table class="salary-table">
                            <tbody>
                                <tr><td>Pajak</td><td class="currency" style="border-bottom: 1.2px solid #000000ff;">Rp</td><td class="amount" style="border-bottom: 1.2px solid #000000ff;">{{ number_format($detail->pajak ?? 0, 0, ',', '.') }}</td><td class="icon-column" style="border-bottom: 1.2px solid #000000ff;"><span class="plus-icon">+</span></td></tr>
                                <tr class="total-row"><td><strong>Total Potongan</strong></td><td class="currency"><strong>Rp</strong></td><td class="amount"><strong>{{ number_format($detail->pajak ?? 0, 0, ',', '.') }}</strong></td><td class="icon-column"></td></tr>
                            </tbody>
                        </table>
                        <br>
                        <div class="section-title">PERHITUNGAN PAJAK</div>
                        <table class="salary-table">
                            <tbody>
                                <tr><td>Pajak</td><td class="currency">Rp</td><td class="amount">{{ number_format($detail->pajak ?? 0, 0, ',', '.') }}</td><td class="icon-column"></td></tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
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