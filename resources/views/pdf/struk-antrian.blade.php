<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Struk Antrian</title>
    <style>
        @page {
            margin: 0;
            size: 80mm 80mm; /* Ukuran struk persegi */
        }
        
        body {
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 15px;
            background: white;
            font-size: 12px;
            line-height: 1.2;
        }
        
        .struk-container {
            border: 2px solid #000000;
            padding: 10px;
            text-align: center;
            background: white;
            min-height: 60mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .header {
            margin-bottom: 10px;
        }
        
        .logo {
            margin-bottom: 8px;
            text-align: center;
        }
        
        .logo img {
            max-width: 60px;
            max-height: 40px;
            object-fit: contain;
        }
        
        .mall-title {
            font-size: 14px;
            font-weight: bold;
            margin: 4px 0;
            line-height: 1.1;
            color: #000;
        }
        
        .sub-info {
            font-size: 10px;
            margin: 2px 0;
            color: #333;
        }
        
        .queue-number {
            font-size: 36px;
            font-weight: bold;
            margin: 8px 0;
            letter-spacing: 1px;
            color: #000;
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .footer-info {
            position: relative;
            display: flex;
            justify-content: flex-start;
            align-items: flex-end;
            font-size: 6px;
            margin-top: 10px;
            padding: 0 3px;
            color: #666;
            flex-direction: row;
            width: 100%;
            height: 10px;
            box-sizing: border-box;
        }
        
        .divider {
            border-top: 1px solid #ccc;
            margin: 5px 0;
        }
        
        .time-info {
            text-align: left;
            flex: 0 0 auto;
            line-height: 1;
            margin: 0;
            padding: 0;
        }
        
        .date-info {
            position: absolute;
            right: 3px;
            bottom: 0;
            text-align: right;
            line-height: 1;
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
    <div class="struk-container">
        <div class="header">
            <div class="logo">
                @php
                    $logoPath = public_path('logo_pemkot.png');
                    $logoData = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';
                @endphp
                @if($logoData)
                    <img src="data:image/png;base64,{{ $logoData }}" alt="Logo Pemerintah Kota Surabaya">
                @else
                    <div style="font-size: 10px; color: #333;">
                        ████████<br>
                        ██&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;██<br>
                        ██&nbsp;&nbsp;&nbsp;&nbsp;██&nbsp;&nbsp;&nbsp;&nbsp;██<br>
                        ██&nbsp;&nbsp;&nbsp;&nbsp;██&nbsp;&nbsp;&nbsp;&nbsp;██<br>
                        ██&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;██<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;████████<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;████
                    </div>
                @endif
            </div>
            
            <div class="mall-title">{{ $data['mall'] }}</div>
            <div class="mall-title">{{ $data['kota'] }}</div>
            
            <div class="divider"></div>
            
            <div class="sub-info">{{ $data['zona'] }} - {{ $data['loket'] }}</div>
            <div class="sub-info">{{ $data['layanan'] }}</div>
        </div>
        
        <div class="queue-number">{{ $data['nomor'] }}</div>
        
        <div class="footer-info">
            <div class="time-info">{{ $data['waktu'] }}</div>
            <div class="date-info">{{ $data['tanggal'] }}</div>
        </div>
    </div>
</body>
</html>
