<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Slip Gaji - {{ $detail->nama_from_relation }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:400,500,600" rel="stylesheet" />
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            font-family: 'Poppins', sans-serif;
        }
        .pdf-container {
            width: 100%;
            height: 100vh;
            border: none;
        }
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 1000;
            font-family: 'Poppins', sans-serif;
        }
        .header-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .header-info h1 {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
        }
        .header-info p {
            font-size: 14px;
            color: #64748b;
        }
        .header-actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .btn-download {
            background-color: #dc2626;
            color: white;
            border: 1px solid #dc2626;
        }
        .btn-download:hover {
            background-color: #b91c1c;
            border-color: #b91c1c;
        }
        .btn-back {
            background-color: #64748b;
            color: white;
            border: 1px solid #64748b;
        }
        .btn-back:hover {
            background-color: #475569;
            border-color: #475569;
        }
        .btn svg {
            width: 16px;
            height: 16px;
            margin-right: 8px;
        }
        .pdf-wrapper {
            margin-top: 60px;
            height: calc(100vh - 60px);
        }
        @media print {
            .header {
                display: none;
            }
            .pdf-wrapper {
                margin-top: 0;
                height: 100vh;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-info">
            <h1>Preview Slip Gaji</h1>
            <p>{{ $detail->nama_from_relation }} ({{ $detail->nip }}) - {{ $detail->header->formatted_periode }}</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('sdm.slip-gaji.download-pdf', $detail) }}" class="btn btn-download">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Download PDF
            </a>
            <a href="{{ route('sdm.slip-gaji.show', $detail->header) }}" class="btn btn-back">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </a>
        </div>
    </div>
    <div class="pdf-wrapper">
        <embed src="{{ route('sdm.slip-gaji.show-pdf', $detail) }}" type="application/pdf" class="pdf-container">
    </div>
</body>
</html>