<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Realisasi DIPA - Per Komponen & Sub Komponen</title>
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
            margin-bottom: 15px;
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
            font-size: 12px;
            font-weight: bold;
        }
        .kop-title-2 {
            font-size: 10px;
            font-weight: bold;
        }
        .kop-title-3 {
            font-size: 13px;
            font-weight: bold;
            color: #1e3a8a;
        }
        .kop-subtitle {
            font-size: 8px;
            color: #555;
        }
        .title-block {
            text-align: center;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        .title {
            font-size: 13px;
            font-weight: bold;
        }
        .subtitle {
            font-size: 10px;
            margin-top: 4px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
        }
        .data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .bg-output {
            background-color: #e0f2fe; /* Light blue */
        }
        .bg-suboutput {
            background-color: #f0fdf4; /* Light green */
        }
        .bg-component {
            background-color: #fef8e7; /* Light yellow */
        }
        .bg-subcomponent {
            background-color: #fafafa; /* Light gray */
        }
        .indent-suboutput {
            padding-left: 15px !important;
        }
        .indent-component {
            padding-left: 30px !important;
        }
        .indent-subcomponent {
            padding-left: 45px !important;
        }
        .indent-budget {
            padding-left: 60px !important;
        }
    </style>
</head>
<body>

    <!-- Kop Surat -->
    <div class="header">
        <table class="kop-table">
            <tr>
                <td class="kop-logo">
                    <span style="font-size: 24px;">⚓</span>
                </td>
                <td class="kop-text">
                    <div class="kop-title-1">KEMENTERIAN PERHUBUNGAN</div>
                    <div class="kop-title-2">BADAN PENGEMBANGAN SUMBER DAYA MANUSIA PERHUBUNGAN</div>
                    <div class="kop-title-2">BADAN LAYANAN UMUM</div>
                    <div class="kop-title-3">POLITEKNIK PELAYARAN BAROMBONG</div>
                    <div class="kop-subtitle">
                        Jln. Permandian Alam No. 1 Barombong - Makassar 90225 | Telp: (0411) 8216999 | Fax: (0411) 8217157 | Email: poltekpelbrb@gmail.com
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Title -->
    <div class="title-block">
        <div class="title">LAPORAN REKAPITULASI REALISASI ANGGARAN DIPA</div>
        <div class="title">MONITORING PER KOMKONEN & SUB KOMPONEN</div>
        <div class="subtitle">Tahun Anggaran: {{ $fiscalYear?->year ?? '-' }}</div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Kode / MAK</th>
                <th style="width: 45%;">Output / Sub Output / Komponen / Sub Komponen / Akun</th>
                <th style="width: 13%;">Pagu (Rp)</th>
                <th style="width: 13%;">Realisasi (Rp)</th>
                <th style="width: 13%;">Sisa Anggaran (Rp)</th>
                <th style="width: 6%;">%</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tree as $prog)
                @foreach($prog['children'] as $act)
                    @foreach($act['children'] as $out)
                        <tr class="bg-output font-bold">
                            <td>{{ $prog['code'] }}.{{ $act['code'] }}.{{ $out['code'] }}</td>
                            <td>Output: {{ $out['name'] }}</td>
                            <td class="text-right">{{ number_format($out['pagu'], 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($out['realisasi'], 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($out['sisa'], 0, ',', '.') }}</td>
                            <td class="text-center">
                                {{ $out['pagu'] > 0 ? round(($out['realisasi'] / $out['pagu']) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                        @foreach($out['children'] as $subOut)
                            <tr class="bg-suboutput font-bold">
                                <td>{{ $prog['code'] }}.{{ $act['code'] }}.{{ $out['code'] }}.{{ $subOut['code'] }}</td>
                                <td class="indent-suboutput">Sub Output: {{ $subOut['name'] }}</td>
                                <td class="text-right">{{ number_format($subOut['pagu'], 0, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($subOut['realisasi'], 0, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($subOut['sisa'], 0, ',', '.') }}</td>
                                <td class="text-center">
                                    {{ $subOut['pagu'] > 0 ? round(($subOut['realisasi'] / $subOut['pagu']) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                            @foreach($subOut['children'] as $comp)
                                <tr class="bg-component font-bold">
                                    <td>{{ $prog['code'] }}.{{ $act['code'] }}.{{ $out['code'] }}.{{ $subOut['code'] }}.{{ $comp['code'] }}</td>
                                    <td class="indent-component">Komponen: {{ $comp['name'] }}</td>
                                    <td class="text-right">{{ number_format($comp['pagu'], 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($comp['realisasi'], 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($comp['sisa'], 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        {{ $comp['pagu'] > 0 ? round(($comp['realisasi'] / $comp['pagu']) * 100, 1) : 0 }}%
                                    </td>
                                </tr>
                                @foreach($comp['children'] as $subComp)
                                    <tr class="bg-subcomponent font-bold">
                                        <td>{{ $prog['code'] }}.{{ $act['code'] }}.{{ $out['code'] }}.{{ $subOut['code'] }}.{{ $comp['code'] }}.{{ $subComp['code'] }}</td>
                                        <td class="indent-subcomponent">Sub Komponen: {{ $subComp['name'] }}</td>
                                        <td class="text-right">{{ number_format($subComp['pagu'], 0, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($subComp['realisasi'], 0, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($subComp['sisa'], 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            {{ $subComp['pagu'] > 0 ? round(($subComp['realisasi'] / $subComp['pagu']) * 100, 1) : 0 }}%
                                        </td>
                                    </tr>
                                    @foreach($subComp['children'] as $budget)
                                        <tr>
                                            <td>{{ $budget['code'] }}</td>
                                            <td class="indent-budget">Akun: {{ $budget['name'] }}</td>
                                            <td class="text-right">{{ number_format($budget['pagu'], 0, ',', '.') }}</td>
                                            <td class="text-right">{{ number_format($budget['realisasi'], 0, ',', '.') }}</td>
                                            <td class="text-right">{{ number_format($budget['sisa'], 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                {{ $budget['pagu'] > 0 ? round(($budget['realisasi'] / $budget['pagu']) * 100, 1) : 0 }}%
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @endforeach
                        @endforeach
                    @endforeach
                @endforeach
            @endforeach
            <tr class="font-bold" style="background-color: #d1d5db;">
                <td colspan="2" class="text-center">TOTAL KESELURUHAN</td>
                <td class="text-right">{{ number_format($totalPagu, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totalRealisasi, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totalSisa, 0, ',', '.') }}</td>
                <td class="text-center">
                    {{ $totalPagu > 0 ? round(($totalRealisasi / $totalPagu) * 100, 1) : 0 }}%
                </td>
            </tr>
        </tbody>
    </table>

</body>
</html>
