<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Monev Triwulanan - {{ $activity->code }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #333333;
            line-height: 1.4;
        }
        @page {
            margin: 1.5cm;
        }
        .header {
            border-bottom: 2px solid #0284c7;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header table {
            width: 100%;
        }
        .header .title {
            font-size: 15px;
            font-weight: bold;
            color: #0c4a6e;
        }
        .header .subtitle {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #0284c7;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .info-table td {
            padding: 5px 0;
            vertical-align: top;
        }
        .info-table td.label {
            width: 130px;
            color: #64748b;
            font-weight: bold;
        }
        .info-table td.value {
            color: #0f172a;
        }
        .content-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 15px;
            color: #334155;
            min-height: 40px;
            white-space: pre-wrap;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            font-weight: bold;
            text-align: left;
            color: #475569;
        }
        .data-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            color: #334155;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-none { background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .badge-draft { background-color: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; }
        .badge-submitted { background-color: #fef3c7; color: #d97706; border: 1px solid #fcd34d; }
        .badge-reviewed { background-color: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }

        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .score-display {
            font-size: 24px;
            font-weight: bold;
            color: #0284c7;
            text-align: center;
            border: 2px solid #0284c7;
            border-radius: 8px;
            padding: 5px 10px;
            display: inline-block;
            background-color: #f0f9ff;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="title">Laporan Monitoring & Evaluasi Kinerja (Monev)</div>
                    <div class="subtitle">Sistem Terintegrasi Pengelolaan Program & Kegiatan | Politeknik Pelayaran Barombong</div>
                </td>
                <td class="text-right" style="vertical-align: bottom; font-size: 10px; color: #64748b;">
                    Dicetak pada: {{ now()->format('d M Y H:i') }}
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Informasi Laporan Monev</div>
    <table class="info-table">
        <tr>
            <td class="label">Kode Kegiatan</td>
            <td class="value">: <strong>{{ $activity->code }}</strong></td>
            <td class="label">Periode Monev</td>
            <td class="value">: <strong>Triwulan {{ substr($quarter, 1) }} ({{ $quarter }})</strong></td>
        </tr>
        <tr>
            <td class="label">Nama Kegiatan</td>
            <td class="value">: <strong>{{ $activity->name }}</strong></td>
            <td class="label">Tahun Anggaran</td>
            <td class="value">: {{ $activity->fiscalYear ? $activity->fiscalYear->year : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Unit Pelaksana</td>
            <td class="value">: {{ $activity->unit ? $activity->unit->name : '-' }}</td>
            <td class="label">Status Laporan</td>
            <td class="value">: 
                <span class="badge badge-{{ $report ? $report->status : 'none' }}">
                    @if(!$report)
                        Belum Dibuat
                    @else
                        @switch($report->status)
                            @case('draft') Draft Laporan @break
                            @case('submitted') Dikirim @break
                            @case('reviewed') Selesai Monev @break
                            @default {{ $report->status }}
                        @endswitch
                    @endif
                </span>
            </td>
        </tr>
        <tr>
            <td class="label">Penanggung Jawab</td>
            <td class="value">: {{ $activity->responsibleUser ? $activity->responsibleUser->name : '-' }}</td>
            <td class="label">Progres Kegiatan saat ini</td>
            <td class="value">: <strong>{{ $activity->progress_percentage }}%</strong></td>
        </tr>
    </table>

    <div class="section-title">Realisasi Indikator Kinerja (Triwulan {{ substr($quarter, 1) }})</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%">Kode Indikator</th>
                <th style="width: 50%">Indikator Kinerja</th>
                <th style="width: 15%">Tipe</th>
                <th style="width: 20%" class="text-right">Target vs Realisasi</th>
            </tr>
        </thead>
        <tbody>
            @php $hasIndicators = false; @endphp
            @foreach ($activity->indicators as $ind)
                @if ($ind->quarter === $quarter || $ind->quarter === 'annual')
                    @php $hasIndicators = true; @endphp
                    <tr>
                        <td>{{ $ind->code }}</td>
                        <td>{{ $ind->name }}</td>
                        <td class="text-center"><span class="badge" style="background-color: {{ $ind->indicator_type === 'iku' ? '#e0f2fe; color: #0369a1;' : '#f1f5f9; color: #475569;' }}">{{ strtoupper($ind->indicator_type) }}</span></td>
                        <td class="text-right">
                            Target: {{ $ind->target_value }} {{ $ind->unit_of_measure }}<br>
                            Realisasi: 
                            @if (is_null($ind->actual_value))
                                <span style="color: #94a3b8; font-style: italic;">Belum Lapor</span>
                            @else
                                <strong>{{ $ind->actual_value }} {{ $ind->unit_of_measure }}</strong>
                                @php
                                    $pct = $ind->target_value > 0 ? round(($ind->actual_value / $ind->target_value) * 100) : 0;
                                @endphp
                                <br><span style="color: {{ $pct >= 100 ? '#16a34a' : '#d97706' }}; font-size: 9px;">Cap: {{ $pct }}%</span>
                            @endif
                        </td>
                    </tr>
                @endif
            @endforeach
            @if (!$hasIndicators)
                <tr>
                    <td colspan="4" class="text-center" style="color: #94a3b8; font-style: italic;">Tidak ada indikator kinerja khusus untuk periode ini.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="section-title">Laporan Progres & Pelaksanaan Kegiatan</div>
    
    <div style="font-weight: bold; margin-bottom: 4px; color: #475569;">Deskripsi Kemajuan Pelaksanaan:</div>
    <div class="content-box">{{ $report ? $report->progress_description : 'Belum dilaporkan.' }}</div>

    <table style="width: 100%; margin-bottom: 15px;">
        <tr>
            <td style="width: 48%; vertical-align: top; padding-right: 2%;">
                <div style="font-weight: bold; margin-bottom: 4px; color: #475569;">Hambatan / Kendala Lapangan:</div>
                <div class="content-box" style="margin-bottom: 0;">{{ $report && $report->obstacles ? $report->obstacles : 'Tidak ada hambatan dilaporkan.' }}</div>
            </td>
            <td style="width: 48%; vertical-align: top; padding-left: 2%;">
                <div style="font-weight: bold; margin-bottom: 4px; color: #475569;">Solusi Tindak Lanjut:</div>
                <div class="content-box" style="margin-bottom: 0;">{{ $report && $report->solutions ? $report->solutions : 'Tidak ada solusi dilaporkan.' }}</div>
            </td>
        </tr>
    </table>

    @if ($report && $report->submittedBy)
        <div style="font-size: 10px; color: #64748b; margin-bottom: 15px;">
            Dilaporkan oleh: <strong>{{ $report->submittedBy->name }}</strong> pada {{ $report->submitted_at ? \Carbon\Carbon::parse($report->submitted_at)->format('d M Y H:i') : '-' }}
        </div>
    @endif

    @if ($report && $report->status === 'reviewed')
        <div class="section-title">Hasil Evaluasi & Rekomendasi M&E</div>
        
        <table style="width: 100%;">
            <tr>
                <td style="width: 25%; text-align: center; vertical-align: middle;">
                    <div style="font-size: 9px; font-weight: bold; color: #64748b; margin-bottom: 4px; text-transform: uppercase;">Skor Evaluasi</div>
                    <div class="score-display">{{ $report->evaluation_score }} / 100</div>
                </td>
                <td style="width: 75%; padding-left: 20px; vertical-align: top;">
                    <div style="font-weight: bold; margin-bottom: 2px; color: #475569;">Catatan Evaluator:</div>
                    <div class="content-box" style="margin-bottom: 10px;">{{ $report->evaluation_notes ?: '-' }}</div>
                    
                    <div style="font-weight: bold; margin-bottom: 2px; color: #475569;">Rekomendasi Tindak Lanjut:</div>
                    <div class="content-box" style="margin-bottom: 10px;">{{ $report->recommendations ?: '-' }}</div>
                </td>
            </tr>
        </table>
        
        <div style="font-size: 10px; color: #64748b; margin-top: 10px;">
            Evaluasi dilakukan oleh: <strong>{{ $report->reviewedBy ? $report->reviewedBy->name : 'Evaluator' }}</strong> pada {{ $report->reviewed_at ? \Carbon\Carbon::parse($report->reviewed_at)->format('d M Y H:i') : '-' }}
        </div>
    @else
        <div class="section-title">Hasil Evaluasi & Rekomendasi M&E</div>
        <div class="content-box" style="font-style: italic; text-align: center; color: #64748b;">
            Laporan ini belum dievaluasi oleh Tim Monitoring & Evaluasi.
        </div>
    @endif

    <div class="footer">
        Dokumen ini diterbitkan secara digital oleh Aplikasi NautiPlan. Politeknik Pelayaran Barombong.
    </div>

</body>
</html>
