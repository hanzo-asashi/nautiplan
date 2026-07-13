<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Realisasi Per Jenis Belanja</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .header {
            margin-bottom: 10px;
        }
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
        }
        .kop-logo {
            width: 10%;
            text-align: center;
            vertical-align: middle;
        }
        .kop-text {
            width: 90%;
            text-align: center;
        }
        .kop-title-1 {
            font-size: 11px;
            font-weight: bold;
        }
        .kop-title-2 {
            font-size: 9px;
            font-weight: bold;
        }
        .kop-title-3 {
            font-size: 12px;
            font-weight: bold;
            color: #1e3a8a;
        }
        .kop-subtitle {
            font-size: 7px;
            color: #555;
        }
        .title-block {
            text-align: center;
            margin-top: 15px;
            margin-bottom: 15px;
        }
        .title {
            font-size: 12px;
            font-weight: bold;
            text-decoration: underline;
        }
        .subtitle {
            font-size: 10px;
        }
        .border-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        .border-table th, .border-table td {
            border: 1px solid #000;
            padding: 5px 7px;
            font-size: 9px;
        }
        .border-table th {
            background-color: #f3f4f6;
            text-align: center;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .font-bold {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <table class="kop-table">
            <tr>
                <td class="kop-logo">
                    <img src="{{ public_path("images/logo-poltekpel.png") }}" height="50" alt="Logo Kemenhub">
                </td>
                <td class="kop-text">
                    <div class="kop-title-1">KEMENTERIAN PERHUBUNGAN</div>
                    <div class="kop-title-2">BADAN PENGEMBANGAN SUMBER DAYA MANUSIA PERHUBUNGAN</div>
                    <div class="kop-title-3">POLITEKNIK PELAYARAN BAROMBONG</div>
                    <div class="kop-subtitle">Jl. Permandian Alam No. 1, Barombong, Kec. Tamalate, Kota Makassar 90224</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="title-block">
        <div class="title">LAPORAN REALISASI ANGGARAN PER JENIS BELANJA</div>
        <div class="subtitle">Tahun Anggaran: {{ $fiscalYear->year ?? date('Y') }}</div>
    </div>

    <table class="border-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th>Kategori / Jenis Belanja</th>
                <th style="width: 25%; text-align: right;">Total Pagu DIPA (Rp)</th>
                <th style="width: 25%; text-align: right;">Total Realisasi (Rp)</th>
                <th style="width: 20%; text-align: center;">Persentase (%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $index => $cat)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $cat['label'] }}</td>
                    <td class="text-right">Rp {{ number_format($cat['pagu'], 2, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($cat['realisasi'], 2, ',', '.') }}</td>
                    <td class="text-center font-bold">{{ $cat['pagu'] > 0 ? round(($cat['realisasi'] / $cat['pagu']) * 100, 2) : 0 }}%</td>
                </tr>
            @endforeach
            <tr class="font-bold" style="background-color: #e5e7eb;">
                <td colspan="2" class="text-right">TOTAL KESELURUHAN</td>
                <td class="text-right">Rp {{ number_format($totalPagu, 2, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totalRealisasi, 2, ',', '.') }}</td>
                <td class="text-center">{{ $totalPagu > 0 ? round(($totalRealisasi / $totalPagu) * 100, 2) : 0 }}%</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
