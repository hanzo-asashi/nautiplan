<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Setoran Pajak - SSP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .container {
            border: 2px solid #000;
            padding: 10px;
            max-width: 100%;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #000;
        }
        .header-table td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: middle;
        }
        .title-ssp {
            font-size: 13px;
            font-weight: bold;
            text-align: center;
        }
        .sub-ssp {
            font-size: 8px;
            text-align: center;
        }
        .section-title {
            background-color: #f3f4f6;
            font-weight: bold;
            padding: 4px;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table td {
            padding: 4px 6px;
            vertical-align: top;
        }
        .box-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            margin-bottom: 5px;
        }
        .box-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            font-size: 10px;
        }
        .code-box {
            font-family: monospace;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 2px;
            border: 1px solid #000;
            padding: 2px 5px;
            background-color: #fff;
            display: inline-block;
        }
        .grid-npwp {
            font-family: monospace;
            font-size: 11px;
            letter-spacing: 3px;
            border: 1px solid #000;
            padding: 2px 4px;
            display: inline-block;
        }
        .masa-box {
            border: 1px solid #000;
            width: 12px;
            height: 12px;
            display: inline-block;
            text-align: center;
            line-height: 12px;
            font-size: 8px;
            font-weight: bold;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .signature-table td {
            border: 1px solid #000;
            width: 50%;
            padding: 10px;
            text-align: center;
            vertical-align: top;
        }
    </style>
</head>
<body>

    <div class="container">
        
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td style="width: 25%; text-align: center;">
                    <span style="font-size: 20px;">⚓</span><br>
                    <strong>KEMENTERIAN KEUANGAN RI</strong><br>
                    <span style="font-size: 8px;">DIREKTORAT JENDERAL PAJAK</span>
                </td>
                <td style="width: 50%; text-align: center;">
                    <div class="title-ssp">SURAT SETORAN PAJAK</div>
                    <div class="title-ssp">(SSP)</div>
                </td>
                <td style="width: 25%; text-align: center; font-size: 10px; font-weight: bold;">
                    LEMBAR KESATU<br>
                    <span style="font-size: 7px; font-weight: normal;">Untuk Wajib Pajak sebagai bukti pembayaran</span>
                </td>
            </tr>
        </table>

        <!-- Bagian Wajib Pajak -->
        <div class="section-title">A. IDENTITAS WAJIB PAJAK</div>
        <table class="data-table">
            <tr>
                <td style="width: 20%;"><strong>NPWP</strong></td>
                <td style="width: 2%;">:</td>
                <td>
                    <span class="grid-npwp">
                        {{ $realization->procurement?->vendor?->npwp ?? $realization->vendor_npwp ?? '00.000.000.0-000.000' }}
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>Nama Wajib Pajak</strong></td>
                <td>:</td>
                <td><strong>{{ $realization->procurement?->vendor?->name ?? $realization->vendor_name ?? '-' }}</strong></td>
            </tr>
            <tr>
                <td><strong>Alamat</strong></td>
                <td>:</td>
                <td>{{ $realization->procurement?->vendor?->address ?? $realization->vendor_address ?? '-' }}</td>
            </tr>
        </table>

        <!-- Bagian Objek Pajak -->
        <div class="section-title">B. KODE AKUN PAJAK & JENIS SETORAN</div>
        <table class="data-table">
            <tr>
                <td style="width: 45%;">
                    <strong>Kode Akun Pajak (KAP)</strong><br><br>
                    <span class="code-box">
                        @if($taxType === 'ppn') 411211 @elseif($taxType === 'pph22') 411122 @elseif($taxType === 'pph23') 411124 @else 411121 @endif
                    </span>
                </td>
                <td>
                    <strong>Kode Jenis Setoran (KJS)</strong><br><br>
                    <span class="code-box">920</span>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding-top: 10px;">
                    <strong>Uraian Pembayaran:</strong> Pembayaran Pungutan {{ strtoupper($taxType) }} atas belanja barang jasa Paket: <em>{{ $realization->procurement?->title ?? $realization->description }}</em> berdasarkan Kontrak/SPK Nomor {{ $realization->procurement?->document_number ?? '-' }}.
                </td>
            </tr>
        </table>

        <!-- Masa & Tahun Pajak -->
        <div class="section-title">C. MASA PAJAK & TAHUN PAJAK</div>
        @php
            $realMonth = $realization->realization_date ? \Carbon\Carbon::parse($realization->realization_date)->month : \Carbon\Carbon::now()->month;
            $realYear = $realization->realization_date ? \Carbon\Carbon::parse($realization->realization_date)->year : \Carbon\Carbon::now()->year;
        @endphp
        <table class="data-table">
            <tr>
                <td style="width: 70%;">
                    <strong>Masa Pajak:</strong> (Beri tanda silang X pada bulan yang sesuai)<br><br>
                    @for($m = 1; $m <= 12; $m++)
                        <span style="margin-right: 8px;">
                            <span class="masa-box">{{ $realMonth === $m ? 'X' : '' }}</span>
                            {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                        </span>
                    @endfor
                </td>
                <td style="vertical-align: middle;">
                    <strong>Tahun Pajak:</strong><br><br>
                    <span class="code-box" style="font-size: 12px; letter-spacing: 4px;">{{ $realYear }}</span>
                </td>
            </tr>
        </table>

        <!-- Jumlah Setoran -->
        <div class="section-title">D. JUMLAH SETORAN PAJAK</div>
        <table class="data-table">
            <tr>
                <td style="width: 30%; font-size: 11px; font-weight: bold; vertical-align: middle;">
                    NOMINAL SETORAN
                </td>
                <td style="width: 2%; font-size: 11px; vertical-align: middle;">:</td>
                <td style="font-size: 13px; font-weight: bold; color: #1e3a8a; vertical-align: middle;">
                    Rp {{ number_format($taxAmount, 2, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td><strong>Terbilang</strong></td>
                <td>:</td>
                <td style="font-style: italic; font-size: 10px;">
                    "{{ $terbilang }}"
                </td>
            </tr>
        </table>

        <!-- Tanda Tangan -->
        <table class="signature-table">
            <tr>
                <td>
                    <strong>Diterima oleh Kantor Penerima Pembayaran</strong><br>
                    Tempat/Tanggal Penerimaan:<br><br><br><br><br>
                    ____________________________________<br>
                    Tanda Tangan & Cap Petugas Bank/Pos
                </td>
                <td>
                    <strong>Wajib Pajak / Penyetor</strong><br>
                    Barombong, {{ $realization->realization_date ? \Carbon\Carbon::parse($realization->realization_date)->translatedFormat('d F Y') : \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br><br><br><br><br>
                    <u>{{ $realization->procurement?->ppk?->name ?? 'Arnaldy Achmadita, M.T.' }}</u><br>
                    NIP. {{ $realization->procurement?->ppk?->nip ?? '19870425 201012 1 002' }}
                </td>
            </tr>
        </table>

    </div>

</body>
</html>
