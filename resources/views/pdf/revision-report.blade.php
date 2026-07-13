<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Komparatif Revisi POK - Rev #{{ $revision->revision_number }}</title>
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
            margin-bottom: 15px;
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
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 10px;
            font-weight: bold;
            color: #555;
            margin-top: 2px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .meta-table td {
            padding: 6px 8px;
            vertical-align: top;
            border: 1px solid #e2e8f0;
        }
        .meta-label {
            font-weight: bold;
            width: 20%;
            color: #334155;
        }
        .meta-value {
            width: 80%;
        }
        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .comparison-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 8px 6px;
            border: 1px solid #334155;
        }
        .comparison-table td {
            padding: 8px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
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
        .badge {
            font-size: 8px;
            font-weight: bold;
            padding: 2px 4px;
            border-radius: 3px;
        }
        .badge-success {
            background-color: #dcfce7;
            color: #15803d;
        }
        .badge-danger {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .delta-positive {
            color: #15803d;
            font-weight: bold;
        }
        .delta-negative {
            color: #b91c1c;
            font-weight: bold;
        }
        .delta-neutral {
            color: #64748b;
        }
        .signature-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding-top: 10px;
        }
        .signature-title {
            margin-bottom: 50px;
        }
    </style>
</head>
<body>

    <!-- Kop Surat -->
    <div class="header">
        <table class="kop-table">
            <tr>
                <td class="kop-logo">
                    <img src="{{ public_path("images/logo-poltekpel.png") }}" height="50" alt="Logo Kemenhub">
                </td>
                <td class="kop-text">
                    <div class="kop-title-1">KEMENTERIAN PERHUBUNGAN</div>
                    <div class="kop-title-2">DIREKTORAT JENDERAL PERHUBUNGAN LAUT</div>
                    <div class="kop-title-3">POLITEKNIK PELAYARAN BAROMBONG</div>
                    <div class="kop-subtitle">Jl. Permandian Alam No. 1 Barombong, Makassar | Telp: (0411) 888647 | Email: info@poltekpelbarombong.ac.id</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Title Block -->
    <div class="title-block">
        <div class="title">Laporan Komparatif Revisi Pagu Anggaran (POK)</div>
        <div class="subtitle">Revisi Pagu #{{ $revision->revision_number }}</div>
    </div>

    <!-- Metadata Section -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Kegiatan</td>
            <td class="meta-value">{{ $revision->activityBudget->activity->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Kode MAK & Akun</td>
            <td class="meta-value">
                <strong>{{ $revision->activityBudget->account_code ?? '-' }}</strong> - {{ $revision->activityBudget->account_name ?? '-' }}
            </td>
        </tr>
        <tr>
            <td class="meta-label">Deskripsi Pagu</td>
            <td class="meta-value">{{ $revision->activityBudget->description ?? '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Tanggal Revisi</td>
            <td class="meta-value">{{ $revision->created_at->format('d F Y H:i') }} WITA</td>
        </tr>
        <tr>
            <td class="meta-label">Direvisi Oleh</td>
            <td class="meta-value">{{ $revision->revisedBy->name ?? 'Sistem' }} ({{ $revision->revisedBy->rank ?? '-' }})</td>
        </tr>
        <tr>
            <td class="meta-label" style="color: #b91c1c;">Alasan / Uraian Revisi</td>
            <td class="meta-value" style="font-style: italic; font-weight: 500;">
                "{{ $revision->description }}"
            </td>
        </tr>
    </table>

    <!-- Comparison Table -->
    <h3 style="font-size: 11px; margin-bottom: 6px; text-transform: uppercase; color: #0f172a;">Rincian Perubahan Item Anggaran</h3>
    <table class="comparison-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 35%;">Rincian Item Anggaran</th>
                <th style="width: 23%;" class="text-right">Semula (Pagu Lama)</th>
                <th style="width: 23%;" class="text-right">Menjadi (Pagu Baru)</th>
                <th style="width: 14%;" class="text-right">Selisih</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($revision->details as $detail)
                @php
                    $isNew = !$detail->total_semula || (float)$detail->total_semula === 0.0;
                    $isDeleted = !$detail->total_menjadi || (float)$detail->total_menjadi === 0.0;
                    $delta = (float)$detail->total_menjadi - (float)$detail->total_semula;
                @endphp
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>
                        {{ $detail->name_menjadi ?: $detail->name_semula }}
                        @if($isNew)
                            <span class="badge badge-success">Baru</span>
                        @elseif($isDeleted)
                            <span class="badge badge-danger">Dihapus</span>
                        @endif
                    </td>
                    <td class="text-right" style="color: #64748b;">
                        @if($isNew)
                            -
                        @else
                            {{ number_format($detail->volume_semula, 2, ',', '.') }} {{ $detail->unit_semula }} @ Rp {{ number_format($detail->unit_price_semula, 0, ',', '.') }}
                            <div class="font-bold" style="color: #000; font-size: 9.5px; margin-top: 2px;">Rp {{ number_format($detail->total_semula, 0, ',', '.') }}</div>
                        @endif
                    </td>
                    <td class="text-right">
                        @if($isDeleted)
                            -
                        @else
                            {{ number_format($detail->volume_menjadi, 2, ',', '.') }} {{ $detail->unit_menjadi }} @ Rp {{ number_format($detail->unit_price_menjadi, 0, ',', '.') }}
                            <div class="font-bold" style="font-size: 9.5px; margin-top: 2px;">Rp {{ number_format($detail->total_menjadi, 0, ',', '.') }}</div>
                        @endif
                    </td>
                    <td class="text-right font-bold">
                        @if($delta > 0)
                            <span class="delta-positive">+Rp {{ number_format($delta, 0, ',', '.') }}</span>
                        @elseif($delta < 0)
                            <span class="delta-negative">-Rp {{ number_format(abs($delta), 0, ',', '.') }}</span>
                        @else
                            <span class="delta-neutral">Rp 0</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            <!-- Total Summary Row -->
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="2" class="text-right">TOTAL PAGU:</td>
                <td class="text-right">
                    Rp {{ number_format($revision->amount_semula, 0, ',', '.') }}
                </td>
                <td class="text-right">
                    Rp {{ number_format($revision->amount_menjadi, 0, ',', '.') }}
                </td>
                <td class="text-right">
                    @php $totalDelta = (float)$revision->amount_menjadi - (float)$revision->amount_semula; @endphp
                    @if($totalDelta > 0)
                        <span class="delta-positive">+Rp {{ number_format($totalDelta, 0, ',', '.') }}</span>
                    @elseif($totalDelta < 0)
                        <span class="delta-negative">-Rp {{ number_format(abs($totalDelta), 0, ',', '.') }}</span>
                    @else
                        <span class="delta-neutral">Rp 0</span>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Signature Area -->
    <table class="signature-table">
        <tr>
            <td>
                <!-- Left spacing or alternative verification -->
                <div>&nbsp;</div>
            </td>
            <td>
                <div>Makassar, {{ $revision->created_at->format('d F Y') }}</div>
                <div class="signature-title font-bold">Pejabat Pembuat Komitmen (PPK)</div>
                <div style="text-decoration: underline;" class="font-bold">{{ $revision->revisedBy->name ?? 'Sistem' }}</div>
                <div>NIP. {{ $revision->revisedBy->employee_id ?? '-' }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
