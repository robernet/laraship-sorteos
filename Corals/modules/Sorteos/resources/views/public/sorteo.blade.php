@extends('layouts.master')

@section('title', $sorteo->name . ' — Sorteos ITSON')

@section('content')

    {{-- Cover Image --}}
    @if($sorteo->cover_image)
        <div class="srt-cover">
            <img src="{{ asset('storage/' . ltrim($sorteo->cover_image, '/')) }}" alt="{{ $sorteo->name }}">
            <div class="srt-cover__overlay"></div>
            <div class="srt-cover__text">
                <h1>{{ $sorteo->name }}</h1>
                @if($sorteo->draw_date)
                    <p><i class="fa fa-calendar"></i> Sorteo el {{ $sorteo->draw_date->format('d \d\e F \d\e Y') }}</p>
                @endif
            </div>
        </div>
    @else
        <div class="srt-hero">
            <h1>{{ $sorteo->name }}</h1>
            @if($sorteo->draw_date)
                <p><i class="fa fa-calendar"></i> Sorteo el {{ $sorteo->draw_date->format('d \d\e F \d\e Y') }}</p>
            @endif
        </div>
    @endif

    <div class="srt-container">

        @if(session('error'))
            <div class="srt-alert srt-alert--danger">
                <i class="fa fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if(session('success'))
            <div class="srt-alert srt-alert--success">
                <i class="fa fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Stats --}}
        <div class="srt-stats">
            <div class="srt-stat">
                <div class="srt-stat__label">Precio por boleto</div>
                <div class="srt-stat__value">${{ number_format($sorteo->ticket_price, 0) }}</div>
            </div>
            <div class="srt-stat">
                <div class="srt-stat__label">Disponibles</div>
                <div class="srt-stat__value">{{ number_format($available) }}</div>
            </div>
            @if($sorteo->ends_at)
            <div class="srt-stat">
                <div class="srt-stat__label">Cierra</div>
                <div class="srt-stat__value" style="font-size:16px">{{ $sorteo->ends_at->format('d/m/Y') }}</div>
            </div>
            @endif
        </div>

        {{-- Purchase Form --}}
        <div class="srt-card">
            <div class="srt-card__title">
                <i class="fa fa-ticket"></i> Comprar boletos
            </div>

            <form method="POST" action="{{ route('sorteos.public.checkout', $sorteo->slug) }}">
                @csrf

                <div class="srt-tabs">
                    <button type="button" class="srt-tab active" onclick="switchTab('random', this)">
                        <i class="fa fa-random"></i> Boletos al azar
                    </button>
                    <button type="button" class="srt-tab" onclick="switchTab('pick', this)">
                        <i class="fa fa-list-ol"></i> Elegir números
                    </button>
                </div>

                <div id="pane-random" class="srt-tab-pane active">
                    <div class="srt-form-group">
                        <label class="srt-label" for="quantity">¿Cuántos boletos quieres? <span>*</span></label>
                        <input class="srt-input" type="number" id="quantity" name="quantity"
                               min="1" max="20" value="{{ old('quantity', 1) }}">
                        <div class="srt-hint">Máximo 20 por compra.</div>
                    </div>
                </div>

                <div id="pane-pick" class="srt-tab-pane">
                    <div class="srt-form-group">
                        <label class="srt-label" for="ticket_numbers">Números de boleto <span>*</span></label>
                        <input class="srt-input" type="text" id="ticket_numbers" name="ticket_numbers"
                               placeholder="Ej: 100, 205, 310" value="{{ old('ticket_numbers') }}">
                        <div class="srt-hint">Separados por coma. Solo se asignarán los disponibles.</div>
                    </div>
                </div>

                <hr class="srt-divider">

                <div class="srt-form-group">
                    <label class="srt-label" for="buyer_name">Nombre completo <span>*</span></label>
                    <input class="srt-input" type="text" id="buyer_name" name="buyer_name"
                           value="{{ old('buyer_name') }}" required autocomplete="name" placeholder="Tu nombre completo">
                </div>

                <div class="srt-form-group">
                    <label class="srt-label" for="buyer_email">Correo electrónico <span>*</span></label>
                    <input class="srt-input" type="email" id="buyer_email" name="buyer_email"
                           value="{{ old('buyer_email') }}" required autocomplete="email" placeholder="correo@ejemplo.com">
                    <div class="srt-hint"><i class="fa fa-envelope-o"></i> Recibirás tus boletos digitales aquí.</div>
                </div>

                <div class="srt-form-group">
                    <label class="srt-label" for="buyer_phone">Teléfono <span>*</span></label>
                    <input class="srt-input" type="tel" id="buyer_phone" name="buyer_phone"
                           value="{{ old('buyer_phone') }}" required autocomplete="tel" placeholder="10 dígitos">
                </div>

                <div class="srt-form-row">
                    <div class="srt-form-group">
                        <label class="srt-label" for="buyer_city">Ciudad</label>
                        <input class="srt-input" type="text" id="buyer_city" name="buyer_city"
                               value="{{ old('buyer_city') }}" placeholder="Cd. Obregón">
                    </div>
                    <div class="srt-form-group">
                        <label class="srt-label" for="buyer_state">Estado</label>
                        <input class="srt-input" type="text" id="buyer_state" name="buyer_state"
                               value="{{ old('buyer_state') }}" placeholder="Sonora">
                    </div>
                </div>

                <button type="submit" class="srt-btn srt-btn--primary" style="margin-top:8px">
                    Continuar al pago &nbsp;<i class="fa fa-arrow-right"></i>
                </button>
            </form>

            <div class="srt-trust">
                <div class="srt-trust__item"><i class="fa fa-lock"></i> Pago seguro</div>
                <div class="srt-trust__item"><i class="fa fa-check-circle"></i> Sorteo oficial</div>
                <div class="srt-trust__item"><i class="fa fa-envelope"></i> Entrega inmediata</div>
            </div>
        </div>

        @if($sorteo->description)
        <div class="srt-card">
            <div class="srt-card__title"><i class="fa fa-info-circle"></i> Acerca del sorteo</div>
            <div style="font-size:14px; color:var(--c-muted); line-height:1.7">
                {!! nl2br(e($sorteo->description)) !!}
            </div>
        </div>
        @endif

    </div>
@endsection

@section('js')
<script>
function switchTab(mode, el) {
    document.querySelectorAll('.srt-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.srt-tab-pane').forEach(p => p.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('pane-' + mode).classList.add('active');
    var q  = document.getElementById('quantity');
    var tn = document.getElementById('ticket_numbers');
    if (mode === 'random') { q.disabled = false; tn.disabled = true;  tn.value = ''; }
    else                   { q.disabled = true;  tn.disabled = false; q.value  = ''; }
}
document.getElementById('ticket_numbers').disabled = true;
</script>
@endsection
