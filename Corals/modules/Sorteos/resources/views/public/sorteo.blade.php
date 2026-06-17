<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $sorteo->name }} — Sorteos ITSON</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f4ff; color: #222; }
        .hero { background: #1a3a6e; color: #fff; text-align: center; padding: 32px 16px; }
        .hero h1 { font-size: 26px; }
        .hero p  { font-size: 14px; opacity: .8; margin-top: 6px; }
        .hero-img { width: 100%; max-height: 260px; object-fit: cover; display: block; }
        .container { max-width: 600px; margin: 0 auto; padding: 24px 16px 48px; }
        .info-bar {
            display: flex; gap: 12px; flex-wrap: wrap;
            background: #fff; border-radius: 10px; padding: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,.08); margin-bottom: 24px;
        }
        .info-item { flex: 1; min-width: 120px; text-align: center; }
        .info-item .label { font-size: 11px; color: #888; text-transform: uppercase; }
        .info-item .value { font-size: 20px; font-weight: bold; color: #1a3a6e; margin-top: 2px; }
        .card { background: #fff; border-radius: 10px; padding: 24px; box-shadow: 0 2px 10px rgba(0,0,0,.08); }
        .card h2 { font-size: 16px; color: #1a3a6e; margin-bottom: 16px; border-bottom: 2px solid #e0e8ff; padding-bottom: 8px; }
        label { display: block; font-size: 12px; font-weight: bold; color: #555; margin-bottom: 4px; margin-top: 12px; }
        input[type="text"], input[type="email"], input[type="tel"], input[type="number"] {
            width: 100%; padding: 9px 11px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px;
        }
        .row { display: flex; gap: 12px; }
        .row > div { flex: 1; }
        .tabs { display: flex; margin-bottom: 12px; }
        .tab {
            flex: 1; text-align: center; padding: 8px; cursor: pointer;
            border: 1px solid #ccc; font-size: 13px; background: #f9f9f9; color: #555;
        }
        .tab:first-child { border-radius: 6px 0 0 6px; }
        .tab:last-child  { border-radius: 0 6px 6px 0; border-left: none; }
        .tab.active { background: #1a3a6e; color: #fff; border-color: #1a3a6e; }
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }
        .btn {
            display: block; width: 100%; margin-top: 20px; padding: 13px;
            background: #1a3a6e; color: #fff; border: none; border-radius: 8px;
            font-size: 16px; font-weight: bold; cursor: pointer;
        }
        .btn:hover { background: #142d57; }
        .alert { padding: 11px 14px; border-radius: 6px; font-size: 13px; margin-bottom: 16px; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .hint { font-size: 11px; color: #888; margin-top: 3px; }
        .footer { text-align: center; font-size: 11px; color: #aaa; margin-top: 24px; }
    </style>
</head>
<body>

@if($sorteo->cover_image)
    <img class="hero-img" src="{{ asset('storage/' . ltrim($sorteo->cover_image, '/')) }}" alt="{{ $sorteo->name }}">
@endif

<div class="hero">
    <h1>{{ $sorteo->name }}</h1>
    @if($sorteo->draw_date)
        <p>Sorteo el {{ $sorteo->draw_date->format('d \d\e F \d\e Y') }}</p>
    @endif
</div>

<div class="container">

    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <div class="info-bar">
        <div class="info-item">
            <div class="label">Precio por boleto</div>
            <div class="value">${{ number_format($sorteo->ticket_price, 2) }}</div>
        </div>
        <div class="info-item">
            <div class="label">Disponibles</div>
            <div class="value">{{ number_format($available) }}</div>
        </div>
        @if($sorteo->ends_at)
        <div class="info-item">
            <div class="label">Cierra</div>
            <div class="value" style="font-size:15px">{{ $sorteo->ends_at->format('d/m/Y') }}</div>
        </div>
        @endif
    </div>

    <div class="card">
        <h2>Comprar boletos</h2>

        <form method="POST" action="{{ route('sorteos.public.checkout', $sorteo->slug) }}">
            @csrf

            <div class="tabs">
                <div class="tab active" onclick="switchTab('random', this)">Boletos al azar</div>
                <div class="tab"        onclick="switchTab('pick', this)">Elegir números</div>
            </div>

            <div id="pane-random" class="tab-pane active">
                <label for="quantity">¿Cuántos boletos quieres?</label>
                <input type="number" id="quantity" name="quantity"
                       min="1" max="20" value="{{ old('quantity', 1) }}">
                <div class="hint">Máximo 20 por compra.</div>
            </div>

            <div id="pane-pick" class="tab-pane">
                <label for="ticket_numbers">Números de boleto (separados por coma)</label>
                <input type="text" id="ticket_numbers" name="ticket_numbers"
                       placeholder="Ej: 100, 205, 310" value="{{ old('ticket_numbers') }}">
                <div class="hint">Solo se asignarán los números que estén disponibles.</div>
            </div>

            <hr style="margin:20px 0; border:none; border-top:1px solid #eee;">

            <label for="buyer_name">Nombre completo *</label>
            <input type="text" id="buyer_name" name="buyer_name"
                   value="{{ old('buyer_name') }}" required>

            <label for="buyer_email">Correo electrónico *</label>
            <input type="email" id="buyer_email" name="buyer_email"
                   value="{{ old('buyer_email') }}" required>
            <div class="hint">Recibirás tus boletos digitales en este correo.</div>

            <label for="buyer_phone">Teléfono *</label>
            <input type="tel" id="buyer_phone" name="buyer_phone"
                   value="{{ old('buyer_phone') }}" required>

            <div class="row">
                <div>
                    <label for="buyer_city">Ciudad</label>
                    <input type="text" id="buyer_city" name="buyer_city"
                           value="{{ old('buyer_city') }}">
                </div>
                <div>
                    <label for="buyer_state">Estado</label>
                    <input type="text" id="buyer_state" name="buyer_state"
                           value="{{ old('buyer_state') }}">
                </div>
            </div>

            <button type="submit" class="btn">Continuar al pago &rarr;</button>
        </form>
    </div>

    <div class="footer">
        Sorteos ITSON &copy; {{ date('Y') }} &bull;
        <a href="{{ route('sorteos.boletos.resend-form') }}" style="color:#aaa;">¿Ya compraste? Reenviar mis boletos</a>
    </div>
</div>

<script>
function switchTab(mode, el) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('pane-' + mode).classList.add('active');
    var q  = document.getElementById('quantity');
    var tn = document.getElementById('ticket_numbers');
    if (mode === 'random') { q.disabled = false; tn.disabled = true; tn.value = ''; }
    else                   { q.disabled = true;  tn.disabled = false; q.value = ''; }
}
document.getElementById('ticket_numbers').disabled = true;
</script>
</body>
</html>
