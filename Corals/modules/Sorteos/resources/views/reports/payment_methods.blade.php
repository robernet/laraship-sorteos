@extends('layouts.master')

@section('content_header')
    @component('components.content_header')
        @slot('page_title') Métodos de Pago @endslot
        @slot('breadcrumb') {{ Breadcrumbs::render('sorteos_reports_payments') }} @endslot
    @endcomponent
@endsection

@section('content')
    <form method="GET" class="box box-default">
        <div class="box-body">
            <div class="row">
                <div class="col-md-4">
                    <label>Sorteo</label>
                    <select name="sorteo_id" class="form-control" onchange="this.form.submit()">
                        <option value="">— Todos —</option>
                        @foreach($sorteos as $id => $name)
                            <option value="{{ $id }}" @selected($sorteoId == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3" style="padding-top:25px">
                    <a href="?{{ http_build_query(array_merge(request()->all(), ['export' => 'csv'])) }}" class="btn btn-default">
                        <i class="fa fa-download"></i> CSV
                    </a>
                    <a href="?{{ http_build_query(array_merge(request()->all(), ['export' => 'xlsx'])) }}" class="btn btn-success">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </a>
                </div>
            </div>
        </div>
    </form>

    @php $total = $data->sum('revenue'); @endphp

    <div class="row">
        <div class="col-md-5">
            <div class="box box-default">
                <div class="box-header"><h3 class="box-title">Distribución</h3></div>
                <div class="box-body"><canvas id="methodChart" height="200"></canvas></div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="box box-default">
                <div class="box-header"><h3 class="box-title">Detalle</h3></div>
                <div class="box-body table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr><th>Método</th><th class="text-center">Órdenes</th><th class="text-center">Boletos</th><th class="text-right">Ingresos</th><th class="text-right">% del Total</th></tr>
                        </thead>
                        <tbody>
                            @forelse($data as $row)
                            <tr>
                                <td>{{ \Corals\Modules\Sorteos\Enums\PaymentMethod::tryFrom($row->payment_method)?->label() ?? $row->payment_method }}</td>
                                <td class="text-center">{{ $row->count }}</td>
                                <td class="text-center">{{ (int) $row->boletos_vendidos }}</td>
                                <td class="text-right">${{ number_format($row->revenue, 2) }}</td>
                                <td class="text-right">
                                    {{ $total > 0 ? number_format($row->revenue / $total * 100, 1) : 0 }}%
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted">Sin datos.</td></tr>
                            @endforelse
                        </tbody>
                        @if($data->isNotEmpty())
                        <tfoot>
                            <tr class="active">
                                <th>Total</th>
                                <th class="text-center">{{ $data->sum('count') }}</th>
                                <th class="text-center">{{ (int) $data->sum('boletos_vendidos') }}</th>
                                <th class="text-right">${{ number_format($total, 2) }}</th>
                                <th class="text-right">100%</th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script src="{{ asset('assets/corals/plugins/chartjs/Chart.min.js') }}"></script>
<script>
new Chart(document.getElementById('methodChart'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($data->map(fn($r) => \Corals\Modules\Sorteos\Enums\PaymentMethod::tryFrom($r->payment_method)?->label() ?? $r->payment_method)->values()) !!},
        datasets: [{ data: {!! json_encode($data->pluck('revenue')->values()) !!}, backgroundColor: ['#1a3a6e','#27ae60','#e67e22','#e74c3c'] }]
    },
    options: { responsive: true, legend: { position: 'bottom' } }
});
</script>
@endpush
