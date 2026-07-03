<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Pembayaran - {{ $realization->receipt_number }}</title>
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
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .title {
            font-size: 12px;
            font-weight: bold;
            text-decoration: underline;
        }
        .subtitle {
            font-size: 10px;
        }
        .paragraph {
            text-align: justify;
            margin-bottom: 8px;
            text-indent: 30px;
        }
        .ident-table {
            width: 100%;
            border-collapse: collapse;
            margin-left: 20px;
            margin-bottom: 10px;
        }
        .ident-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .calc-table {
            width: 60%;
            margin-left: auto;
            margin-right: auto;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .calc-table td {
            padding: 4px 6px;
            vertical-align: top;
        }
        .calc-table tr.border-top td {
            border-top: 1px solid #000;
        }
        .calc-table tr.border-bottom td {
            border-bottom: 1px solid #000;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
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
            height: 50px;
        }
        .font-bold {
            font-weight: bold;
        }
    </style>
</head>
<body>

    @php
        $bapDate = $realization->bap_date ? \Carbon\Carbon::parse($realization->bap_date) : \Carbon\Carbon::parse($realization->realization_date);
        
        $days = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $dayName = $days[$bapDate->format('l')];
        
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $monthName = $months[$bapDate->month];
        
        $daySpelled = \App\Http\Controllers\ReportController::terbilang($bapDate->day);
        $yearSpelled = \App\Http\Controllers\ReportController::terbilang($bapDate->year);
        
        $dateSpelled = trim("{$daySpelled} bulan {$monthName} tahun {$yearSpelled}");
        
        $kpaName = $realization->procurement?->kpa?->name ?? 'Capt. SIDROTUL MUNTAHA, M.Si., M.Mar';
        $kpaNip = $realization->procurement?->kpa?->employee_id ?? '19670712 199808 1 001';
        $kpaRank = $realization->procurement?->kpa?->rank ?? 'Pembina Tk.I (IV/b)';
        
        $vendorDirector = $realization->procurement?->vendor?->bank_account_name ?? 'M. SUTANTO IDRIS, S.Pd';
        $vendorName = $realization->procurement?->vendor?->name ?? 'CV. TEKSAS JAYA PERKASA';
        $vendorAddress = $realization->procurement?->vendor?->address ?? 'JL. TIDUNG IV SETAPAK 2 NO. 96 MAKASSAR';
        
        $baPenyerahanNumber = $realization->ba_penyerahan_number ?? 'PL.109/57/22/POLTEKPEL.B-2024';
        $baPenyerahanDate = $realization->ba_penyerahan_date ? \Carbon\Carbon::parse($realization->ba_penyerahan_date)->format('d M Y') : '16 Desember 2024';
    @endphp

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

    <!-- Title Block -->
    <div class="title-block">
        <div class="title">BERITA ACARA PEMBAYARAN</div>
        <div class="subtitle">Nomor : {{ $realization->bap_number ?? '-' }}</div>
    </div>

    <div class="paragraph">
        Pada hari ini {{ $dayName }}, tanggal {{ $dateSpelled }}, yang bertanda tangan di bawah ini :
    </div>

    <table class="ident-table">
        <tr>
            <td style="width: 5%;">1.</td>
            <td style="width: 15%;" class="font-bold">Nama</td>
            <td style="width: 3%;">:</td>
            <td style="width: 77%; font-bold">{{ $kpaName }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="font-bold">NIP</td>
            <td>:</td>
            <td>{{ $kpaNip }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="font-bold">Jabatan</td>
            <td>:</td>
            <td>KUASA PENGGUNA ANGGARAN</td>
        </tr>
        <tr>
            <td></td>
            <td colspan="3">Dalam hal ini bertindak untuk dan atas nama Menteri Perhubungan, berdasarkan No. SK. 5480 Tahun 2023 Tanggal 20 Oktober 2023, Untuk selanjutnya di dalam Berita Acara disebut sebagai <span class="font-bold">PIHAK PERTAMA</span>.</td>
        </tr>
        
        <tr>
            <td style="padding-top: 8px;">2.</td>
            <td style="width: 15%; padding-top: 8px;" class="font-bold">Nama</td>
            <td style="width: 3%; padding-top: 8px;">:</td>
            <td style="width: 77%; font-bold; padding-top: 8px;">{{ $vendorDirector }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="font-bold">Jabatan</td>
            <td>:</td>
            <td>Direktur {{ $vendorName }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="font-bold">Alamat</td>
            <td>:</td>
            <td>{{ $vendorAddress }}</td>
        </tr>
        <tr>
            <td></td>
            <td colspan="3">Untuk selanjutnya di dalam Berita Acara disebut sebagai <span class="font-bold">PIHAK KEDUA</span>.</td>
        </tr>
    </table>

    <div class="paragraph" style="text-indent: 0;">
        Berdasarkan Berita Acara Penyerahan Barang Nomor : {{ $baPenyerahanNumber }} tanggal {{ $baPenyerahanDate }} tentang {{ $realization->procurement?->title ?? $realization->description }} yang dilaksanakan PIHAK KEDUA dan telah memenuhi syarat untuk menerima pembayaran sekaligus sebesar Rp. {{ number_format($realization->amount, 0, ',', '.') }} (<span style="font-style: italic;">{{ $terbilang }}</span>).
    </div>

    <!-- Calculations Table -->
    <table class="calc-table">
        <tr class="border-top">
            <td style="width: 60%;" class="font-bold">Jumlah harga seluruhnya sebesar</td>
            <td style="width: 10%;">:</td>
            <td style="width: 30%;" class="text-right font-bold">Rp {{ number_format($realization->amount, 0, ',', '.') }}</td>
        </tr>
        <tr class="border-bottom">
            <td class="font-bold">Jumlah yang dibayarkan sebesar</td>
            <td>:</td>
            <td class="text-right font-bold">Rp {{ number_format($realization->amount, 0, ',', '.') }}</td>
        </tr>
        <tr class="border-bottom">
            <td class="font-bold" style="text-align: right; padding-right: 50px;">Sisa</td>
            <td>:</td>
            <td class="text-right font-bold">N I H I L</td>
        </tr>
    </table>

    <div class="paragraph" style="text-indent: 0; margin-top: 10px;">
        Yakni dibayarkan sekaligus melalui Bank {{ $realization->procurement?->vendor?->bank_name ?? 'BTN' }} dengan Norek {{ $realization->procurement?->vendor?->bank_account_number ?? '00000192-01-30-0002078' }} setelah pekerjaan tersebut mencapai 100% (seratus persen) selesai dan disertai berita acara.
    </div>
    
    <div class="paragraph" style="text-indent: 0;">
        Demikian Berita Acara ini dibuat dalam rangkap 7 (tujuh) untuk dapat dipergunakan seperlunya.
    </div>

    <!-- Signatures -->
    <table class="signature-table">
        <tr>
            <td></td>
            <td>
                <div>DIBUAT DI : MAKASSAR</div>
                <div>PADA TANGGAL : {{ $bapDate->format('d M Y') }}</div>
            </td>
        </tr>
        <tr>
            <td class="text-center">
                <div>PIHAK KEDUA</div>
                <div class="signature-space"></div>
                <div class="font-bold" style="text-decoration: underline;">{{ $vendorDirector }}</div>
                <div>Direktur</div>
            </td>
            <td class="text-center">
                <div>PIHAK PERTAMA</div>
                <div class="signature-space"></div>
                <div class="font-bold" style="text-decoration: underline;">{{ $kpaName }}</div>
                <div>Pembina Tk.I (IV/b)</div>
                <div>NIP. {{ $kpaNip }}</div>
            </td>
        </tr>
    </table>

    <div style="margin-top: 40px; border-top: 1px dashed #cbd5e1; padding-top: 8px; text-align: center; font-size: 8px; color: #64748b;">
        Dokumen ini dibuat dan diverifikasi secara digital melalui **Sistem Informasi & Monitoring Realisasi PPK (SIM-PPK) Politeknik Pelayaran Barombong**.
    </div>

</body>
</html>
