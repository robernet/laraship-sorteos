<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de tu orden — Sorteos ITSON</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f4ff; margin: 0; padding: 30px 16px; }
        .card {
            max-width: 500px; margin: 0 auto; background: #fff;
            border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,.12); overflow: hidden;
        }
        .card-header { background: #1a3a6e; color: #fff; text-align: center; padding: 20px; }
        .card-header h1 { font-size: 18px; margin: 0; }
        .card-body { padding: 28px; }
        .status-badge {
            display: inline-block; padding: 6px 16px; border-radius: 20px;
            font-size: 13px; font-weight: bold; margin-bottom: 20px;
        }
        .status-confirmed { background: #d1fae5; color: #065f46; }
        .status-pending   { background: #fef9c3; color: #854d0e; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .info-row { display: flex; justify-content: space-between; font-size: 13px; padding: 7px 0; border-bottom: 1px solid #f0f0f0; }
        .info-row .lbl { color: #777; }
        .info-row .val { font-weight: bold; color: #222; }
        .boletos-table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 13px; }
        .boletos-table th { background: #f0f4ff; color: #1a3a6e; text-align: left; padding: 7px 10px; }
        .boletos-table td { padding: 7px 10px; border-bottom: 1px solid #f0f0f0; }
        .alert { padding: 14px; border-radius: 8px; font-size: 14px; text-align: center; }
        .alert-info    { background: #fef9c3; color: #854d0e; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .footer { text-align: center; padding: 14px; font-size: 11px; color: #aaa; border-top: 1px solid #eee; }
        .links { text-align: center; margin-top: 20px; font-size: 12px; }
        .links a { color: #1a3a6e; text-decoration: none; margin: 0 8px; }
    </style>
</head>
<body>
<div class="card">
    <div class="card-header">
        <h1>Sorteos ITSON</h1>
    </div>
    <div class="card-body">

        @if(!$order)
            <div class="alert alert-info">No se encontró la orden.</div>
        @else
            @php
                $statusVal = is_object($order->status) ? $order->status->value : $order->status;
                [$statusLabel, $statusClass] = match($statusVal) {
                    'confirmed' => ['Pago confirmado', 'confirmed'],
                    'cancelled' => ['Cancelada',        'cancelled'],
                    default     => ['Pendiente de pago','pending'],
                };
            @endphp

            <span class="status-badge status-{{ $statusClass }}">{{ $statusLabel }}</span>

            <div class="info-row">
                <span class="lbl">Orden</span>
                <span class="val">#{{ $order->id }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Comprador</span>
                <span class="val">{{ $order->buyer_name }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Sorteo</span>
                <span class="val">{{ $order->sorteo?->name }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Total</span>
                <span class="val">${{ number_format($order->total_amount, 2) }} MXN</span>
            </div>

            @if($order->isConfirmed())
                <div class="alert alert-success" style="margin-top:16px;">
                    ✓ Tus boletos digitales fueron enviados a <strong>{{ $order->buyer_email }}</strong>.
                    Revisa tu bandeja de entrada.
                </div>
            @elseif($order->isPending())
                <div class="alert alert-info" style="margin-top:16px;">
                    Tu pago está siendo procesado. Si ya pagaste, recibirás un correo de confirmación en breve.
                </div>
            @endif

            @if($order->items->isNotEmpty())
                <table class="boletos-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Número digital</th>
                            <th>Número físico</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $i => $item)
                            @if($item->boleto)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ str_pad($item->boleto->digital_number, 5, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $item->boleto->physical_number }}</td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            @endif

            <div class="links">
                <a href="{{ route('sorteos.boletos.resend-form') }}">Reenviar mis boletos</a>
                @if($order->sorteo?->slug)
                    &bull;
                    <a href="{{ route('sorteos.public.show', $order->sorteo->slug) }}">Comprar más boletos</a>
                @endif
            </div>
        @endif

    </div>
    <div class="footer">Sorteos ITSON &copy; {{ date('Y') }}</div>
</div>
</body>
</html>
