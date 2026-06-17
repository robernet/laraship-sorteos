<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }

        .ticket {
            width: 540px;
            margin: 20px auto;
            border: 3px solid #1a3a6e;
            border-radius: 8px;
            overflow: hidden;
        }

        .header {
            background: #1a3a6e;
            color: #fff;
            text-align: center;
            padding: 14px 10px 10px;
        }
        .header h1 { font-size: 20px; letter-spacing: 1px; text-transform: uppercase; }
        .header h2 { font-size: 13px; font-weight: normal; margin-top: 4px; opacity: .85; }

        .cover {
            width: 100%;
            max-height: 140px;
            object-fit: cover;
            display: block;
        }

        .body { display: table; width: 100%; padding: 16px; }
        .col-left  { display: table-cell; width: 65%; vertical-align: top; }
        .col-right { display: table-cell; width: 35%; vertical-align: top; text-align: center; }

        .number-box {
            background: #f0f4ff;
            border: 2px solid #1a3a6e;
            border-radius: 6px;
            text-align: center;
            padding: 10px;
            margin-bottom: 12px;
        }
        .number-box .label { font-size: 10px; text-transform: uppercase; color: #666; }
        .number-box .value { font-size: 36px; font-weight: bold; color: #1a3a6e; line-height: 1; }

        .info-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .info-table td { padding: 4px 6px; font-size: 11px; border-bottom: 1px solid #eee; }
        .info-table td:first-child { color: #555; font-weight: bold; width: 45%; }

        .qr-label { font-size: 9px; color: #888; margin-top: 6px; }

        .footer {
            background: #f7f7f7;
            border-top: 2px solid #1a3a6e;
            text-align: center;
            padding: 8px;
            font-size: 9px;
            color: #888;
        }
    </style>
</head>
<body>
<div class="ticket">
    <div class="header">
        <h1>{{ $boleto->sorteo?->name ?? 'Sorteos ITSON' }}</h1>
        <h2>Boleto Digital Oficial</h2>
    </div>

    @if($boleto->sorteo?->cover_image)
        @php
            $img = $boleto->sorteo->cover_image;
            // Normalise to absolute filesystem path for DomPDF (URLs won't work)
            if (!str_starts_with($img, '/')) {
                $imgPath = public_path('storage/' . $img);
            } elseif (str_starts_with($img, '/storage/')) {
                $imgPath = public_path(ltrim($img, '/'));
            } else {
                $imgPath = public_path(ltrim($img, '/'));
            }
        @endphp
        @if(file_exists($imgPath))
            <img class="cover" src="{{ $imgPath }}" alt="Portada">
        @endif
    @endif

    <div class="body">
        <div class="col-left">
            <div class="number-box">
                <div class="label">Número de Boleto</div>
                <div class="value">{{ str_pad($boleto->digital_number, 5, '0', STR_PAD_LEFT) }}</div>
            </div>

            <table class="info-table">
                <tr>
                    <td>Número Físico</td>
                    <td>{{ $boleto->physical_number }}</td>
                </tr>
                <tr>
                    <td>Cartera</td>
                    <td>{{ $boleto->cartera?->code ?? '-' }}</td>
                </tr>
                @if($boleto->sorteo?->draw_date)
                <tr>
                    <td>Fecha del Sorteo</td>
                    <td>{{ $boleto->sorteo->draw_date->format('d/m/Y') }}</td>
                </tr>
                @endif
                @if($boleto->sorteo?->ticket_price)
                <tr>
                    <td>Precio</td>
                    <td>${{ number_format($boleto->sorteo->ticket_price, 2) }} MXN</td>
                </tr>
                @endif
            </table>
        </div>

        <div class="col-right">
            {!! $qrSvg !!}
            <div class="qr-label">Escanea para verificar<br>la autenticidad del boleto</div>
        </div>
    </div>

    <div class="footer">
        Este boleto es personal e intransferible &bull; Consérvalo para el día del sorteo
        &bull; Sorteos ITSON &copy; {{ date('Y') }}
    </div>
</div>
</body>
</html>
