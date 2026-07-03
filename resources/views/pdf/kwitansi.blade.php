<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kwitansi Pembayaran - {{ $realization->receipt_number }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 40px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .kwitansi-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .kwitansi-title {
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            width: 60%;
        }

        .kwitansi-meta {
            width: 40%;
            font-size: 10.5px;
            border-collapse: collapse;
        }

        .kwitansi-meta td {
            padding: 3px 6px;
            vertical-align: top;
        }

        .main-form-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .main-form-table td {
            padding: 6px 8px;
            vertical-align: top;
        }

        .main-form-table td.label {
            width: 18%;
            font-weight: bold;
        }

        .main-form-table td.colon {
            width: 2%;
            text-align: center;
        }

        .main-form-table td.val {
            width: 80%;
        }

        .amount-box {
            font-size: 14px;
            font-weight: bold;
            background-color: #eaeaea;
            border: 1px solid #000;
            padding: 5px 12px;
            display: inline-block;
        }

        .terbilang-text {
            font-style: italic;
            font-weight: bold;
            text-transform: capitalize;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 5px;
        }

        .signature-space {
            height: 60px;
        }

        .font-bold {
            font-weight: bold;
        }
    </style>
</head>

<body>

    @php
        $realDate = \Carbon\Carbon::parse($realization->realization_date);
        $fiscalYear = $realization->activityBudget->activity->fiscalYear->year ?? '2026';

        $kpaName = $realization->procurement?->kpa?->name ?? 'Capt. SIDROTUL MUNTAHA, M.Si., M.Mar';
        $kpaNip = $realization->procurement?->kpa?->employee_id ?? '19670712 199808 1 001';
        $kpaRank = $realization->procurement?->kpa?->rank ?? 'Pembina Tk.I (IV/b)';

        $vendorDirector = $realization->procurement?->vendor?->bank_account_name ?? 'M. SUTANTO IDRIS, S.Pd';
        $vendorName = $realization->procurement?->vendor?->name ?? 'CV. TEKSAS JAYA PERKASA';

        $spNumber = $realization->procurement?->document_number ?? 'PL.107/67/7/POLTEKPEL.B/2024';
        $spDate = ($realization->procurement?->document_date) ? \Carbon\Carbon::parse($realization->procurement->document_date)->format('d December Y') : '12 Desember 2024';

        $bastNumber = $realization->bast_number ?? 'PL.109/57/22/POLTEKPEL.B-2024';
        $bastDate = $realization->bast_date ? \Carbon\Carbon::parse($realization->bast_date)->format('d December Y') : '16 Desember 2024';

        // Format MAK based on reference
        $accountCode = $realization->activityBudget->account_code ?? '525112';
        $mak = "022.12.DL.3996.DCB.011.601.0J." . $accountCode;
    @endphp

    <!-- Kwitansi Header -->
    <table class="kwitansi-header-table">
        <tr>
            <td class="kwitansi-title">
                KWITANSI / BUKTI PEMBAYARAN
            </td>
            <td>
                <table class="kwitansi-meta" style="margin-left: auto;">
                    <tr>
                        <td style="width: 40%;">Tahun Anggaran</td>
                        <td style="width: 5%;">:</td>
                        <td style="width: 55%; font-weight: bold;">{{ $fiscalYear }}</td>
                    </tr>
                    <tr>
                        <td>Nomor Bukti</td>
                        <td>:</td>
                        <td style="font-weight: bold;">{{ $realization->receipt_number }}</td>
                    </tr>
                    <tr>
                        <td>MAK</td>
                        <td>:</td>
                        <td style="font-weight: bold; font-family: Courier, monospace;">{{ $mak }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Main Form Fields -->
    <table class="main-form-table">
        <tr>
            <td class="label">Sudah Terima Dari</td>
            <td class="colon">:</td>
            <td class="val">Kuasa Pengguna Anggaran Politeknik Pelayaran Barombong</td>
        </tr>
        <tr>
            <td class="label">Jumlah Uang</td>
            <td class="colon">:</td>
            <td class="val">
                <span class="amount-box">Rp {{ number_format($realization->amount, 0, ',', '.') }}</span>
            </td>
        </tr>
        <tr>
            <td class="label">Terbilang</td>
            <td class="colon">:</td>
            <td class="val terbilang-text">
                {{ $terbilang }}
            </td>
        </tr>
        <tr>
            <td class="label">Untuk Pembayaran</td>
            <td class="colon">:</td>
            <td class="val" style="text-align: justify;">
                {{ $realization->description }} pada Politeknik Pelayaran Barombong, sesuai
                {{ strtoupper($realization->procurement?->procurement_type ?? 'Surat Pesanan') }} No. {{ $spNumber }}
                tanggal {{ $spDate }} dan Berita Acara Serah Terima Barang No. {{ $bastNumber }} tanggal {{ $bastDate }}
                pada tahun anggaran {{ $fiscalYear }}.
            </td>
        </tr>
    </table>

    <!-- Signatures -->
    <table class="signature-table">
        <tr>
            <td style="width: 50%;">
                <div>Setuju dibayar,</div>
                <div class="font-bold">KUASA PENGGUNA ANGGARAN</div>
                <div class="signature-space"></div>
                <div class="font-bold" style="text-decoration: underline;">{{ $kpaName }}</div>
                <div style="font-size: 9.5px;">{{ $kpaRank }}</div>
                <div style="font-size: 9.5px;">NIP. {{ $kpaNip }}</div>
            </td>
            <td style="width: 50%;">
                <div>Makassar, {{ $realDate->format('d F Y') }}</div>
                <div class="font-bold">{{ $vendorName }}</div>
                <div class="signature-space"></div>
                <div class="font-bold" style="text-decoration: underline;">{{ $vendorDirector }}</div>
                <div>Direktur</div>
            </td>
        </tr>
    </table>

    <div
        style="margin-top: 50px; border-top: 1px dashed #cbd5e1; padding-top: 8px; text-align: center; font-size: 8px; color: #64748b;">
        Dokumen kwitansi ini dibuat dan diverifikasi secara digital melalui **Sistem Informasi & Monitoring Realisasi
        PPK (NAUTIPLAN) Politeknik Pelayaran Barombong**.
    </div>

</body>

</html>