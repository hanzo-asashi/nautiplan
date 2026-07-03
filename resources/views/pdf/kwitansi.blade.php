<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kwitansi Pembayaran - {{ $realization->receipt_number }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap');

        @page {
            size: A4 landscape;
            margin: 25px;
        }

        body {
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.6;
            color: #1e293b;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        .kwitansi-border {
            border: 2px solid #0f172a;
            border-radius: 8px;
            padding: 24px;
            position: relative;
            background-color: #ffffff;
        }

        .top-accent {
            height: 6px;
            background: linear-gradient(90deg, #1e3a8a 0%, #2563eb 50%, #3b82f6 100%);
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            border-radius: 6px 6px 0 0;
        }

        .kwitansi-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-bottom: 2px solid #cbd5e1;
            padding-bottom: 12px;
        }

        .kwitansi-title {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 1px;
            width: 55%;
            vertical-align: middle;
        }

        .kwitansi-meta {
            width: 45%;
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
            margin-bottom: 20px;
        }

        .main-form-table td {
            padding: 8px 10px;
            vertical-align: middle;
        }

        .main-form-table td.label {
            width: 18%;
            font-weight: 700;
            color: #334155;
        }

        .main-form-table td.colon {
            width: 2%;
            text-align: center;
            color: #64748b;
        }

        .main-form-table td.val {
            width: 80%;
        }

        .amount-box {
            font-family: 'Courier New', Courier, monospace;
            font-size: 16px;
            font-weight: 700;
            color: #1e3a8a;
            background-color: #f1f5f9;
            border: 2px double #1e3a8a;
            padding: 6px 16px;
            display: inline-block;
            border-radius: 4px;
            letter-spacing: 0.5px;
        }

        .terbilang-box {
            font-style: italic;
            font-weight: 600;
            color: #2563eb;
            background-color: #f0f9ff;
            border: 1px solid #e0f2fe;
            padding: 8px 14px;
            border-radius: 4px;
            display: inline-block;
            width: 95%;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 5px;
        }

        .signature-space {
            height: 55px;
        }

        .font-bold {
            font-weight: 700;
        }

        .footer-note {
            margin-top: 35px;
            border-top: 1px dashed #e2e8f0;
            padding-top: 10px;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
        }
    </style>
</head>

<body>

    <div class="kwitansi-border">
        <div class="top-accent"></div>

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
                            <td style="width: 40%; font-weight: 500; color: #475569;">Tahun Anggaran</td>
                            <td style="width: 5%; color: #64748b;">:</td>
                            <td style="width: 55%; font-weight: 700; color: #0f172a;">{{ $fiscalYear }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 500; color: #475569;">Nomor Bukti</td>
                            <td style="color: #64748b;">:</td>
                            <td style="font-weight: 700; color: #0f172a;">{{ $realization->receipt_number }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 500; color: #475569;">MAK</td>
                            <td style="color: #64748b;">:</td>
                            <td style="font-weight: 700; color: #0f172a; font-family: 'Courier New', Courier, monospace;">{{ $mak }}</td>
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
                <td class="val" style="font-weight: 500;">Kuasa Pengguna Anggaran Politeknik Pelayaran Barombong</td>
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
                <td class="val">
                    <div class="terbilang-box">
                        {{ ucwords($terbilang) }}
                    </div>
                </td>
            </tr>
            <tr>
                <td class="label">Untuk Pembayaran</td>
                <td class="colon">:</td>
                <td class="val" style="text-align: justify; color: #334155; font-size: 10.5px;">
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
                    <div style="color: #475569; margin-bottom: 4px;">Setuju dibayar,</div>
                    <div class="font-bold" style="color: #0f172a; font-size: 10px; letter-spacing: 0.5px;">KUASA PENGGUNA ANGGARAN</div>
                    <div class="signature-space"></div>
                    <div class="font-bold" style="text-decoration: underline; color: #0f172a; font-size: 11px;">{{ $kpaName }}</div>
                    <div style="font-size: 9.5px; color: #475569;">{{ $kpaRank }}</div>
                    <div style="font-size: 9.5px; color: #64748b;">NIP. {{ $kpaNip }}</div>
                </td>
                <td style="width: 50%;">
                    <div style="color: #475569; margin-bottom: 4px;">Makassar, {{ $realDate->format('d F Y') }}</div>
                    <div class="font-bold" style="color: #0f172a; font-size: 10px; letter-spacing: 0.5px;">{{ $vendorName }}</div>
                    <div class="signature-space"></div>
                    <div class="font-bold" style="text-decoration: underline; color: #0f172a; font-size: 11px;">{{ $vendorDirector }}</div>
                    <div style="font-size: 9.5px; color: #475569;">Direktur / Penyedia</div>
                </td>
            </tr>
        </table>

        <div class="footer-note">
            Dokumen kwitansi ini dibuat dan diverifikasi secara digital melalui <strong>Sistem Informasi & Monitoring Realisasi PPK (NAUTIPLAN) Politeknik Pelayaran Barombong</strong>.
        </div>
    </div>

</body>

</html>