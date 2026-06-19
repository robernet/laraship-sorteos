@extends('layouts.master')

@section('content_header')
    @component('components.content_header')
        @slot('page_title') Ventas por Período @endslot
        @slot('breadcrumb') {{ Breadcrumbs::render('sorteos_reports_sales') }} @endslot
    @endcomponent
@endsection

@section('content')
    {{-- Filters --}}
    <form method="GET" class="box box-default">
        <div class="box-body">
            <div class="row">
                <div class="col-md-2">
                    <label>Desde</label>
                    <input type="date" name="from" class="form-control" value="{{ $from->toDateString() }}">
                </div>
                <div class="col-md-2">
                    <label>Hasta</label>
                    <input type="date" name="to" class="form-control" value="{{ $to->toDateString() }}">
                </div>
                <div class="col-md-2">
                    <label>Agrupar por</label>
                    <select name="period" class="form-control">
                        <option value="day"   @selected($period === 'day')>Día</option>
                        <option value="week"  @selected($period === 'week')>Semana</option>
                        <option value="month" @selected($period === 'month')>Mes</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Sorteo</label>
                    <select name="sorteo_id" class="form-control">
                        <option value="">— Todos —</option>
                        @foreach($sorteos as $id => $name)
                            <option value="{{ $id }}" @selected($sorteoId == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3" style="padding-top:25px">
                    <button class="btn btn-primary"><i class="fa fa-search"></i> Filtrar</button>
                    <a href="?{{ http_build_query(array_merge(request()->all(), ['export' => 'csv'])) }}" class="btn btn-default">
                        <i class="fa fa-download"></i> CSV
                    </a>
                    <a href="?{{ http_build_query(array_merge(request()->all(), ['export' => 'pdf'])) }}" class="btn btn-danger">
                        <i class="fa fa-file-pdf-o"></i> PDF
                    </a>
                    <a href="?{{ http_build_query(array_merge(request()->all(), ['export' => 'xlsx'])) }}" class="btn btn-success">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </a>
                </div>
            </div>
        </div>
    </form>

    {{-- KPI strip --}}
    <div class="row">
        @foreach([
            ['Órdenes', $data['totals']['orders'], 'bg-blue'],
            ['Ingresos', '$' . number_format($data['totals']['revenue'], 2), 'bg-green'],
            ['Boletos', $data['totals']['tickets'], 'bg-purple'],
            ['Promedio por Orden', '$' . number_format($data['totals']['avg_order'], 2), 'bg-aqua'],
        ] as [$label, $value, $bg])
        <div class="col-md-3">
            <div class="info-box"><span class="info-box-icon {{ $bg }}"><i class="fa fa-bar-chart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ $label }}</span>
                    <span class="info-box-number">{{ $value }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if(count($data['labels']))
    {{-- Chart --}}
    <div class="box box-default">
        <div class="box-header"><h3 class="box-title">Ingresos y Órdenes</h3></div>
        <div class="box-body">
            <canvas id="salesChart" height="80"></canvas>
        </div>
    </div>

    {{-- Table --}}
    <div class="box box-default">
        <div class="box-header"><h3 class="box-title">Detalle por Período</h3></div>
        <div class="box-body table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr><th>Período</th><th>Órdenes</th><th>Ingresos</th><th>Boletos</th></tr>
                </thead>
                <tbody>
                    @foreach($data['labels'] as $i => $label)
                    <tr>
                        <td>{{ $label }}</td>
                        <td>{{ $data['orders'][$i] }}</td>
                        <td>${{ number_format($data['revenue'][$i], 2) }}</td>
                        <td>{{ $data['tickets'][$i] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
        <div class="alert alert-info">No hay datos para el período seleccionado.</div>
    @endif
@endsection

@push('js')
<script src="{{ asset('assets/corals/plugins/chartjs/Chart.min.js') }}"></script>
<script>
new Chart(document.getElementById('salesChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($data['labels']) !!},
        datasets: [
            {
                label: 'Ingresos (MXN)',
                data: {!! json_encode($data['revenue']) !!},
                backgroundColor: 'rgba(26, 58, 110, 0.7)',
                yAxisID: 'y'
            },
            {
                label: 'Órdenes',
                data: {!! json_encode($data['orders']) !!},
                type: 'line',
                borderColor: '#27ae60',
                backgroundColor: 'rgba(39,174,96,0.1)',
                fill: true,
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            y:  { type: 'linear', position: 'left',  title: { display: true, text: 'Ingresos (MXN)' } },
            y1: { type: 'linear', position: 'right', title: { display: true, text: 'Órdenes' }, grid: { drawOnChartArea: false } }
        }
    }
});
</script>
@endpush
