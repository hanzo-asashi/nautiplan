@extends('layouts.print', ['hideHeader' => true])

@section('title', 'Berita Acara Serah Terima - ' . $realization->receipt_number)

@section('styles')
<style>
    .signatures {
        margin-top: 30px;
        width: 100%;
        page-break-inside: avoid;
    }
    .signature-table {
        width: 100%;
        border-collapse: collapse;
        border: none;
    }
    .signature-table td {
        width: 50%;
        text-align: center;
        vertical-align: top;
        padding: 5px;
        border: none;
    }
    .signature-space {
        height: 60px;
    }
    .ttd-name {
        font-weight: bold;
        text-decoration: underline;
    }
    table, th, td {
        border-collapse: collapse;
    }
    .mt-4 { margin-top: 15px; }
    .mb-4 { margin-bottom: 15px; }
    
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
    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        margin-bottom: 10px;
    }
    .data-table th, .data-table td {
        border: 1px solid #000;
        padding: 5px 7px;
        text-align: left;
    }
    .data-table th {
        background-color: #f2f2f2;
        font-weight: bold;
        text-align: center;
    }
    .font-bold {
        font-weight: bold;
    }
</style>
@endsection

@section('content')
    @php
        $bastDate = $realization->bast_date ? \Carbon\Carbon::parse($realization->bast_date) : \Carbon\Carbon::parse($realization->realization_date);
        
        $days = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $dayName = $days[$bastDate->format('l')];
        
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $monthName = $months[$bastDate->month];
        
        $daySpelled = \App\Helpers\FormatHelper::terbilang($bastDate->day);
        $yearSpelled = \App\Helpers\FormatHelper::terbilang($bastDate->year);
        
        $dateSpelled = trim("{$daySpelled} bulan {$monthName} tahun {$yearSpelled}");
    @endphp

    <!-- Kop Surat -->
    <div class="header">
        <table class="kop-table">
            <tr>
                <td class="kop-logo">
                    <img src="{{ public_path("images/logo-poltekpel.png") }}" height="50" alt="Logo Kemenhub">
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
        <div class="title">BERITA ACARA SERAH TERIMA BARANG</div>
        <div class="subtitle">Nomor : {{ $realization->bast_number ?? '-' }}</div>
    </div>

    <div class="paragraph">
        Pada hari ini {{ $dayName }}, tanggal {{ $dateSpelled }}, masing-masing yang bertanda tangan di bawah ini :
    </div>

    <table class="ident-table">
        <tr>
            <td style="width: 5%;">I.</td>
            <td style="width: 15%;" class="font-bold">Nama</td>
            <td style="width: 3%;">:</td>
            <td style="width: 77%; font-bold">{{ $realization->procurement?->ppk?->name ?? 'ARNALDY ACHMADITA A., S.T., M.T' }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="font-bold">NIP</td>
            <td>:</td>
            <td>{{ $realization->procurement?->ppk?->employee_id ?? '19800123 200912 1 002' }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="font-bold">Jabatan</td>
            <td>:</td>
            <td>PEJABAT PEMBUAT KOMITMEN BLU</td>
        </tr>
        <tr>
            <td></td>
            <td colspan="3">Dalam hal ini yang berhak menerima dan mengeluarkan hasil Pekerjaan, untuk selanjutnya disebut sebagai <span class="font-bold">PIHAK PERTAMA</span>.</td>
        </tr>
        
        <tr>
            <td style="padding-top: 8px;">II.</td>
            <td style="width: 15%; padding-top: 8px;" class="font-bold">Nama</td>
            <td style="width: 3%; padding-top: 8px;">:</td>
            <td style="width: 77%; font-bold; padding-top: 8px;">{{ $realization->procurement?->vendor?->bank_account_name ?? 'HARUDDIN' }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="font-bold">Jabatan</td>
            <td>:</td>
            <td>Direktur / Penyedia {{ $realization->procurement?->vendor?->name ?? 'CV. YUSHAR' }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="font-bold">Alamat</td>
            <td>:</td>
            <td>{{ $realization->procurement?->vendor?->address ?? '-' }}</td>
        </tr>
        <tr>
            <td></td>
            <td colspan="3">Untuk selanjutnya disebut sebagai <span class="font-bold">PIHAK KEDUA</span>.</td>
        </tr>
    </table>

    <div class="paragraph" style="text-indent: 0;">
        Kedua belah pihak menyetujui untuk menerima/menyerahkan barang-barang sesuai {{ strtoupper($realization->procurement?->procurement_type ?? 'Surat Pesanan') }} Nomor {{ $realization->procurement?->document_number ?? '-' }} tanggal {{ ($realization->procurement?->document_date) ? App\Helpers\FormatHelper::tanggal($realization->procurement->document_date) : '-' }} sebagai berikut :
    </div>

    <!-- Items Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;">No.</th>
                <th style="width: 62%;">Nama Barang / Deskripsi</th>
                <th style="width: 15%;">Jumlah</th>
                <th style="width: 15%;">Satuan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($realization->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->name }}</td>
                    <td class="text-center font-bold">{{ number_format($item->volume, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item->unit }}</td>
                </tr>
            @empty
                <tr>
                    <td class="text-center">1</td>
                    <td>{{ $realization->description }}</td>
                    <td class="text-center font-bold">1</td>
                    <td class="text-center">Paket</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="paragraph" style="text-indent: 0; margin-top: 10px;">
        Selanjutnya penyimpanan barang - barang dilakukan oleh Bendaharawan Materiil pada Politeknik Pelayaran Barombong. Demikianlah Berita Acara ini dibuat dengan sebenar - benarnya untuk diketahui dan dipergunakan seperlunya.
    </div>

    <!-- Signatures -->
    <table class="signature-table">
        <tr>
            <td class="text-center">
                <div>PIHAK PERTAMA</div>
                <div class="signature-space"></div>
                <div class="font-bold" style="text-decoration: underline;">{{ $realization->procurement?->ppk?->name ?? 'ARNALDY ACHMADITA A., S.T., M.T' }}</div>
                <div>NIP. {{ $realization->procurement?->ppk?->employee_id ?? '19800123 200912 1 002' }}</div>
                <div style="font-size: 8px; color: #555;">{{ $realization->procurement?->ppk?->rank ?? 'Penata (III/c)' }}</div>
            </td>
            <td class="text-center">
                <div>Makassar, {{ $bastDate->format('d M Y') }}</div>
                <div>PIHAK KEDUA</div>
                <div class="signature-space"></div>
                <div class="font-bold" style="text-decoration: underline;">{{ $realization->procurement?->vendor?->bank_account_name ?? 'HARUDDIN' }}</div>
                <div>Direktur {{ $realization->procurement?->vendor?->name ?? 'CV. YUSHAR' }}</div>
            </td>
        </tr>
    </table>

    <div style="margin-top: 40px; border-top: 1px dashed #cbd5e1; padding-top: 8px; text-align: center; font-size: 8px; color: #64748b;">
        Dokumen ini dibuat dan diverifikasi secara digital melalui **Sistem Informasi & Monitoring Realisasi PPK (SIM-PPK) Politeknik Pelayaran Barombong**.
    </div>
@endsection
