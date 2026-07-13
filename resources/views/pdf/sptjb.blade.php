<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Pernyataan Tanggung Jawab Belanja - SPTJB</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10.5px;
            line-height: 1.5;
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
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 12px;
            font-weight: bold;
            text-decoration: underline;
        }
        .subtitle {
            font-size: 10px;
        }
        .ident-table {
            width: 100%;
            border-collapse: collapse;
            margin-left: 20px;
            margin-bottom: 15px;
            margin-top: 10px;
        }
        .ident-table td {
            padding: 3px 5px;
            vertical-align: top;
        }
        .paragraph {
            text-align: justify;
            margin-bottom: 12px;
            text-indent: 30px;
        }
        .signature-table {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
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

    <!-- Title -->
    <div class="title-block">
        <div class="title">SURAT PERNYATAAN TANGGUNG JAWAB BELANJA (SPTJB)</div>
        <div class="subtitle">Nomor: {{ $realization->sptjb_number ?? '-' }}</div>
    </div>

    <!-- Body -->
    <p>Yang bertanda tangan di bawah ini:</p>

    <table class="ident-table">
        <tr>
            <td style="width: 25%;">Nama</td>
            <td style="width: 2%;">:</td>
            <td><strong>{{ $realization->procurement?->ppk?->name ?? 'Arnaldy Achmadita, M.T.' }}</strong></td>
        </tr>
        <tr>
            <td>NIP</td>
            <td>:</td>
            <td>{{ $realization->procurement?->ppk?->nip ?? '19870425 201012 1 002' }}</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td>Pejabat Pembuat Komitmen (PPK)</td>
        </tr>
        <tr>
            <td>Satuan Kerja</td>
            <td>:</td>
            <td>Politeknik Pelayaran Barombong</td>
        </tr>
    </table>

    <div class="paragraph">
        Menyatakan dengan sesungguhnya bahwa saya bertanggung jawab penuh atas kebenaran materiil dan keabsahan penggunaan dana serta pemotongan/penyetoran pajak atas segala transaksi belanja yang tercantum pada berkas realisasi kuitansi/bukti pembayaran nomor <strong>{{ $realization->receipt_number }}</strong> senilai <strong>Rp {{ number_format($realization->amount, 2, ',', '.') }}</strong> (<em>{{ $terbilang }}</em>) untuk keperluan <strong>{{ $realization->description }}</strong>.
    </div>

    <div class="paragraph">
        Bukti-bukti belanja tersebut di atas disimpan pada kantor Satuan Kerja Politeknik Pelayaran Barombong sesuai ketentuan yang berlaku untuk keperluan pemeriksaan aparat pengawas fungsional.
    </div>

    <div class="paragraph">
        Apabila di kemudian hari terbukti terdapat ketidakbenaran materiil dan/atau keabsahan berkas belanja tersebut yang mengakibatkan kerugian negara, saya bersedia bertanggung jawab sepenuhnya dan bersedia menyetorkan kerugian negara tersebut ke Kas Negara.
    </div>

    <p>Demikian surat pernyataan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p>

    <!-- Tanda Tangan -->
    <table class="signature-table">
        <tr>
            <td>
                &nbsp;
            </td>
            <td>
                Barombong, {{ $realization->sptjb_date ? \Carbon\Carbon::parse($realization->sptjb_date)->translatedFormat('d F Y') : \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                <strong>Pejabat Pembuat Komitmen (PPK)</strong>
                <br><br><br><br><br>
                <u>{{ $realization->procurement?->ppk?->name ?? 'Arnaldy Achmadita, M.T.' }}</u><br>
                NIP. {{ $realization->procurement?->ppk?->nip ?? '19870425 201012 1 002' }}
            </td>
        </tr>
    </table>

</body>
</html>
