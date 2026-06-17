<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Boleto — Sorteos ITSON</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f4ff; margin: 0; padding: 30px 16px; }
        .card {
            max-width: 420px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,.12);
            overflow: hidden;
        }
        .card-header {
            background: #1a3a6e;
            color: #fff;
            text-align: center;
            padding: 20px;
        }
        .card-header h1 { font-size: 18px; margin: 0; }
        .card-header p  { font-size: 12px; opacity: .8; margin: 4px 0 0; }
        .card-body { padding: 24px; }
        .status { text-align: center; margin-bottom: 20px; }
        .status .icon { font-size: 56px; line-height: 1; }
        .status.valid   .icon { color: #22a05a; }
        .status.invalid .icon { color: #e53e3e; }
        .status h2 { margin: 8px 0 4px; font-size: 20px; }
        .status.valid   h2 { color: #22a05a; }
        .status.invalid h2 { color: #e53e3e; }
        .detail-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .detail-table td { padding: 8px 10px; border-bottom: 1px solid #eee; }
        .detail-table td:first-child { color: #666; font-weight: bold; width: 45%; }
        .badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; }
        .badge-sold      { background: #fee2e2; color: #c0392b; }
        .badge-reserved  { background: #fef9c3; color: #92400e; }
        .badge-available { background: #d1fae5; color: #065f46; }
        .footer { text-align: center; padding: 14px; font-size: 11px; color: #aaa; border-top: 1px solid #eee; }
    </style>
</head>
<body>
<div class="card">
    <div class="card-header">
        <h1>Sorteos ITSON</h1>
        <p>Verificación de Autenticidad</p>
    </div>
    <div class="card-body">
        @if($boleto)
            <div class="status valid">
                <div class="icon">✓</div>
                <h2>Boleto Auténtico</h2>
                <p style="color:#555; font-size:13px;">Este boleto es válido y pertenece a Sorteos ITSON.</p>
            </div>

            <table class="detail-table">
                <tr>
                    <td>Sorteo</td>
                    <td>{{ $boleto->sorteo?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Número Digital</td>
                    <td><strong>#{{ str_pad($boleto->digital_number, 5, '0', STR_PAD_LEFT) }}</strong></td>
                </tr>
                <tr>
                    <td>Número Físico</td>
                    <td>{{ $boleto->physical_number }}</td>
                </tr>
                <tr>
                    <td>Cartera</td>
                    <td>{{ $boleto->cartera?->code ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Estado</td>
                    <td>
                        <span class="badge badge-{{ $boleto->status?->value }}">
                            {{ $boleto->status?->label() }}
                        </span>
                    </td>
                </tr>
                @if($boleto->sorteo?->draw_date)
                <tr>
                    <td>Fecha del Sorteo</td>
                    <td>{{ $boleto->sorteo->draw_date->format('d/m/Y') }}</td>
                </tr>
                @endif
            </table>
        @else
            <div class="status invalid">
                <div class="icon">✗</div>
                <h2>Boleto No Encontrado</h2>
                <p style="color:#555; font-size:13px;">
                    Este código QR no corresponde a ningún boleto registrado.<br>
                    Si crees que es un error, contacta a Sorteos ITSON.
                </p>
            </div>
        @endif
    </div>
    <div class="footer">Sorteos ITSON &copy; {{ date('Y') }} &bull; Verificación en tiempo real</div>
</div>
</body>
</html>
