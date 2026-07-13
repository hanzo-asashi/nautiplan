<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Perintah Pencairan Dana - SP2D</title>
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
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .info-table td {
            padding: 3px 5px;
            vertical-align: top;
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
            vertical-align: top;
            text-align: center;
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
        <div class="title">SURAT PERINTAH PENCAIRAN DANA (SP2D)</div>
        <div class="subtitle">Nomor: {{ $realization->sp2d_number ?? '.........................................' }}</div>
        <div class="subtitle">Tanggal: {{ $realization->sp2d_date ? App\Helpers\FormatHelper::tanggal($realization->sp2d_date) : '.........................................' }}</div>
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 25%;">Dari</td>
            <td style="width: 2%;">:</td>
            <td>Kuasa Bendahara Umum Negara (BUN) / KPA Politeknik Pelayaran Barombong</td>
        </tr>
        <tr>
            <td>Tahun Anggaran</td>
            <td>:</td>
            <td>{{ $realization->activityBudget->activity->fiscalYear->year ?? '-' }}</td>
        </tr>
        <tr>
            <td>Dasar Pembayaran (SPM)</td>
            <td>:</td>
            <td>SPM Nomor: {{ $realization->spm_number ?? '......................' }} tanggal {{ $realization->created_at ? App\Helpers\FormatHelper::tanggal($realization->created_at) : '......................' }}</td>
        </tr>
        <tr>
            <td>Nilai Tagihan</td>
            <td>:</td>
            <td class="font-bold">Rp {{ number_format($realization->amount, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Terbilang</td>
            <td>:</td>
            <td style="font-style: italic;">{{ $terbilang }}</td>
        </tr>
        <tr>
            <td>Kepada (Penyedia)</td>
            <td>:</td>
            <td>{{ $realization->procurement->vendor->name ?? 'Pihak Internal / Swakelola' }}</td>
        </tr>
        <tr>
            <td>Nomor Rekening</td>
            <td>:</td>
            <td>{{ $realization->procurement->vendor->bank_account ?? '......................' }} ({{ $realization->procurement->vendor->bank_name ?? '......................' }})</td>
        </tr>
        <tr>
            <td>Keperluan</td>
            <td>:</td>
            <td>{{ $realization->description }}</td>
        </tr>
    </table>

    <table class="border-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 20%;">Kode Akun</th>
                <th>Uraian Akun / Rincian Belanja</th>
                <th style="width: 25%; text-align: right;">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($realization->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $realization->activityBudget->account_code ?? '-' }}</td>
                    <td>{{ $item->budgetItem->name ?? 'Detail Belanja' }} (Vol: {{ $item->volume }})</td>
                    <td class="text-right">Rp {{ number_format($item->volume * $item->unit_price, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="font-bold">
                <td colspan="3" class="text-right">Total Transaksi</td>
                <td class="text-right">Rp {{ number_format($realization->amount, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="signature-container">
        <table class="signature-table">
            <tr>
                <td>
                    <br>
                    <strong>Pejabat Pembuat Komitmen</strong>
                    <br><br><br><br>
                    <u>{{ $realization->procurement->ppk->name ?? '.........................................' }}</u><br>
                    NIP. {{ $realization->procurement->ppk->employee_id ?? '.........................................' }}
                </td>
                <td>
                    Makassar, {{ $realization->sp2d_date ? App\Helpers\FormatHelper::tanggal($realization->sp2d_date) : App\Helpers\FormatHelper::tanggal(date('Y-m-d')) }}<br>
                    <strong>Kuasa Pengguna Anggaran (KPA)</strong>
                    <br><br><br><br>
                    <u>{{ $realization->procurement->kpa->name ?? '.........................................' }}</u><br>
                    NIP. {{ $realization->procurement->kpa->employee_id ?? '.........................................' }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
