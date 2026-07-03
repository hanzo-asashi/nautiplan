<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Perintah Membayar - SPM</title>
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
        <div class="title">SURAT PERINTAH MEMBAYAR (SPM)</div>
        <div class="subtitle">Nomor: {{ $realization->spm_number ?? '-' }}</div>
        <div class="subtitle">Tanggal: {{ $realization->spm_date ? \Carbon\Carbon::parse($realization->spm_date)->translatedFormat('d F Y') : '-' }}</div>
    </div>

    <p>Kepada Yth.<br>
    <strong>Kepala Kantor Pelayanan Perbendaharaan Negara (KPPN) Makassar II</strong><br>
    Makassar</p>

    <p>Agar melakukan pembayaran sejumlah uang sebesar <strong>Rp {{ number_format($realization->amount, 2, ',', '.') }}</strong> (<em>{{ $terbilang }}</em>) dibebankan pada DIPA Politeknik Pelayaran Barombong kepada:</p>

    <table class="info-table" style="border: 1px solid #000; padding: 10px;">
        <tr>
            <td style="width: 30%;"><strong>Nama Penerima</strong></td>
            <td style="width: 2%;">:</td>
            <td>{{ $realization->procurement?->vendor?->name ?? $realization->vendor_name ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>NPWP Penerima</strong></td>
            <td>:</td>
            <td>{{ $realization->procurement?->vendor?->npwp ?? $realization->vendor_npwp ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Nomor Rekening Bank</strong></td>
            <td>:</td>
            <td>{{ $realization->procurement?->vendor?->bank_account_number ?? $realization->bank_account_number ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Bank Penerima</strong></td>
            <td>:</td>
            <td>{{ $realization->procurement?->vendor?->bank_name ?? $realization->bank_name ?? '-' }} a/n {{ $realization->procurement?->vendor?->bank_account_name ?? $realization->bank_account_name ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Untuk Keperluan</strong></td>
            <td>:</td>
            <td>{{ $realization->description }}</td>
        </tr>
    </table>

    @php
        $sumPpn = 0.0;
        $sumPph21 = 0.0;
        $sumPph22 = 0.0;
        $sumPph23 = 0.0;
        foreach($realization->items as $item) {
            $sumPpn += (float) $item->tax_ppn;
            $sumPph21 += (float) $item->tax_pph21;
            $sumPph22 += (float) $item->tax_pph22;
            $sumPph23 += (float) $item->tax_pph23;
        }
        $netPayment = $realization->amount - ($sumPpn + $sumPph21 + $sumPph22 + $sumPph23);
    @endphp

    <div class="font-bold" style="margin-top: 10px; margin-bottom: 5px;">Rincian Potongan Pajak / Penerimaan Negara:</div>
    <table class="border-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Jenis Potongan Pajak</th>
                <th>Kode Akun Pajak (KAP)</th>
                <th>Jumlah Potongan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>PPN Dalam Negeri (Bendahara APBN)</td>
                <td class="text-center">411211</td>
                <td class="text-right">Rp {{ number_format($sumPpn, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>PPh Pasal 21</td>
                <td class="text-center">411121</td>
                <td class="text-right">Rp {{ number_format($sumPph21, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>PPh Pasal 22</td>
                <td class="text-center">411122</td>
                <td class="text-right">Rp {{ number_format($sumPph22, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td>PPh Pasal 23</td>
                <td class="text-center">411124</td>
                <td class="text-right">Rp {{ number_format($sumPph23, 2, ',', '.') }}</td>
            </tr>
            <tr class="font-bold">
                <td colspan="3" class="text-right">TOTAL POTONGAN PAJAK</td>
                <td class="text-right">Rp {{ number_format($sumPpn + $sumPph21 + $sumPph22 + $sumPph23, 2, ',', '.') }}</td>
            </tr>
            <tr class="font-bold" style="background-color: #f3f4f6;">
                <td colspan="3" class="text-right">JUMLAH BERSIH YANG DIBAYARKAN (NETTO)</td>
                <td class="text-right">Rp {{ number_format($netPayment, 2, ',', '.') }}</td>
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
                    Barombong, {{ $realization->spm_date ? \Carbon\Carbon::parse($realization->spm_date)->translatedFormat('d F Y') : \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                    <strong>Pejabat Penandatangan SPM (PPSPM)</strong>
                    <br><br><br><br><br>
                    <u>Capt. Sidrotul Muntaha, M.Mar.</u><br>
                    NIP. 19720512 199712 1 001
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
