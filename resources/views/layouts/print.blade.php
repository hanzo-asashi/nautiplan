<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Laporan')</title>
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
            margin-top: 2px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        /* Utility classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .mt-10 { margin-top: 10px; }
        .mt-20 { margin-top: 20px; }
        .mb-10 { margin-bottom: 10px; }
        .w-100 { width: 100%; }
        
        /* Table styles */
        .table-bordered {
            border: 1px solid #000;
        }
        .table-bordered th, .table-bordered td {
            border: 1px solid #000;
            padding: 4px;
        }
        
        @yield('styles')
    </style>
</head>
<body>
    @if (!isset($hideHeader) || !$hideHeader)
    <div class="header">
        <table class="kop-table">
            <tr>
                <td class="kop-logo">
                    <!-- Placeholder for Logo -->
                    <img src="{{ public_path('images/logo.png') }}" alt="Logo" style="width: 50px; height: auto;">
                </td>
                <td class="kop-text">
                    <div class="kop-title-1">KEMENTERIAN PERHUBUNGAN</div>
                    <div class="kop-title-1">BADAN PENGEMBANGAN SDM PERHUBUNGAN</div>
                    <div class="kop-title-3">POLITEKNIK PELAYARAN BAROMBONG</div>
                    <div class="kop-subtitle">Jalan Permandian Alam No. 1 Barombong, Makassar 90225</div>
                    <div class="kop-subtitle">Telepon: (0411) 3613636 | Email: info@poltekpelbarombong.ac.id</div>
                </td>
            </tr>
        </table>
    </div>
    @endif
    
    <div class="content">
        @yield('content')
    </div>
</body>
</html>
