<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $employeeName }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        .header img {
            max-width: 100%;
            height: auto;
            margin-bottom: 15px;
        }
        .header h1 {
            color: #2c3e50;
            margin: 10px 0 0 0;
            font-size: 24px;
        }
        .header p {
            color: #7f8c8d;
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        .content {
            margin-bottom: 30px;
        }
        .content p {
            margin-bottom: 15px;
            font-size: 16px;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box h3 {
            margin: 0 0 10px 0;
            color: #007bff;
            font-size: 18px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .info-row strong {
            color: #2c3e50;
        }
        .highlight {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .highlight p {
            margin: 0;
            color: #856404;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 12px;
            color: #7f8c8d;
        }
        .footer p {
            margin: 5px 0;
        }
        .btn {
            display: inline-block;
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin-top: 10px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .warning {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SLIP GAJI</h1>
            <p>UNIVERSITAS SANGGA BUANA</p>
        </div>

        <div class="content">
            <p>Yth. Bapak/Ibu <strong>{{ $employeeName }}</strong>,</p>
            
            <p>Bersama ini kami sampaikan slip gaji untuk periode <strong>{{ $periodeFormatted }}</strong>.</p>

            <div class="info-box">
                <h3>Informasi Slip Gaji</h3>
                <div class="info-row">
                    <strong>Nama:</strong>
                    <span>{{ $employeeName }}</span>
                </div>
                <div class="info-row">
                    <strong>NIP:</strong>
                    <span>{{ $nip }}</span>
                </div>
                <div class="info-row">
                    <strong>Periode:</strong>
                    <span>{{ $periodeFormatted }}</span>
                </div>
                <div class="info-row">
                    <strong>Penerimaan Bersih:</strong>
                    <span>Rp {{ $penerimaanBersih }}</span>
                </div>
                <div class="info-row">
                    <strong>Total Potongan:</strong>
                    <span>Rp {{ $totalPotongan }}</span>
                </div>
            </div>

            <div class="highlight">
                <p><strong>Catatan Penting:</strong> Slip gaji ini bersifat rahasia dan hanya untuk keperluan pribadi. Mohon untuk tidak menyebarluaskan dokumen ini kepada pihak lain.</p>
            </div>

            <p>Dokumen slip gaji terlampir dalam format PDF. Silakan unduh dan simpan dokumen tersebut untuk arsip pribadi Anda.</p>

            <p>Jika ada pertanyaan terkait slip gaji ini, silakan menghubungi bagian SDM Universitas Sangga Buana.</p>

            <div class="warning">
                <p><strong>Penting:</strong> Pastikan email Anda aktif dan dapat menerima lampiran. Jika Anda tidak menerima email dalam waktu 24 jam, silakan hubungi bagian SDM.</p>
            </div>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Universitas Sangga Buana</p>
            <p>Jl. PH.H. Mustofa No.68, Cikutra, Kec. Cibeunying Kidul, Kota Bandung, Jawa Barat 40124</p>
            <p>Email: info@usbypkp.ac.id| Telp: +62 22 7275489</p>
        </div>
    </div>
</body>
</html>
