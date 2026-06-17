@extends('layouts.crud.index')

@section('content_header')
    @component('components.content_header')
        @slot('page_title')
            {{ $title }}
        @endslot
        @slot('breadcrumb')
            {{ Breadcrumbs::render('sorteos_carteras') }}
        @endslot
    @endcomponent
@endsection

@section('actions')
    @parent
    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#importCsvModal">
        <i class="fa fa-upload"></i> Importar CSV
    </button>
@endsection

@section('content')
    @parent

    {{-- Import CSV Modal --}}
    <div class="modal fade" id="importCsvModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('sorteos.carteras.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Importar Carteras desde CSV</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Sorteo <span class="text-danger">*</span></label>
                            <select name="sorteo_id" class="form-control" required>
                                <option value="">-- Seleccionar Sorteo --</option>
                                @foreach(\Corals\Modules\Sorteos\Models\Sorteo::pluck('name','id') as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Archivo CSV <span class="text-danger">*</span></label>
                            <input type="file" name="csv_file" accept=".csv,.txt" required class="form-control">
                            <p class="help-block">
                                Columnas requeridas: <code>code, physical_start, digital_start</code>.<br>
                                <a href="{{ route('sorteos.carteras.import.template') }}">Descargar plantilla</a>
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-upload"></i> Importar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
