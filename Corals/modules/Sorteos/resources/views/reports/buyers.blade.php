@extends('layouts.crud.index')

@section('content_header')
    @component('components.content_header')
        @slot('page_title') Historial de Compradores @endslot
        @slot('breadcrumb') {{ Breadcrumbs::render('sorteos_reports_buyers') }} @endslot
    @endcomponent
@endsection

@section('content')
    <form method="GET" class="box box-default">
        <div class="box-body">
            <div class="row">
                <div class="col-md-4">
                    <label>Buscar (nombre o email)</label>
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="juan@email.com">
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
                    <button class="btn btn-primary"><i class="fa fa-search"></i> Buscar</button>
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

    <div class="box box-default">
        <div class="box-body table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th class="text-center">Órdenes</th>
                        <th class="text-center">Sorteos</th>
                        <th class="text-right">Total Gastado</th>
                        <th>Último Pedido</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($buyers as $buyer)
                    <tr>
                        <td>{{ $buyer->buyer_name }}</td>
                        <td>
                            <a href="?search={{ urlencode($buyer->buyer_email) }}">{{ $buyer->buyer_email }}</a>
                        </td>
                        <td>{{ $buyer->buyer_phone }}</td>
                        <td class="text-center">{{ $buyer->order_count }}</td>
                        <td class="text-center">{{ $buyer->sorteos_count }}</td>
                        <td class="text-right">${{ number_format($buyer->total_spent, 2) }}</td>
                        <td>{{ \Carbon\Carbon::parse($buyer->last_purchase)->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted">Sin resultados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="box-footer">
            {{ $buyers->appends(request()->query())->links() }}
        </div>
    </div>
@endsection
