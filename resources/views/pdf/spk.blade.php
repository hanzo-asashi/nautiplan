<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Perintah Kerja - {{ $realization->receipt_number }}</title>
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
        .spk-info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .spk-info-table td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
            width: 50%;
        }
        .spk-title-box {
            text-align: center;
            padding: 15px 5px !important;
        }
        .spk-title {
            font-size: 12px;
            font-weight: bold;
            text-decoration: underline;
        }
        .spk-meta-row {
            margin-bottom: 3px;
        }
        .spk-meta-label {
            display: inline-block;
            width: 150px;
        }
        .full-width-box {
            border: 1px solid #000;
            border-top: none;
            padding: 5px;
            margin-top: -10px;
            margin-bottom: 10px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: left;
        }
        .data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .instruction-box {
            border: 1px solid #000;
            padding: 6px;
            margin-bottom: 15px;
            font-size: 8.5px;
        }
        .instruction-title {
            font-weight: bold;
            margin-bottom: 4px;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 5px;
        }
        .signature-space {
            height: 45px;
        }
        .font-bold {
            font-weight: bold;
        }
    </style>
</head>
<body>

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

    <!-- SPK Info Header -->
    <table class="spk-info-table">
        <tr>
            <td class="spk-title-box">
                <div class="font-bold">Paket Pekerjaan :</div>
                <div style="margin-bottom: 10px;">{{ $realization->procurement?->title ?? $realization->description }}</div>
                <div class="spk-title">SURAT PERINTAH KERJA (SPK)</div>
            </td>
            <td>
                <div class="spk-meta-row">
                    <span class="spk-meta-label">Memperhatikan Nota Dinas Nomor</span>
                    <span>: {{ $realization->procurement?->nota_dinas_number ?? '-' }}</span>
                </div>
                <div class="spk-meta-row" style="margin-bottom: 8px;">
                    <span class="spk-meta-label">Tanggal Nota Dinas</span>
                    <span>: {{ $realization->procurement?->nota_dinas_date ? \Carbon\Carbon::parse($realization->procurement->nota_dinas_date)->format('d December Y') : '-' }}</span>
                </div>
                <div class="spk-meta-row">
                    <span class="spk-meta-label">Dan berdasarkan BA HP Langsung</span>
                    <span>: {{ $realization->procurement?->ba_pl_number ?? '-' }}</span>
                </div>
                <div class="spk-meta-row" style="margin-bottom: 8px;">
                    <span class="spk-meta-label">Tanggal BA HP Langsung</span>
                    <span>: {{ $realization->procurement?->ba_pl_date ? \Carbon\Carbon::parse($realization->procurement->ba_pl_date)->format('d December Y') : '-' }}</span>
                </div>
                <div class="spk-meta-row">
                    <span class="spk-meta-label font-bold">SURAT PERINTAH KERJA (SPK)</span>
                    <span class="font-bold">: {{ $realization->procurement?->document_number ?? '-' }}</span>
                </div>
                <div class="spk-meta-row">
                    <span class="spk-meta-label">Tanggal SPK</span>
                    <span>: {{ $realization->procurement?->document_date ? \Carbon\Carbon::parse($realization->procurement->document_date)->format('d December Y') : '-' }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="full-width-box">
        <div class="spk-meta-row">
            <span style="display: inline-block; width: 100px;" class="font-bold">Sumber Dana :</span>
            <span>Dibebankan pada DIPA POLTEKPEL BAROMBONG TA {{ $realization->activityBudget->activity->fiscalYear->year ?? '2026' }} untuk mata anggaran kegiatan : {{ $realization->activityBudget->account_code ?? '-' }} {{ $realization->activityBudget->account_name ?? '-' }}</span>
        </div>
        <div class="spk-meta-row">
            <span style="display: inline-block; width: 100px;" class="font-bold">Waktu Pelaksanaan :</span>
            <span>{{ $realization->procurement?->work_duration ?? '-' }}</span>
        </div>
    </div>

    <!-- Items Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 45%;">Nama Barang / Jasa</th>
                <th style="width: 10%;">Volume</th>
                <th style="width: 10%;">Satuan</th>
                <th style="width: 15%;">Harga Satuan</th>
                <th style="width: 15%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $subtotal = 0;
            @endphp
            @forelse($realization->items as $index => $item)
                @php
                    $itemTotal = $item->volume * $item->unit_price;
                    $subtotal += $itemTotal;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->name }} <br><small style="color: #555;">{{ $item->remarks }}</small></td>
                    <td class="text-center">{{ number_format($item->volume, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item->unit }}</td>
                    <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($itemTotal, 0, ',', '.') }}</td>
                </tr>
            @empty
                @php
                    $subtotal = $realization->amount / 1.11; // back-calculate assuming 11% PPN
                @endphp
                <tr>
                    <td class="text-center">1</td>
                    <td>{{ $realization->description }}</td>
                    <td class="text-center">1</td>
                    <td class="text-center">Paket</td>
                    <td class="text-right">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforelse
            
            @php
                $ppn = $subtotal * 0.11;
                $total = $subtotal + $ppn;
            @endphp
            <tr>
                <td colspan="5" class="text-right font-bold">Jumlah</td>
                <td class="text-right font-bold">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="5" class="text-right font-bold">PPN 11%</td>
                <td class="text-right font-bold">Rp {{ number_format($ppn, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="5" class="text-right font-bold">Jumlah Total</td>
                <td class="text-right font-bold" style="background-color: #f2f2f2;">Rp {{ number_format($total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-bottom: 10px;">
        <span class="font-bold">Terbilang : </span>
        <span style="font-style: italic; text-transform: capitalize;">{{ $terbilang }}</span>
    </div>

    <!-- Instructions -->
    <div class="instruction-box">
        <div class="instruction-title">Instruksi Kepada Penyedia :</div>
        Penagihan hanya dapat dilakukan setelah penyelesaian pekerjaan yang diperintahkan dalam SPK ini dan dibuktikan dengan Berita Acara Serah Terima (BAST). Jika pekerjaan tidak dapat diselesaikan dalam jangka waktu pelaksanaan pekerjaan karena kesalahan atau kelalaian Penyedia maka Penyedia berkewajiban untuk membayar denda sebesar 1/1000 (satu per seribu) dari nilai Kontrak untuk setiap hari keterlambatan dan disetorkan ke kas negara.
    </div>

    <!-- Signatures -->
    <table class="signature-table">
        <tr>
            <td>
                <div>Untuk dan atas nama Poltekpel Barombong</div>
                <div class="font-bold">Pejabat Pembuat Komitmen (PPK) BLU</div>
                <div class="signature-space"></div>
                <div class="font-bold" style="text-decoration: underline;">{{ $realization->procurement?->ppk?->name ?? 'ARNALDY ACHMADITA A., S.T., M.T' }}</div>
                <div>NIP. {{ $realization->procurement?->ppk?->employee_id ?? '19800123 200912 1 002' }}</div>
                <div style="font-size: 8px; color: #555;">{{ $realization->procurement?->ppk?->rank ?? 'Penata (III/c)' }}</div>
            </td>
            <td>
                <div>Untuk dan atas nama Penyedia</div>
                <div class="font-bold">{{ $realization->procurement?->vendor?->name ?? 'CV. YUSHAR' }}</div>
                <div class="signature-space"></div>
                <div class="font-bold" style="text-decoration: underline;">{{ $realization->procurement?->vendor?->bank_account_name ?? 'HARUDDIN' }}</div>
                <div style="font-size: 9px; color: #555;">Direktur / Penanggung Jawab</div>
            </td>
        </tr>
    </table>

    <div style="margin-top: 20px; border-top: 1px dashed #000; padding-top: 5px; text-align: center; font-size: 7px; color: #555;">
        Dokumen ini diterbitkan secara digital melalui Sistem Informasi & Monitoring Realisasi PPK (SIM-PPK) Politeknik Pelayaran Barombong.
    </div>

</body>
</html>
