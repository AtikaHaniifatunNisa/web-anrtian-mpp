<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        body { font-family: DejaVu Sans, sans-serif; margin: 14px; }
        .wrap { border: 3px solid #0b66c3; padding: 14px; text-align: center; }
        .mall { font-size: 16px; font-weight: 700; line-height: 1.25; }
        .sub { margin-top: 6px; font-size: 12px; }
        .layanan { margin-top: 4px; font-size: 13px; font-weight: 700; }
        .nomor { margin: 12px 0 8px; font-size: 96px; font-weight: 700; letter-spacing: 3px; }
        .footer{ display:flex; justify-content:space-between; margin-top:8px; font-size:12px; }
        .qr { margin-top: 6px; }
    </style>
    {{-- Load simple QR generator via php-qrcode if available, fallback ke teks --}}
</head>
<body>
    <div class="wrap">
        <div class="mall">{{ $mall }}</div>
        <div class="sub">{{ $zona }}  -  {{ $loket }}</div>
        <div class="layanan">Layanan {{ $layanan }}</div>
        <div class="nomor">{{ $nomor }}</div>

        <div class="footer">
            <div>{{ $tanggal }}</div>
            <div>{{ $waktu }}</div>
        </div>

        {{-- QR/Barcode --}}
        <div class="qr">
            @if(!empty($qrDataUri))
                <img src="{{ $qrDataUri }}" alt="QR" width="140" height="140">
            @else
                <div style="font-size:12px;">[QR tidak tersedia]</div>
            @endif
        </div>
    </div>
</body>
</html>


