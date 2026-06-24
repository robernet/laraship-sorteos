@extends('layouts.crud.show')

@section('content_header')
    @component('components.content_header')
        @slot('page_title')
            {{ $title_singular }}
        @endslot
        @slot('breadcrumb')
            {{ Breadcrumbs::render('sorteos_sorteo_show') }}
        @endslot
    @endcomponent
@endsection

@section('content')

    @component('components.box')
        @slot('box_title')
            {{ $sorteo->name }}
            <span class="badge {{ $sorteo->status?->badgeClass() }} ml-2">{{ $sorteo->status?->label() }}</span>
            @if($sorteo->is_public)
                <span class="badge badge-info ml-1">Público</span>
            @else
                <span class="badge badge-default ml-1">Privado</span>
            @endif
        @endslot

        <div class="row">
            <div class="col-md-6">
                <table class="table table-striped">
                    <tr>
                        <th width="40%">{{ trans('Sorteos::attributes.sorteo.name') }}</th>
                        <td>{{ $sorteo->name }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.sorteo.slug') }}</th>
                        <td><code>{{ $sorteo->slug }}</code></td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.sorteo.ticket_price') }}</th>
                        <td>${{ number_format($sorteo->ticket_price, 2) }} MXN</td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.sorteo.tiraje') }}</th>
                        <td>{{ $sorteo->tiraje ? number_format($sorteo->tiraje) . ' boletos' : '—' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.sorteo.status') }}</th>
                        <td><span class="badge {{ $sorteo->status?->badgeClass() }}">{{ $sorteo->status?->label() }}</span></td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.sorteo.is_public') }}</th>
                        <td>
                            @if($sorteo->is_public)
                                <i class="fa fa-check text-success"></i> Sí
                            @else
                                <i class="fa fa-times text-danger"></i> No
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            <div class="col-md-6">
                <table class="table table-striped">
                    <tr>
                        <th width="45%">{{ trans('Sorteos::attributes.sorteo.starts_at') }}</th>
                        <td>{{ $sorteo->starts_at ? $sorteo->starts_at->format('d/m/Y') : '—' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.sorteo.ends_at') }}</th>
                        <td>{{ $sorteo->ends_at ? $sorteo->ends_at->format('d/m/Y') : '—' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.sorteo.draw_date') }}</th>
                        <td>
                            {{ $sorteo->draw_date ? $sorteo->draw_date->format('d/m/Y') : '—' }}
                            @if($sorteo->draw_date)
                                @php $days = (int) now()->diffInDays($sorteo->draw_date, false); @endphp
                                @if($days > 0)
                                    <span class="text-muted">(en {{ $days }} días)</span>
                                @elseif($days === 0)
                                    <span class="text-warning">¡Hoy!</span>
                                @else
                                    <span class="text-muted">(hace {{ abs($days) }} días)</span>
                                @endif
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Creado</th>
                        <td>{{ $sorteo->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Última actualización</th>
                        <td>{{ $sorteo->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        @if($sorteo->description)
        <div class="row mt-2">
            <div class="col-md-12">
                <strong>{{ trans('Sorteos::attributes.sorteo.description') }}</strong>
                <p class="mt-1 text-muted">{{ $sorteo->description }}</p>
            </div>
        </div>
        @endif

        @if($sorteo->prize_description)
        <div class="row mt-2">
            <div class="col-md-12">
                <strong>{{ trans('Sorteos::attributes.sorteo.prize_description') }}</strong>
                <p class="mt-1 text-muted">{{ $sorteo->prize_description }}</p>
            </div>
        </div>
        @endif

    @endcomponent

    @php
        $boletosVendidos = $sorteo->boletos()->where('status', 'sold')->count();
        $totalBoletos    = $sorteo->boletos()->count();
        $carterasTotal   = $sorteo->carteras()->count();
        $carterasAsig    = $sorteo->carteras()->whereIn('status', ['asignado', 'entregado'])->count();
        $ordersConfirmed = $sorteo->orders()->where('status', 'confirmed')->count();
        $totalRevenue    = $sorteo->orders()->where('status', 'confirmed')->sum('total_amount');
        $tiraje          = $sorteo->tiraje ?: $totalBoletos;
        $pct             = $tiraje > 0 ? round($boletosVendidos / $tiraje * 100, 1) : 0;
    @endphp

    @component('components.box')
        @slot('box_title') Estadísticas @endslot

        <div class="row text-center">
            <div class="col-md-3 col-sm-6">
                <div style="padding: 16px 0;">
                    <div style="font-size: 28px; font-weight: 700; color: #00a65a;">{{ number_format($boletosVendidos) }}</div>
                    <div style="font-size: 13px; color: #666; margin-top: 4px;">Boletos Vendidos</div>
                    <div style="font-size: 12px; color: #aaa;">de {{ number_format($tiraje) }} ({{ $pct }}%)</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div style="padding: 16px 0;">
                    <div style="font-size: 28px; font-weight: 700; color: #0073b7;">${{ number_format($totalRevenue, 0) }}</div>
                    <div style="font-size: 13px; color: #666; margin-top: 4px;">Ingresos Confirmados</div>
                    <div style="font-size: 12px; color: #aaa;">MXN</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div style="padding: 16px 0;">
                    <div style="font-size: 28px; font-weight: 700; color: #605ca8;">{{ number_format($carterasTotal) }}</div>
                    <div style="font-size: 13px; color: #666; margin-top: 4px;">Carteras</div>
                    <div style="font-size: 12px; color: #aaa;">{{ $carterasAsig }} asignadas/entregadas</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div style="padding: 16px 0;">
                    <div style="font-size: 28px; font-weight: 700; color: #f39c12;">{{ number_format($ordersConfirmed) }}</div>
                    <div style="font-size: 13px; color: #666; margin-top: 4px;">Órdenes Confirmadas</div>
                </div>
            </div>
        </div>

        @if($tiraje > 0)
        <div class="row mt-2">
            <div class="col-md-12">
                <div style="font-size: 13px; margin-bottom: 4px; display: flex; justify-content: space-between;">
                    <span>Progreso de ventas</span>
                    <strong>{{ $pct }}%</strong>
                </div>
                <div class="progress" style="height: 18px; border-radius: 4px;">
                    <div class="progress-bar bg-success progress-bar-striped"
                         style="width: {{ $pct }}%; min-width: {{ $pct > 0 ? '2em' : '0' }}; font-size: 13px; line-height: 18px;">
                        {{ $pct > 5 ? $pct . '%' : '' }}
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endcomponent

    @if($sorteo->cover_image)
    @component('components.box')
        @slot('box_title') Imagen de Portada @endslot
        <img src="{{ $sorteo->cover_image }}" alt="Portada" style="max-width: 400px; border-radius: 6px;">
    @endcomponent
    @endif

    @component('components.box')
        @slot('box_title') Acciones @endslot
        <div class="d-flex flex-wrap" style="gap: 8px;">
            <a href="{{ url(config('sorteos.models.sorteo.resource_url') . '/' . $sorteo->hashed_id . '/edit') }}"
               class="btn btn-primary">
                <i class="fa fa-edit"></i> Editar
            </a>
            <a href="{{ url('sorteos/dashboard?sorteo_id=' . $sorteo->id) }}" class="btn btn-info">
                <i class="fa fa-bar-chart"></i> Ver Dashboard
            </a>
            <a href="{{ url('sorteos/orders?sorteo_id=' . $sorteo->id) }}" class="btn btn-default">
                <i class="fa fa-list"></i> Ver Órdenes
            </a>
            <a href="{{ url('sorteos/carteras?sorteo_id=' . $sorteo->id) }}" class="btn btn-default">
                <i class="fa fa-folder-open"></i> Ver Carteras
            </a>
        </div>
    @endcomponent

@endsection
