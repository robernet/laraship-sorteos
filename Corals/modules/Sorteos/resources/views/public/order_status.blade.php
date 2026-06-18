@extends('layouts.master')

@section('title', 'Estado de tu orden — Sorteos ITSON')

@section('content')
<div class="srt-container" style="max-width:560px">

    @if(!$order)
        <div class="srt-alert srt-alert--warning">
            <i class="fa fa-exclamation-triangle"></i>
            <span>No se encontró la orden solicitada.</span>
        </div>
    @else
        @php
            $statusVal = is_object($order->status) ? $order->status->value : $order->status;
            [$statusLabel, $statusClass, $statusIcon] = match($statusVal) {
                'confirmed' => ['Pago confirmado',  'confirmed', 'fa-check-circle'],
                'cancelled' => ['Cancelada',         'cancelled', 'fa-times-circle'],
                default     => ['Pendiente de pago', 'pending',   'fa-clock-o'],
            };
        @endphp

        <div class="srt-card" style="text-align:center; padding:28px 22px 22px">
            <div style="margin-bottom:12px">
                <span class="srt-badge srt-badge--{{ $statusClass }}">
                    <i class="fa {{ $statusIcon }}"></i> {{ $statusLabel }}
                </span>
            </div>
            <div style="font-size:13px; color:var(--c-muted)">
                Orden <strong style="color:var(--c-text)">#{{ $order->id }}</strong>
                &nbsp;&bull;&nbsp;
                {{ $order->sorteo?->name }}
            </div>
        </div>

        @if($order->isConfirmed())
            <div class="srt-alert srt-alert--success">
                <i class="fa fa-envelope"></i>
                <span>Tus boletos digitales fueron enviados a <strong>{{ $order->buyer_email }}</strong>. Revisa tu bandeja de entrada.</span>
            </div>
        @elseif($order->isPending())
            <div class="srt-alert srt-alert--warning">
                <i class="fa fa-clock-o"></i>
                <span>Tu pago está siendo procesado. Recibirás un correo de confirmación en breve.</span>
            </div>
        @endif

        <div class="srt-card">
            <div class="srt-card__title"><i class="fa fa-file-text-o"></i> Resumen de orden</div>
            <div class="srt-info-row">
                <span class="srt-info-row__label">Comprador</span>
                <span class="srt-info-row__value">{{ $order->buyer_name }}</span>
            </div>
            <div class="srt-info-row">
                <span class="srt-info-row__label">Correo</span>
                <span class="srt-info-row__value">{{ $order->buyer_email }}</span>
            </div>
            <div class="srt-info-row">
                <span class="srt-info-row__label">Sorteo</span>
                <span class="srt-info-row__value">{{ $order->sorteo?->name ?? '—' }}</span>
            </div>
            <div class="srt-info-row">
                <span class="srt-info-row__label">Total</span>
                <span class="srt-info-row__value srt-info-row__value--total">
                    ${{ number_format($order->total_amount, 2) }} MXN
                </span>
            </div>
        </div>

        @if($order->items->isNotEmpty())
        <div class="srt-card">
            <div class="srt-card__title"><i class="fa fa-ticket"></i> Tus boletos</div>
            <table class="srt-table">
                <thead>
                    <tr><th>#</th><th>Núm. digital</th><th>Núm. físico</th></tr>
                </thead>
                <tbody>
                    @foreach($order->items as $i => $item)
                        @if($item->boleto)
                        <tr>
                            <td style="color:var(--c-muted)">{{ $i + 1 }}</td>
                            <td><span class="srt-ticket-num">{{ str_pad($item->boleto->digital_number, 5, '0', STR_PAD_LEFT) }}</span></td>
                            <td>{{ $item->boleto->physical_number }}</td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <div style="display:flex; gap:10px; flex-wrap:wrap">
            <a href="{{ route('sorteos.boletos.resend-form') }}" class="srt-btn srt-btn--ghost srt-btn--sm" style="flex:1">
                <i class="fa fa-refresh"></i> Reenviar mis boletos
            </a>
            @if($order->sorteo?->slug)
            <a href="{{ route('sorteos.public.show', $order->sorteo->slug) }}" class="srt-btn srt-btn--ghost srt-btn--sm" style="flex:1">
                <i class="fa fa-plus"></i> Comprar más boletos
            </a>
            @endif
        </div>

    @endif
</div>
@endsection
