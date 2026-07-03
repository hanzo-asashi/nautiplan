<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Permintaan Pembayaran - SPP</title>
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
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .content-table td {
            padding: 3px 5px;
            vertical-align: top;
        }
        .border-all {
            border: 1px solid #000;
        }
        .border-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        .border-table th, .border-table td {
            border: 1px solid #000;
            padding: 4px 6px;
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
        .signature-container {
            width: 100%;
            margin-top: 30px;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
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
        <div class="title">SURAT PERMINTAAN PEMBAYARAN (SPP)</div>
        <div class="subtitle">Nomor: {{ $realization->spp_number ?? '-' }}</div>
        <div class="subtitle">Tanggal: {{ $realization->spp_date ? \Carbon\Carbon::parse($realization->spp_date)->translatedFormat('d F Y') : '-' }}</div>
    </div>

    <!-- Kepada -->
    <p>Kepada Yth.<br>
    <strong>Pejabat Penandatangan SPM (PPSPM)</strong><br>
    Satker Politeknik Pelayaran Barombong<br>
    Makassar</p>

    <p>Dengan ini kami mengajukan permintaan agar Saudara menerbitkan SPM sebesar <strong>Rp {{ number_format($realization->amount, 2, ',', '.') }}</strong> (<em>{{ $terbilang }}</em>) untuk pembayaran keperluan berikut:</p>

    <table class="content-table">
        <tr>
            <td style="width: 25%;"><strong>Untuk Keperluan</strong></td>
            <td style="width: 2%;">:</td>
            <td>{{ $realization->description }}</td>
        </tr>
        @if($realization->procurement)
        <tr>
            <td><strong>Pihak Ketiga (Vendor)</strong></td>
            <td>:</td>
            <td>{{ $realization->procurement->vendor?->name ?? $realization->vendor_name }}</td>
        </tr>
        <tr>
            <td><strong>Nomor Kontrak/SPK</strong></td>
            <td>:</td>
            <td>{{ $realization->procurement->document_number }} tanggal {{ \Carbon\Carbon::parse($realization->procurement->document_date)->translatedFormat('d F Y') }}</td>
        </tr>
        @endif
    </table>

    <div class="font-bold" style="margin-bottom: 5px;">Rincian Pembebanan Anggaran (MAK):</div>
    <table class="border-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode MAK / DIPA</th>
                <th>Uraian Anggaran</th>
                <th>Jumlah Anggaran</th>
                <th>Realisasi Ini</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>
                    {{ $realization->activityBudget?->activity?->program?->code }}.{{ $realization->activityBudget?->activity?->code }}
                    @if($realization->activityBudget?->subComponent)
                        .{{ $realization->activityBudget->subComponent->component?->code }}.{{ $realization->activityBudget->subComponent->code }}
                    @endif
                    .{{ $realization->activityBudget?->account_code }}
                </td>
                <td>
                    {{ $realization->activityBudget?->account_name }}<br>
                    <small style="color: #666;">
                        Sub-Komponen: {{ $realization->activityBudget?->subComponent?->name ?? 'Default' }}
                    </small>
                </td>
                <td class="text-right">Rp {{ number_format($realization->activityBudget?->amount ?? 0, 2, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($realization->amount, 2, ',', '.') }}</td>
            </tr>
            <tr class="font-bold">
                <td colspan="4" class="text-right">TOTAL</td>
                <td class="text-right">Rp {{ number_format($realization->amount, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div class="signature-container">
        <table class="signature-table">
            <tr>
                <td>
                    &nbsp;
                </td>
                <td>
                    Barombong, {{ $realization->spp_date ? \Carbon\Carbon::parse($realization->spp_date)->translatedFormat('d F Y') : \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                    <strong>Pejabat Pembuat Komitmen (PPK)</strong>
                    <br><br><br><br><br>
                    <u>{{ $realization->procurement?->ppk?->name ?? 'Arnaldy Achmadita, M.T.' }}</u><br>
                    NIP. {{ $realization->procurement?->ppk?->nip ?? '19870425 201012 1 002' }}
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
