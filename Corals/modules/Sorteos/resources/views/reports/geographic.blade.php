@extends('layouts.crud.index')

@section('content_header')
    @component('components.content_header')
        @slot('page_title') Reporte Geográfico @endslot
        @slot('breadcrumb') {{ Breadcrumbs::render('sorteos_reports') }} @endslot
    @endcomponent
@endsection

@section('content')
    <form method="GET" class="box box-default">
        <div class="box-body">
            <div class="row">
                <div class="col-md-4">
                    <label>Sorteo</label>
                    <select name="sorteo_id" class="form-control">
                        <option value="">— Todos —</option>
                        @foreach($sorteos as $id => $name)
                            <option value="{{ $id }}" @selected($sorteoId == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4" style="padding-top:25px">
                    <button class="btn btn-primary"><i class="fa fa-search"></i> Filtrar</button>
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

    @if($data->isEmpty())
        <div class="alert alert-info">No hay datos geográficos registrados aún.</div>
    @else
        <div class="box box-default">
            <div class="box-header"><h3 class="box-title">Compras por Estado y Ciudad</h3></div>
            <div class="box-body table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Estado / Región</th>
                            <th>Ciudad</th>
                            <th>Órdenes</th>
                            <th>Ingresos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $row)
                        <tr>
                            <td>{{ $row->state }}</td>
                            <td>{{ $row->city }}</td>
                            <td>{{ $row->orders_count }}</td>
                            <td>${{ number_format($row->revenue, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
