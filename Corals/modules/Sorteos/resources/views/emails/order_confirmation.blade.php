<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Compra</title>
    <style>
        body { margin: 0; padding: 0; background: #f4f6fb; font-family: Arial, sans-serif; color: #333; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        .header  { background: #1a3a6e; padding: 28px 30px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 22px; letter-spacing: 1px; }
        .header p  { color: rgba(255,255,255,.75); margin: 6px 0 0; font-size: 13px; }
        .body    { padding: 28px 30px; }
        .greeting { font-size: 16px; margin-bottom: 18px; }
        .summary { background: #f0f4ff; border-left: 4px solid #1a3a6e; border-radius: 4px; padding: 14px 18px; margin-bottom: 24px; }
        .summary h3 { margin: 0 0 10px; font-size: 14px; color: #1a3a6e; text-transform: uppercase; letter-spacing: .5px; }
        .summary table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .summary td { padding: 4px 0; }
        .summary td:first-child { color: #666; width: 45%; }
        .tickets-title { font-size: 14px; font-weight: bold; margin: 20px 0 10px; color: #1a3a6e; border-bottom: 1px solid #dde3f0; padding-bottom: 6px; }
        .ticket-row { display: flex; align-items: center; padding: 8px 10px; background: #f9fafc; border: 1px solid #e2e8f0; border-radius: 4px; margin-bottom: 6px; font-size: 13px; }
        .ticket-num { font-size: 20px; font-weight: bold; color: #1a3a6e; width: 80px; }
        .ticket-detail { color: #555; flex: 1; }
        .cta { text-align: center; margin: 28px 0 10px; }
        .cta p { font-size: 13px; color: #666; margin-bottom: 12px; }
        .footer { background: #f7f8fc; border-top: 1px solid #e2e8f0; padding: 16px 30px; text-align: center; font-size: 11px; color: #999; }
        .footer a { color: #1a3a6e; text-decoration: none; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>{{ $order->sorteo?->name ?? 'Sorteos ITSON' }}</h1>
        <p>Confirmación de Compra · Boleto(s) Adjunto(s)</p>
    </div>

    <div class="body">
        <p class="greeting">Hola, <strong>{{ $order->buyer_name }}</strong> 👋</p>
        <p style="font-size:14px; margin-bottom:20px;">
            Tu compra ha sido confirmada. Encontrarás tus boletos digitales adjuntos a este correo en formato PDF.
            Guárdalos y preséntanos al momento del sorteo.
        </p>

        <div class="summary">
            <h3>Detalle de la Orden</h3>
            <table>
                <tr>
                    <td>Orden #</td>
                    <td><strong>{{ $order->id }}</strong></td>
                </tr>
                <tr>
                    <td>Sorteo</td>
                    <td>{{ $order->sorteo?->name }}</td>
                </tr>
                <tr>
                    <td>Método de pago</td>
                    <td>{{ $order->payment_method?->label() }}</td>
                </tr>
                <tr>
                    <td>Total</td>
                    <td><strong>${{ number_format($order->total_amount, 2) }} MXN</strong></td>
                </tr>
                @if($order->sorteo?->draw_date)
                <tr>
                    <td>Fecha del sorteo</td>
                    <td>{{ $order->sorteo->draw_date->format('d/m/Y') }}</td>
                </tr>
                @endif
            </table>
        </div>

        <div class="tickets-title">
            Tus Boletos ({{ $order->items->count() }})
        </div>

        @foreach($order->items as $item)
            @if($item->boleto)
            <div class="ticket-row">
                <div class="ticket-num">#{{ str_pad($item->boleto->digital_number, 5, '0', STR_PAD_LEFT) }}</div>
                <div class="ticket-detail">
                    Físico: {{ $item->boleto->physical_number }} &bull;
                    Cartera: {{ $item->boleto->cartera?->code ?? '-' }}
                </div>
            </div>
            @endif
        @endforeach

        <div class="cta">
            <p>¿Tienes dudas sobre tu compra? Contáctanos y con gusto te ayudaremos.</p>
        </div>
    </div>

    <div class="footer">
        Este correo fue enviado a <a href="mailto:{{ $order->buyer_email }}">{{ $order->buyer_email }}</a>
        por haber realizado una compra en Sorteos ITSON.<br>
        &copy; {{ date('Y') }} Sorteos ITSON · Todos los derechos reservados.
    </div>
</div>
</body>
</html>
