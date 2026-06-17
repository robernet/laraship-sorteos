<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reenviar Boletos — Sorteos ITSON</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f4ff; margin: 0; padding: 30px 16px; }
        .card {
            max-width: 440px; margin: 0 auto; background: #fff;
            border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,.12); overflow: hidden;
        }
        .card-header { background: #1a3a6e; color: #fff; text-align: center; padding: 20px; }
        .card-header h1 { font-size: 18px; margin: 0; }
        .card-header p  { font-size: 12px; opacity: .8; margin: 4px 0 0; }
        .card-body { padding: 28px; }
        label { display: block; font-size: 13px; font-weight: bold; color: #444; margin-bottom: 6px; }
        input[type="email"] {
            width: 100%; padding: 10px 12px; border: 1px solid #ccc;
            border-radius: 6px; font-size: 14px; box-sizing: border-box;
        }
        button {
            width: 100%; margin-top: 14px; padding: 11px;
            background: #1a3a6e; color: #fff; border: none;
            border-radius: 6px; font-size: 15px; cursor: pointer;
        }
        button:hover { background: #142d57; }
        .alert { padding: 12px 14px; border-radius: 6px; font-size: 13px; margin-bottom: 16px; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .alert-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .footer { text-align: center; padding: 14px; font-size: 11px; color: #aaa; border-top: 1px solid #eee; }
    </style>
</head>
<body>
<div class="card">
    <div class="card-header">
        <h1>Sorteos ITSON</h1>
        <p>Reenvío de Boletos Digitales</p>
    </div>
    <div class="card-body">
        @if(session('resend_ok'))
            <div class="alert alert-success">
                ✓ Te reenviamos {{ session('resend_count') }} correo(s) a <strong>{{ session('resend_email') }}</strong>.
                Revisa tu bandeja de entrada y carpeta de spam.
            </div>
        @endif

        @if(session('resend_error'))
            <div class="alert alert-error">{{ session('resend_error') }}</div>
        @endif

        <form method="POST" action="{{ route('sorteos.boletos.resend-email') }}">
            @csrf
            <label for="email">Correo electrónico con el que compraste</label>
            <input type="email" id="email" name="email"
                   value="{{ old('email') }}"
                   placeholder="tu@correo.com" required autofocus>

            @error('email')
                <div style="color:#c0392b; font-size:12px; margin-top:4px;">{{ $message }}</div>
            @enderror

            <button type="submit">Reenviar mis boletos</button>
        </form>
    </div>
    <div class="footer">Sorteos ITSON &copy; {{ date('Y') }} &bull; Solo se reenvían órdenes confirmadas</div>
</div>
</body>
</html>
