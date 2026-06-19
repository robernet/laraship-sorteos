@extends('layouts.master')

@section('content_header')
    @component('components.content_header')
        @slot('page_title') {{ $title_singular }} @endslot
        @slot('breadcrumb') {{ Breadcrumbs::render('sorteos_reports') }} @endslot
    @endcomponent
@endsection

@section('content')
    {{-- Sorteo filter --}}
    <form method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <select name="sorteo_id" class="form-control" onchange="this.form.submit()">
                    <option value="">— Todos los sorteos —</option>
                    @foreach($sorteos as $id => $name)
                        <option value="{{ $id }}" @selected($sorteoId == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    {{-- KPI cards --}}
    <div class="row">
        @php
            $cards = [
                ['Órdenes Confirmadas', $kpis['confirmed_orders'],  'fa-check-circle',  'bg-green'],
                ['Ingresos Totales',    '$' . number_format($kpis['total_revenue'], 2), 'fa-dollar', 'bg-blue'],
                ['Boletos Vendidos',    $kpis['tickets_sold'],      'fa-ticket',        'bg-purple'],
                ['Compradores Únicos',  $kpis['unique_buyers'],     'fa-users',         'bg-yellow'],
                ['Órdenes Pendientes',  $kpis['pending_orders'],    'fa-clock-o',       'bg-orange'],
                ['Órdenes Canceladas',  $kpis['cancelled_orders'],  'fa-times-circle',  'bg-red'],
                ['Valor Promedio',      '$' . number_format($kpis['avg_order_value'], 2), 'fa-bar-chart', 'bg-aqua'],
            ];
        @endphp

        @foreach($cards as [$label, $value, $icon, $bg])
        <div class="col-md-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon {{ $bg }}"><i class="fa {{ $icon }}"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ $label }}</span>
                    <span class="info-box-number">{{ $value }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Quick links --}}
    <div class="row mt-2">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header"><h3 class="box-title">Reportes Disponibles</h3></div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="{{ url('sorteos/reports/sales') }}" class="btn btn-block btn-primary btn-lg">
                                <i class="fa fa-line-chart"></i> Ventas por Período
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ url('sorteos/reports/buyers') }}" class="btn btn-block btn-info btn-lg">
                                <i class="fa fa-users"></i> Historial de Compradores
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ url('sorteos/reports/payment-methods') }}" class="btn btn-block btn-success btn-lg">
                                <i class="fa fa-credit-card"></i> Métodos de Pago
                            </a>
                        </div>
                        <div class="col-md-4 mt-2">
                            <a href="{{ url('sorteos/reports/geographic') }}" class="btn btn-block btn-warning btn-lg">
                                <i class="fa fa-map-marker"></i> Geográfico
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
