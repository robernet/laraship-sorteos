@extends('layouts.master')

@section('css')
    <style>
        .kpi-box { border-radius: 6px; padding: 18px 20px; color: #fff; margin-bottom: 16px; position: relative; overflow: hidden; }
        .kpi-box .kpi-value { font-size: 2rem; font-weight: 700; line-height: 1; }
        .kpi-box .kpi-label { font-size: 0.82rem; opacity: .85; margin-top: 4px; }
        .kpi-box .kpi-icon  { font-size: 2.4rem; opacity: .3; position: absolute; right: 18px; top: 14px; }
        .kpi-green  { background: #00a65a; }
        .kpi-blue   { background: #0073b7; }
        .kpi-purple { background: #605ca8; }
        .kpi-orange { background: #f39c12; }
        .kpi-red    { background: #dd4b39; }
        .kpi-teal   { background: #00acd6; }
        .chart-box  { background: #fff; border: 1px solid #e0e0e0; border-radius: 6px; padding: 16px; margin-bottom: 20px; }
        .chart-box h4 { font-size: 0.95rem; font-weight: 600; color: #444; margin-bottom: 14px; border-bottom: 1px solid #f0f0f0; padding-bottom: 8px; }
        .stat-mini { text-align: center; padding: 10px 0; }
        .stat-mini .n { font-size: 1.6rem; font-weight: 700; }
        .stat-mini .l { font-size: 0.75rem; color: #888; }
        .colabs-table th { font-size: 0.82rem; color: #777; font-weight: 600; }
        .colabs-table td { font-size: 0.9rem; }
        .progress-bar-label { font-size: 0.78rem; margin-bottom: 2px; }
    </style>
@endsection

@section('content_header')
    @component('components.content_header')
        @slot('page_title') Dashboard del Sorteo @endslot
        @slot('breadcrumb') {{ Breadcrumbs::render('sorteos_dashboard') }} @endslot
    @endcomponent
@endsection

@section('content')
    {{-- Selector de sorteo --}}
    <form method="GET" class="mb-4">
        <div class="row align-items-center">
            <div class="col-md-4">
                <select name="sorteo_id" class="form-control" onchange="this.form.submit()">
                    @foreach($sorteos as $id => $name)
                        <option value="{{ $id }}" @selected($sorteoId == $id || (!$sorteoId && $loop->first))>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            @if($sorteo)
            <div class="col-md-8 d-flex align-items-center flex-wrap gap-2">
                <span class="badge badge-{{ $sorteo->status?->badgeClass() ?? 'secondary' }} mr-2" style="font-size:.85rem;padding:5px 10px;">
                    {{ $sorteo->status?->label() }}
                </span>
                @if($sorteo->draw_date)
                    <span class="text-muted" style="font-size:.85rem;">
                        <i class="fa fa-calendar mr-1"></i>Fecha del sorteo: <strong>{{ $sorteo->draw_date->format('d/m/Y') }}</strong>
                    </span>
                @endif
            </div>
            @endif
        </div>
    </form>

    @if(!$sorteo)
        <div class="alert alert-info">No hay sorteos registrados.</div>
    @else

    @php
        $d               = $data;
        $tiraje          = $d['tiraje']           ?? 0;
        $pctVendido      = $d['pctVendido']       ?? 0;
        $daysLeft        = $d['daysLeft']          ?? null;
        $confirmedCount  = $d['confirmedCount']    ?? 0;
        $pendingCount    = $d['pendingCount']      ?? 0;
        $totalRevenue    = $d['totalRevenue']      ?? 0;
        $uniqueBuyers    = $d['uniqueBuyers']      ?? 0;
        $avgOrder        = $d['avgOrder']          ?? 0;
        $boletosVendidos    = $d['boletosVendidos']    ?? 0;
        $boletosReservados  = $d['boletosReservados']  ?? 0;
        $boletosDisponibles = $d['boletosDisponibles'] ?? 0;
        $carterasTotal      = $d['carterasTotal']      ?? 0;
        $carterasByStatus   = $d['carterasByStatus']   ?? [];
        $alertas            = $d['alertas']            ?? [];
        $topColaboradores   = $d['topColaboradores']   ?? collect([]);

        $statusLabels = ['available'=>'Disponible','partial'=>'Parcial','sold'=>'Vendida','asignado'=>'Asignada','entregado'=>'Entregada'];
        $statusColors = ['available'=>'#00a65a','partial'=>'#f39c12','sold'=>'#dd4b39','asignado'=>'#0073b7','entregado'=>'#605ca8'];
    @endphp

    {{-- Alertas --}}
    @foreach($alertas as $alerta)
    <div class="alert alert-{{ $alerta['type'] }} py-2 mb-2">
        <i class="fa {{ $alerta['icon'] }} mr-2"></i> {{ $alerta['msg'] }}
    </div>
    @endforeach

    {{-- KPI Cards --}}
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="kpi-box kpi-green">
                <i class="fa fa-ticket kpi-icon"></i>
                <div class="kpi-value">{{ number_format($boletosVendidos) }}</div>
                <div class="kpi-label">Boletos Vendidos de {{ number_format($tiraje) }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="kpi-box kpi-blue">
                <i class="fa fa-dollar kpi-icon"></i>
                <div class="kpi-value">${{ number_format($totalRevenue, 0) }}</div>
                <div class="kpi-label">Ingresos Confirmados (MXN)</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="kpi-box kpi-purple">
                <i class="fa fa-users kpi-icon"></i>
                <div class="kpi-value">{{ number_format($uniqueBuyers) }}</div>
                <div class="kpi-label">Compradores Únicos</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            @php
                $kpiColor = ($daysLeft !== null && $daysLeft >= 0 && $daysLeft <= 7) ? 'kpi-red'
                    : (($daysLeft !== null && $daysLeft < 0) ? 'kpi-teal' : 'kpi-orange');
            @endphp
            <div class="kpi-box {{ $kpiColor }}">
                <i class="fa fa-calendar kpi-icon"></i>
                @if($daysLeft === null)
                    <div class="kpi-value">—</div>
                    <div class="kpi-label">Sin fecha de sorteo</div>
                @elseif($daysLeft < 0)
                    <div class="kpi-value">Realizado</div>
                    <div class="kpi-label">Hace {{ abs($daysLeft) }} días</div>
                @else
                    <div class="kpi-value">{{ $daysLeft }}</div>
                    <div class="kpi-label">Días para el Sorteo</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Progreso de ventas --}}
    <div class="chart-box">
        <h4><i class="fa fa-bar-chart mr-1"></i> Progreso de Ventas</h4>
        <div class="row align-items-center">
            <div class="col-md-9">
                <div class="progress-bar-label d-flex justify-content-between">
                    <span><strong>{{ $boletosVendidos }}</strong> vendidos</span>
                    <span><strong>{{ $pctVendido }}%</strong></span>
                </div>
                <div class="progress" style="height:22px;border-radius:4px;">
                    <div class="progress-bar bg-success progress-bar-striped"
                         style="width:{{ $pctVendido }}%;min-width:{{ $pctVendido > 0 ? '2em' : '0' }};">
                        {{ $pctVendido > 5 ? $pctVendido . '%' : '' }}
                    </div>
                </div>
                @if($boletosReservados > 0)
                @php $pctRes = $tiraje > 0 ? round($boletosReservados/$tiraje*100,1) : 0; @endphp
                <div class="progress mt-1" style="height:8px;border-radius:4px;">
                    <div class="progress-bar bg-warning" style="width:{{ $pctRes }}%"></div>
                </div>
                <small class="text-muted">{{ $boletosReservados }} reservados ({{ $pctRes }}%)</small>
                @endif
            </div>
            <div class="col-md-3">
                <div class="row text-center">
                    <div class="col-4 stat-mini">
                        <div class="n text-success">{{ number_format($boletosVendidos) }}</div>
                        <div class="l">Vendidos</div>
                    </div>
                    <div class="col-4 stat-mini">
                        <div class="n text-warning">{{ number_format($boletosReservados) }}</div>
                        <div class="l">Reservados</div>
                    </div>
                    <div class="col-4 stat-mini">
                        <div class="n text-muted">{{ number_format($boletosDisponibles) }}</div>
                        <div class="l">Disponibles</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Doughnut boletos + Line diaria --}}
    <div class="row">
        <div class="col-md-4">
            <div class="chart-box">
                <h4><i class="fa fa-pie-chart mr-1"></i> Estado de Boletos</h4>
                <canvas id="chartBoletos" height="200"></canvas>
            </div>
        </div>
        <div class="col-md-8">
            <div class="chart-box">
                <h4><i class="fa fa-line-chart mr-1"></i> Ventas Diarias (últimos 30 días)</h4>
                <canvas id="chartDailyOrders" height="160"></canvas>
            </div>
        </div>
    </div>

    {{-- Geográfico + Pagos --}}
    <div class="row">
        <div class="col-md-7">
            <div class="chart-box">
                <h4><i class="fa fa-map-marker mr-1"></i> Distribución Geográfica (top ciudades)</h4>
                @if(count($d['ciudades']) > 0)
                    <canvas id="chartCiudades" height="170"></canvas>
                @else
                    <p class="text-muted text-center py-4">Sin datos geográficos aún.</p>
                @endif
            </div>
        </div>
        <div class="col-md-5">
            <div class="chart-box">
                <h4><i class="fa fa-credit-card mr-1"></i> Métodos de Pago</h4>
                @if(count($d['paymentMethods']) > 0)
                    <canvas id="chartPayments" height="170"></canvas>
                @else
                    <p class="text-muted text-center py-4">Sin órdenes confirmadas aún.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Carteras + Colaboradores --}}
    <div class="row">
        <div class="col-md-5">
            <div class="chart-box">
                <h4><i class="fa fa-folder-open mr-1"></i> Estado de Carteras</h4>
                <canvas id="chartCarteras" height="180"></canvas>
                <div class="row mt-2 text-center">
                    @foreach($carterasByStatus as $st => $n)
                    <div class="col stat-mini">
                        <div class="n" style="color:{{ $statusColors[$st] ?? '#888' }}">{{ $n }}</div>
                        <div class="l">{{ $statusLabels[$st] ?? $st }}</div>
                    </div>
                    @endforeach
                    <div class="col stat-mini">
                        <div class="n text-dark">{{ $carterasTotal }}</div>
                        <div class="l">Total</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="chart-box">
                <h4><i class="fa fa-user mr-1"></i> Top Colaboradores</h4>
                @if($topColaboradores->count() > 0)
                <table class="table table-sm colabs-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th><th>Colaborador</th>
                            <th class="text-center">Órdenes</th>
                            <th class="text-right">Ingresos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topColaboradores as $i => $c)
                        <tr>
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td>{{ $c->name }}</td>
                            <td class="text-center">{{ $c->orders }}</td>
                            <td class="text-right">${{ number_format($c->revenue, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                    <p class="text-muted text-center py-4">Sin datos de colaboradores aún.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Mini stats de órdenes --}}
    <div class="chart-box">
        <div class="row text-center">
            <div class="col-md-3 col-6 stat-mini border-right">
                <div class="n text-success">{{ number_format($confirmedCount) }}</div>
                <div class="l">Órdenes Confirmadas</div>
            </div>
            <div class="col-md-3 col-6 stat-mini border-right">
                <div class="n text-warning">{{ number_format($pendingCount) }}</div>
                <div class="l">Órdenes Pendientes</div>
            </div>
            <div class="col-md-3 col-6 stat-mini border-right">
                <div class="n text-primary">${{ number_format($avgOrder, 2) }}</div>
                <div class="l">Ticket Promedio</div>
            </div>
            <div class="col-md-3 col-6 stat-mini">
                <div class="n text-info">{{ number_format($carterasTotal) }}</div>
                <div class="l">Carteras Totales</div>
            </div>
        </div>
    </div>

    @endif
@endsection

@section('js')
<script src="{{ asset('assets/corals/plugins/chartjs/Chart.min.js') }}"></script>
@if($sorteo && count($data))
<script>
Chart.defaults.global.defaultFontFamily = "'Source Sans Pro', sans-serif";
Chart.defaults.global.defaultFontSize   = 12;

// Doughnut: Boletos
new Chart(document.getElementById('chartBoletos').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: ['Vendidos', 'Reservados', 'Disponibles'],
        datasets: [{
            data: [{{ $data['boletosVendidos'] }}, {{ $data['boletosReservados'] }}, {{ $data['boletosDisponibles'] }}],
            backgroundColor: ['#00a65a', '#f39c12', '#d2d6de'],
            borderWidth: 2,
        }]
    },
    options: {
        legend: { position: 'bottom', labels: { boxWidth: 12 } },
        cutoutPercentage: 60,
        tooltips: {
            callbacks: {
                label: function(t, d) {
                    var val = d.datasets[t.datasetIndex].data[t.index];
                    var sum = d.datasets[t.datasetIndex].data.reduce(function(a,b){return a+b;}, 0);
                    return d.labels[t.index] + ': ' + val + (sum > 0 ? ' (' + (val/sum*100).toFixed(1) + '%)' : '');
                }
            }
        }
    }
});

// Line: Ventas diarias
new Chart(document.getElementById('chartDailyOrders').getContext('2d'), {
    type: 'line',
    data: {
        labels: {!! json_encode($data['dailyLabels']) !!},
        datasets: [{
            label: 'Órdenes',
            data: {!! json_encode($data['dailyOrders']) !!},
            borderColor: '#0073b7',
            backgroundColor: 'rgba(0,115,183,0.08)',
            fill: true, borderWidth: 2, pointRadius: 3,
            yAxisID: 'y',
        }, {
            label: 'Ingresos ($)',
            data: {!! json_encode($data['dailyRevenue']) !!},
            borderColor: '#00a65a',
            backgroundColor: 'rgba(0,166,90,0)',
            fill: false, borderWidth: 2, borderDash: [5,3], pointRadius: 3,
            yAxisID: 'y2',
        }]
    },
    options: {
        scales: {
            yAxes: [
                { id: 'y',  position: 'left',  ticks: { beginAtZero: true, stepSize: 1 } },
                { id: 'y2', position: 'right', ticks: { beginAtZero: true, callback: function(v){ return '$'+v; } } }
            ],
            xAxes: [{ ticks: { maxRotation: 45 } }]
        },
        legend: { position: 'bottom', labels: { boxWidth: 12 } }
    }
});

@if(count($data['ciudades']) > 0)
// Horizontal Bar: Ciudades
new Chart(document.getElementById('chartCiudades').getContext('2d'), {
    type: 'horizontalBar',
    data: {
        labels: {!! json_encode(array_keys($data['ciudades'])) !!},
        datasets: [{
            label: 'Órdenes',
            data: {!! json_encode(array_values($data['ciudades'])) !!},
            backgroundColor: 'rgba(0,115,183,0.7)',
            borderColor: '#0073b7', borderWidth: 1,
        }]
    },
    options: {
        legend: { display: false },
        scales: { xAxes: [{ ticks: { beginAtZero: true, stepSize: 1 } }] }
    }
});
@endif

@if(count($data['paymentMethods']) > 0)
// Doughnut: Métodos de pago
new Chart(document.getElementById('chartPayments').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($data['paymentLabels']) !!},
        datasets: [{
            data: {!! json_encode(array_values($data['paymentMethods'])) !!},
            backgroundColor: ['#00a65a','#0073b7','#605ca8','#f39c12'],
            borderWidth: 2,
        }]
    },
    options: {
        legend: { position: 'bottom', labels: { boxWidth: 12 } },
        cutoutPercentage: 55,
    }
});
@endif

// Bar: Carteras
@php
    $cLabels = []; $cValues = []; $cColors = [];
    $sLabels = ['available'=>'Disponible','partial'=>'Parcial','sold'=>'Vendida','asignado'=>'Asignada','entregado'=>'Entregada'];
    $sColors = ['available'=>'#00a65a','partial'=>'#f39c12','sold'=>'#dd4b39','asignado'=>'#0073b7','entregado'=>'#605ca8'];
    foreach ($data['carterasByStatus'] as $st => $n) {
        $cLabels[] = $sLabels[$st] ?? $st;
        $cValues[] = $n;
        $cColors[] = $sColors[$st] ?? '#888';
    }
@endphp
new Chart(document.getElementById('chartCarteras').getContext('2d'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($cLabels) !!},
        datasets: [{
            label: 'Carteras',
            data: {!! json_encode($cValues) !!},
            backgroundColor: {!! json_encode($cColors) !!},
            borderWidth: 0,
        }]
    },
    options: {
        legend: { display: false },
        scales: { yAxes: [{ ticks: { beginAtZero: true, stepSize: 1 } }] }
    }
});
</script>
@endif
@endsection
