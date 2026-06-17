<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h1 { font-size: 16px; color: #1a3a6e; margin-bottom: 4px; }
        .meta { color: #666; font-size: 10px; margin-bottom: 16px; }
        .kpis { display: table; width: 100%; margin-bottom: 16px; }
        .kpi  { display: table-cell; text-align: center; background: #f0f4ff; border: 1px solid #c8d6f0; padding: 8px; width: 25%; }
        .kpi .val { font-size: 18px; font-weight: bold; color: #1a3a6e; }
        .kpi .lbl { font-size: 9px; color: #666; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1a3a6e; color: #fff; padding: 5px 8px; text-align: left; }
        td { padding: 4px 8px; border-bottom: 1px solid #e0e0e0; }
        tr:nth-child(even) td { background: #f7f9ff; }
        .text-right { text-align: right; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #aaa; }
    </style>
</head>
<body>
    <h1>Reporte de Ventas — Sorteos ITSON</h1>
    <p class="meta">
        Período: {{ $from->format('d/m/Y') }} al {{ $to->format('d/m/Y') }} &bull;
        Agrupación: {{ ['day'=>'Diaria','week'=>'Semanal','month'=>'Mensual'][$period] ?? $period }} &bull;
        Generado: {{ now()->format('d/m/Y H:i') }}
    </p>

    <div class="kpis">
        <div class="kpi"><div class="val">{{ $data['totals']['orders'] }}</div><div class="lbl">Órdenes</div></div>
        <div class="kpi"><div class="val">${{ number_format($data['totals']['revenue'], 2) }}</div><div class="lbl">Ingresos MXN</div></div>
        <div class="kpi"><div class="val">{{ $data['totals']['tickets'] }}</div><div class="lbl">Boletos</div></div>
        <div class="kpi"><div class="val">${{ number_format($data['totals']['avg_order'], 2) }}</div><div class="lbl">Promedio / Orden</div></div>
    </div>

    <table>
        <thead>
            <tr><th>Período</th><th class="text-right">Órdenes</th><th class="text-right">Ingresos (MXN)</th><th class="text-right">Boletos</th></tr>
        </thead>
        <tbody>
            @foreach($data['labels'] as $i => $label)
            <tr>
                <td>{{ $label }}</td>
                <td class="text-right">{{ $data['orders'][$i] }}</td>
                <td class="text-right">${{ number_format($data['revenue'][$i], 2) }}</td>
                <td class="text-right">{{ $data['tickets'][$i] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p class="footer">Sorteos ITSON &copy; {{ date('Y') }}</p>
</body>
</html>
