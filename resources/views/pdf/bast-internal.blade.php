<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Serah Terima Internal - BAST Internal</title>
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
        .intro-text {
            text-align: justify;
            margin-bottom: 15px;
            text-indent: 30px;
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
            margin-top: 40px;
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
        <div class="title">BERITA ACARA SERAH TERIMA INTERNAL</div>
        <div class="subtitle">Nomor: BAST-INT/{{ $realization->id }}/{{ date('Y') }}</div>
    </div>

    <p class="intro-text">
        Pada hari ini <strong>{{ App\Helpers\FormatHelper::tanggal(date('Y-m-d')) }}</strong> bertempat di Politeknik Pelayaran Barombong Makassar, yang bertanda tangan di bawah ini masing-masing Pihak Unit Pelaksana Kegiatan menyatakan telah menyerahkan dan menerima rincian pengadaan barang/jasa untuk mendukung kegiatan <strong>[{{ $realization->activityBudget->activity->code ?? '-' }}] {{ $realization->activityBudget->activity->name ?? '-' }}</strong> di bawah pengawasan Pejabat Pembuat Komitmen:
    </p>

    <table class="border-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th>Rincian Item Barang/Jasa</th>
                <th style="width: 15%;">Volume</th>
                <th style="width: 15%;">Satuan</th>
                <th style="width: 25%; text-align: right;">Total Nilai (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($realization->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->budgetItem->name ?? 'Detail Belanja' }}</td>
                    <td class="text-center">{{ $item->volume }}</td>
                    <td class="text-center">{{ $item->budgetItem->unit ?? 'Pcs' }}</td>
                    <td class="text-right">Rp {{ number_format($item->volume * $item->unit_price, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="font-bold">
                <td colspan="4" class="text-right">Total Transaksi Belanja</td>
                <td class="text-right">Rp {{ number_format($realization->amount, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <p style="text-align: justify; margin-top: 15px;">
        Demikian Berita Acara Serah Terima Internal ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.
    </p>

    <div class="signature-container">
        <table class="signature-table">
            <tr>
                <td>
                    <strong>Pihak Yang Menerima (Unit Kerja Pelaksana)</strong>
                    <br>
                    Kepala Unit / Penanggung Jawab
                    <br><br><br><br>
                    <u>{{ $realization->activityBudget->activity->responsibleUser->name ?? '.........................................' }}</u><br>
                    NIP. {{ $realization->activityBudget->activity->responsibleUser->employee_id ?? '.........................................' }}
                </td>
                <td>
                    <strong>Pihak Yang Menyerahkan</strong>
                    <br>
                    Pejabat Pembuat Komitmen (PPK)
                    <br><br><br><br>
                    <u>{{ $realization->procurement->ppk->name ?? '.........................................' }}</u><br>
                    NIP. {{ $realization->procurement->ppk->employee_id ?? '.........................................' }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
