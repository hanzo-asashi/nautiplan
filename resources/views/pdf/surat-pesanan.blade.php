<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Pesanan - {{ $realization->receipt_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        .header {
            margin-bottom: 15px;
        }
        .title-block {
            text-align: center;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 14px;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 10px;
            color: #475569;
            margin-top: 2px;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            background-color: #f1f5f9;
            padding: 5px 8px;
            margin-top: 15px;
            margin-bottom: 8px;
            border-left: 3px solid #1e3a8a;
            text-transform: uppercase;
        }
        .info-table, .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .info-table td {
            padding: 4px 6px;
            vertical-align: top;
        }
        .info-table td.label {
            width: 25%;
            font-weight: bold;
            color: #475569;
        }
        .info-table td.value {
            width: 75%;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            font-size: 10px;
        }
        .data-table th {
            background-color: #f8fafc;
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .signature-container {
            margin-top: 40px;
            width: 100%;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 10px;
        }
        .signature-space {
            height: 60px;
        }
    </style>
</head>
<body>

    <!-- Kop Surat Kemenhub Resmi -->
    <div class="header" style="border-bottom: 2px double #000; padding-bottom: 8px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 12%; text-align: center; vertical-align: middle; padding-right: 10px;">
                    <span style="font-size: 28px; font-weight: bold; color: #1e3a8a;">⚓</span>
                </td>
                <td style="width: 88%; text-align: center;">
                    <div style="font-size: 12px; font-weight: bold; letter-spacing: 0.5px; text-transform: uppercase;">KEMENTERIAN PERHUBUNGAN</div>
                    <div style="font-size: 10px; font-weight: bold; letter-spacing: 0.5px; text-transform: uppercase;">BADAN PENGEMBANGAN SUMBER DAYA MANUSIA PERHUBUNGAN</div>
                    <div style="font-size: 11px; font-weight: bold; letter-spacing: 0.5px; text-transform: uppercase; color: #1e3a8a;">POLITEKNIK PELAYARAN BAROMBONG</div>
                    <div style="font-size: 8px; color: #64748b; margin-top: 2px;">
                        Jl. Permandian Alam No. 1, Barombong, Kec. Tamalate, Kota Makassar | Telp: (0411) 889722 | Email: poltekpel.barombong@dephub.go.id
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Title Dokumen -->
    <div class="title-block">
        <div class="title">SURAT PESANAN (SP)</div>
        <div class="subtitle">Nomor SP: {{ $realization->procurement_number ?: '-' }} | Tanggal SP: {{ $realization->procurement_date ? \Carbon\Carbon::parse($realization->procurement_date)->format('d M Y') : '-' }}</div>
    </div>

    <!-- Pihak Terlibat -->
    <div class="section-title">Pihak-Pihak yang Terlibat</div>
    <table class="info-table">
        <tr>
            <td class="label">Pihak Pertama (PPK)</td>
            <td class="value">: <strong>Pejabat Pembuat Komitmen (PPK)</strong> Politeknik Pelayaran Barombong</td>
        </tr>
        <tr>
            <td class="label">Pihak Kedua (Penyedia)</td>
            <td class="value">
                : <strong>{{ $realization->vendor_name ?: '-' }}</strong><br>
                &nbsp;&nbsp;NPWP: {{ $realization->vendor_npwp ?: '-' }}<br>
                &nbsp;&nbsp;Alamat: {{ $realization->vendor_address ?: '-' }}
            </td>
        </tr>
    </table>

    <!-- Rincian Pekerjaan / Anggaran Sumber -->
    <div class="section-title">Rincian Pekerjaan & Sumber Anggaran</div>
    <table class="info-table">
        <tr>
            <td class="label">Program Kerja</td>
            <td class="value">: {{ $realization->activityBudget->activity->program ? '['.$realization->activityBudget->activity->program->code.'] '.$realization->activityBudget->activity->program->name : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Kegiatan Utama</td>
            <td class="value">: {{ $realization->activityBudget->activity ? '['.$realization->activityBudget->activity->code.'] '.$realization->activityBudget->activity->name : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Mata Anggaran (Akun)</td>
            <td class="value">: {{ $realization->activityBudget->account_code ? $realization->activityBudget->account_code.' - '.$realization->activityBudget->account_name : '-' }}</td>
        </tr>
    </table>

    <!-- Detail Pembayaran & Transaksi -->
    <div class="section-title">Detail Pengadaan & Bukti Realisasi</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 50%">Deskripsi Pekerjaan / Barang</th>
                <th style="width: 25%">Nomor Kuitansi / Bukti</th>
                <th style="width: 25%" class="text-right">Nilai Belanja</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $realization->description ?: '-' }}</strong>
                    <div style="font-size: 8px; color: #475569; margin-top: 3px;">
                        Tanggal Realisasi: {{ \Carbon\Carbon::parse($realization->realization_date)->format('d M Y') }}
                    </div>
                </td>
                <td>{{ $realization->receipt_number ?: '-' }}</td>
                <td class="text-right" style="font-weight: bold; color: #1e3a8a;">
                    Rp {{ number_format($realization->amount, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Pembayaran SP2D -->
    @if($realization->sp2d_number)
        <table class="info-table" style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 10px; border-radius: 6px;">
            <tr>
                <td style="width: 20%; font-weight: bold; color: #475569;">Nomor SP2D</td>
                <td style="width: 30%">: {{ $realization->sp2d_number }}</td>
                <td style="width: 20%; font-weight: bold; color: #475569;">Tanggal SP2D</td>
                <td style="width: 30%">: {{ $realization->sp2d_date ? \Carbon\Carbon::parse($realization->sp2d_date)->format('d M Y') : '-' }}</td>
            </tr>
        </table>
    @endif

    <!-- Tanda Tangan PPK & Vendor -->
    <div class="signature-container">
        <table class="signature-table">
            <tr>
                <td>
                    <div>Penyedia Jasa / Barang</div>
                    <div style="font-weight: bold; margin-top: 2px;">{{ $realization->vendor_name ?: 'Pihak Kedua' }}</div>
                    <div class="signature-space"></div>
                    <div style="text-decoration: underline; font-weight: bold;">( .................................................. )</div>
                    <div style="font-size: 9px; color: #64748b;">Direktur / Penanggung Jawab</div>
                </td>
                <td>
                    <div>Untuk dan atas nama</div>
                    <div style="font-weight: bold; margin-top: 2px;">Pejabat Pembuat Komitmen (PPK)</div>
                    <div class="signature-space"></div>
                    <div style="text-decoration: underline; font-weight: bold;">( .................................................. )</div>
                    <div style="font-size: 9px; color: #64748b;">NIP. ..................................................</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Verifikasi Sistem QR -->
    <div style="margin-top: 50px; border-top: 1px dashed #cbd5e1; padding-top: 8px; text-align: center; font-size: 8px; color: #64748b;">
        <span style="font-size: 12px; margin-right: 5px; vertical-align: middle;">🛡️</span>
        Dokumen ini dibuat dan diverifikasi secara digital melalui **Sistem Informasi & Monitoring Realisasi PPK (SIM-PPK) Politeknik Pelayaran Barombong**.
    </div>

</body>
</html>
