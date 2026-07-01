<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Detail Kegiatan - {{ $activity->code }}</title>
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
            font-size: 16px;
            font-weight: bold;
            color: #0c4a6e;
        }
        .header .subtitle {
            font-size: 10px;
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
        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-draft { background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .badge-proposed { background-color: #fef3c7; color: #d97706; border: 1px solid #fcd34d; }
        .badge-approved { background-color: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
        .badge-progress { background-color: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; }
        .badge-completed { background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-cancelled { background-color: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
        
        .badge-low { background-color: #f1f5f9; color: #475569; }
        .badge-medium { background-color: #e0f2fe; color: #0369a1; }
        .badge-high { background-color: #ffedd5; color: #c2410c; }
        .badge-critical { background-color: #fee2e2; color: #be123c; }

        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .progress-container {
            width: 100px;
            background-color: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            display: inline-block;
            height: 10px;
            vertical-align: middle;
        }
        .progress-bar {
            background-color: #10b981;
            height: 100%;
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
                    <div class="title">NautiPlan - Detail Rencana Kegiatan</div>
                    <div class="subtitle">Sistem Terintegrasi Pengelolaan Program & Kegiatan | Politeknik Pelayaran Barombong</div>
                </td>
                <td class="text-right" style="vertical-align: bottom; font-size: 10px; color: #64748b;">
                    Dicetak pada: {{ now()->format('d M Y H:i') }}
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Informasi Umum Kegiatan</div>
    <table class="info-table">
        <tr>
            <td class="label">Kode Kegiatan</td>
            <td class="value">: <strong>{{ $activity->code }}</strong></td>
            <td class="label">Program</td>
            <td class="value">: {{ $activity->program ? '['.$activity->program->code.'] '.$activity->program->name : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Nama Kegiatan</td>
            <td class="value">: <strong>{{ $activity->name }}</strong></td>
            <td class="label">Unit Pelaksana</td>
            <td class="value">: {{ $activity->unit ? '['.$activity->unit->code.'] '.$activity->unit->name : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Deskripsi</td>
            <td class="value">: {{ $activity->description ?: 'Tidak ada deskripsi.' }}</td>
            <td class="label">Tahun Anggaran</td>
            <td class="value">: {{ $activity->fiscalYear ? $activity->fiscalYear->year : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Status</td>
            <td class="value">: 
                <span class="badge badge-{{ $activity->status }}">
                    @switch($activity->status)
                        @case('draft') Draft @break
                        @case('proposed') Ditinjau @break
                        @case('approved') Disetujui @break
                        @case('in_progress') Berjalan @break
                        @case('completed') Selesai @break
                        @case('cancelled') Dibatalkan @break
                        @default {{ $activity->status }}
                    @endswitch
                </span>
            </td>
            <td class="label">Prioritas</td>
            <td class="value">: 
                <span class="badge badge-{{ $activity->priority }}">
                    @switch($activity->priority)
                        @case('low') Rendah @break
                        @case('medium') Sedang @break
                        @case('high') Tinggi @break
                        @case('critical') Kritis @break
                        @default {{ $activity->priority }}
                    @endswitch
                </span>
            </td>
        </tr>
        <tr>
            <td class="label">Waktu Pelaksanaan</td>
            <td class="value">: {{ $activity->start_date ? \Carbon\Carbon::parse($activity->start_date)->format('d M Y') : '-' }} s/d {{ $activity->end_date ? \Carbon\Carbon::parse($activity->end_date)->format('d M Y') : '-' }}</td>
            <td class="label">Lokasi</td>
            <td class="value">: {{ $activity->location ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Penanggung Jawab</td>
            <td class="value">: {{ $activity->responsibleUser ? $activity->responsibleUser->name : '-' }}</td>
            <td class="label">Progres Kegiatan</td>
            <td class="value">: 
                <div class="progress-container">
                    <div class="progress-bar" style="width: {{ $activity->progress_percentage }}%"></div>
                </div>
                <span>{{ $activity->progress_percentage }}%</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Rencana Anggaran Biaya (Pagu vs Realisasi)</div>
    <table class="info-table" style="margin-bottom: 10px;">
        <tr>
            <td style="width: 50%;">
                Total Pagu Anggaran: <strong style="color: #0284c7; font-size: 13px;">Rp {{ number_format($activity->budgets->sum('amount'), 0, ',', '.') }}</strong>
            </td>
            <td style="width: 50%;">
                Total Realisasi Anggaran: <strong style="color: #10b981; font-size: 13px;">Rp {{ number_format($activity->budgets->flatMap->realizations->sum('amount'), 0, ',', '.') }}</strong>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 30%">Kategori Anggaran</th>
                <th style="width: 40%">Deskripsi Perincian</th>
                <th style="width: 25%" class="text-right">Pagu Anggaran</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($activity->budgets as $idx => $budget)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $budget->budget_category)) }}</td>
                    <td>{{ $budget->description ?: '-' }}</td>
                    <td class="text-right">Rp {{ number_format($budget->amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center" style="color: #94a3b8; font-style: italic;">Tidak ada rencana anggaran yang didaftarkan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($activity->budgets->flatMap->realizations->count() > 0)
        <div style="font-weight: bold; margin-top: 10px; margin-bottom: 5px; color: #475569;">Daftar Realisasi Anggaran:</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%">No</th>
                    <th style="width: 20%">Tanggal</th>
                    <th style="width: 50%">Keterangan Realisasi</th>
                    <th style="width: 25%" class="text-right">Jumlah Realisasi</th>
                </tr>
            </thead>
            <tbody>
                @php $realizationIdx = 1; @endphp
                @foreach ($activity->budgets as $budget)
                    @foreach ($budget->realizations as $realization)
                        <tr>
                            <td class="text-center">{{ $realizationIdx++ }}</td>
                            <td>{{ \Carbon\Carbon::parse($realization->realization_date)->format('d M Y') }}</td>
                            <td>[{{ ucwords(str_replace('_', ' ', $budget->budget_category)) }}] {{ $realization->description ?: '-' }}</td>
                            <td class="text-right">Rp {{ number_format($realization->amount, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="section-title">Sub-Kegiatan & Progres</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 35%">Nama Sub-Kegiatan</th>
                <th style="width: 25%">Penanggung Jawab</th>
                <th style="width: 20%">Status</th>
                <th style="width: 15%" class="text-right">Progres</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($activity->subActivities as $idx => $sub)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $sub->name }}</strong>
                        @if($sub->description)<br><span style="font-size: 9px; color: #64748b;">{{ $sub->description }}</span>@endif
                    </td>
                    <td>{{ $sub->assignedUser ? $sub->assignedUser->name : '-' }}</td>
                    <td>
                        <span class="badge" style="background-color: 
                            @switch($sub->status)
                                @case('pending') #f1f5f9; color: #475569; @break
                                @case('in_progress') #e0f2fe; color: #0284c7; @break
                                @case('completed') #dcfce7; color: #15803d; @break
                                @case('cancelled') #fee2e2; color: #dc2626; @break
                                @default #f1f5f9; color: #475569;
                            @endswitch
                        ">
                            @switch($sub->status)
                                @case('pending') Belum Mulai @break
                                @case('in_progress') Berjalan @break
                                @case('completed') Selesai @break
                                @case('cancelled') Batal @break
                                @default {{ $sub->status }}
                            @endswitch
                        </span>
                    </td>
                    <td class="text-right"><strong>{{ $sub->progress_percentage }}%</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: #94a3b8; font-style: italic;">Tidak ada sub-kegiatan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Indikator Kinerja Kegiatan (IKU & IKK)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%">Kode</th>
                <th style="width: 40%">Indikator Kinerja</th>
                <th style="width: 15%">Tipe</th>
                <th style="width: 15%">Target</th>
                <th style="width: 20%">Realisasi / Capaian</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($activity->indicators as $ind)
                <tr>
                    <td>{{ $ind->code }}</td>
                    <td>{{ $ind->name }}</td>
                    <td class="text-center"><span class="badge" style="background-color: {{ $ind->indicator_type === 'iku' ? '#e0f2fe; color: #0369a1;' : '#f1f5f9; color: #475569;' }}">{{ strtoupper($ind->indicator_type) }}</span></td>
                    <td>{{ $ind->target_value }} {{ $ind->unit_of_measure }} ({{ $ind->quarter }})</td>
                    <td>
                        @if (is_null($ind->actual_value))
                            <span style="color: #94a3b8; font-style: italic;">Belum Dilaporkan</span>
                        @else
                            <strong>{{ $ind->actual_value }} {{ $ind->unit_of_measure }}</strong> 
                            @php
                                $pct = $ind->target_value > 0 ? round(($ind->actual_value / $ind->target_value) * 100) : 0;
                            @endphp
                            <span style="color: {{ $pct >= 100 ? '#16a34a' : '#d97706' }};">({{ $pct }}%)</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: #94a3b8; font-style: italic;">Tidak ada indikator kinerja yang dikaitkan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($activity->approvalRequest)
        <div class="section-title">Histori Persetujuan & Verifikasi</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%">Langkah</th>
                    <th style="width: 25%">Verifikator Jabatan</th>
                    <th style="width: 25%">Nama Verifikator</th>
                    <th style="width: 15%">Status</th>
                    <th style="width: 30%">Catatan / Waktu</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($activity->approvalRequest->steps as $idx => $step)
                    <tr>
                        <td class="text-center">{{ $step->step_order }}</td>
                        <td>{{ $step->role ? $step->role->display_name : 'Role ID: '.$step->role_id }}</td>
                        <td>{{ $step->approver ? $step->approver->name : '-' }}</td>
                        <td>
                            <span class="badge" style="background-color: 
                                @switch($step->status)
                                    @case('pending') #f1f5f9; color: #475569; @break
                                    @case('approved') #dcfce7; color: #16a34a; @break
                                    @case('rejected') #fee2e2; color: #dc2626; @break
                                    @case('revision') #ffedd5; color: #d97706; @break
                                    @default #f1f5f9; color: #475569;
                                @endswitch
                            ">
                                @switch($step->status)
                                    @case('pending') Menunggu @break
                                    @case('approved') Disetujui @break
                                    @case('rejected') Ditolak @break
                                    @case('revision') Revisi @break
                                    @default {{ $step->status }}
                                @endswitch
                            </span>
                        </td>
                        <td>
                            @if ($step->notes)
                                <div style="font-style: italic; margin-bottom: 3px;">"{{ $step->notes }}"</div>
                            @endif
                            @if ($step->acted_at)
                                <span style="font-size: 9px; color: #64748b;">{{ \Carbon\Carbon::parse($step->acted_at)->format('d M Y H:i') }}</span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Dokumen ini diterbitkan secara digital oleh Aplikasi NautiPlan. Politeknik Pelayaran Barombong.
    </div>

</body>
</html>
