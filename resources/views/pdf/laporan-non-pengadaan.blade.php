<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Realisasi Non-Pengadaan</title>
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
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            font-size: 9px;
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
        .total-row {
            font-weight: bold;
            background-color: #f1f5f9;
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
        <div class="title">LAPORAN REKAPITULASI REALISASI NON-PENGADAAN</div>
        <div class="subtitle">Sistem Informasi & Monitoring Realisasi PPK | Dicetak pada: {{ now()->format('d M Y H:i') }}</div>
    </div>

    <!-- Tabel Data Realisasi -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 15%">Tanggal</th>
                <th style="width: 25%">Program / Unit</th>
                <th style="width: 35%">Uraian Pekerjaan / Realisasi</th>
                <th style="width: 20%" class="text-right">Jumlah Realisasi</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @forelse ($realizations as $idx => $real)
                @php $total += $real->amount; @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($real->realization_date)->format('d M Y') }}</td>
                    <td>
                        <div style="font-weight: bold;">{{ $real->activityBudget->activity->unit->code ?: '-' }}</div>
                        <div style="font-size: 8px; color: #475569;">Prog: {{ $real->activityBudget->activity->program->code ?: '-' }}</div>
                    </td>
                    <td>
                        <strong>{{ $real->description ?: '-' }}</strong>
                        @if ($real->receipt_number)
                            <div style="font-size: 8px; color: #64748b; margin-top: 2px;">No. Kuitansi: {{ $real->receipt_number }}</div>
                        @endif
                    </td>
                    <td class="text-right">Rp {{ number_format($real->amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: #94a3b8; font-style: italic; padding: 20px;">
                        Tidak ada transaksi realisasi non-pengadaan.
                    </td>
                </tr>
            @endforelse
            @if ($realizations->count() > 0)
                <tr class="total-row">
                    <td colspan="4" class="text-right">TOTAL REALISASI NON-PENGADAAN:</td>
                    <td class="text-right">Rp {{ number_format($total, 0, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div style="margin-top: 50px; border-top: 1px dashed #cbd5e1; padding-top: 8px; text-align: center; font-size: 8px; color: #64748b;">
        Dokumen ini digenerate secara otomatis oleh **Sistem Informasi & Monitoring Realisasi PPK (SIM-PPK) Politeknik Pelayaran Barombong**.
    </div>

</body>
</html>
