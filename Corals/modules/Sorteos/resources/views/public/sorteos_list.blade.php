@extends('layouts.master')

@section('title', 'Sorteos ITSON')

@section('content')
<div class="srt-container">

    <div class="srt-hero" style="text-align:center; padding:40px 16px 24px">
        <h1 style="margin:0 0 8px">Sorteos ITSON</h1>
        <p style="margin:0; color:var(--c-muted)">Selecciona un sorteo para participar</p>
    </div>

    @if($sorteos->isEmpty())
        <div class="srt-alert srt-alert--warning">
            <i class="fa fa-info-circle"></i>
            <span>No hay sorteos activos en este momento. ¡Vuelve pronto!</span>
        </div>
    @else
        <div style="display:grid; gap:20px; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr))">
            @foreach($sorteos as $sorteo)
                <div class="srt-card" style="padding:0; overflow:hidden">
                    @if($sorteo->cover_image)
                        <img src="{{ asset('storage/' . ltrim($sorteo->cover_image, '/')) }}"
                             alt="{{ $sorteo->name }}"
                             style="width:100%; height:180px; object-fit:cover; display:block">
                    @else
                        <div style="width:100%; height:180px; background:var(--c-primary); display:flex; align-items:center; justify-content:center">
                            <i class="fa fa-ticket" style="font-size:48px; color:rgba(255,255,255,.3)"></i>
                        </div>
                    @endif

                    <div style="padding:16px 18px 18px">
                        <div style="font-size:18px; font-weight:700; color:var(--c-text); margin-bottom:8px">
                            {{ $sorteo->name }}
                        </div>

                        <div class="srt-info-row" style="margin-bottom:4px">
                            <span class="srt-info-row__label">Precio</span>
                            <span class="srt-info-row__value">${{ number_format($sorteo->ticket_price, 0) }} MXN</span>
                        </div>

                        @if($sorteo->draw_date)
                        <div class="srt-info-row" style="margin-bottom:12px">
                            <span class="srt-info-row__label">Sorteo</span>
                            <span class="srt-info-row__value">{{ $sorteo->draw_date->format('d/m/Y') }}</span>
                        </div>
                        @endif

                        <a href="{{ route('sorteos.public.show', $sorteo->slug) }}"
                           class="srt-btn srt-btn--primary"
                           style="display:block; text-align:center">
                            Comprar boletos &nbsp;<i class="fa fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
