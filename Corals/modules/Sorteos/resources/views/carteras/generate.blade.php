@extends('layouts.master')

@section('content_header')
    @component('components.content_header')
        @slot('page_title') Generar Carteras @endslot
        @slot('breadcrumb')
            {{ Breadcrumbs::render('sorteos_carteras') }}
        @endslot
    @endcomponent
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Generación automática de carteras</h3>
            </div>
            <form method="POST" action="{{ route('sorteos.carteras.do-generate') }}">
                @csrf
                <div class="box-body">

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-group">
                        <label>Sorteo <span class="text-danger">*</span></label>
                        <select name="sorteo_id" id="sorteo_id" class="form-control" required>
                            <option value="">-- Seleccionar --</option>
                            @foreach($sorteos as $id => $name)
                                <option value="{{ $id }}" {{ old('sorteo_id', $sorteoId) == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Total de boletos <span class="text-danger">*</span></label>
                                <input type="number" name="total_boletos" id="total_boletos"
                                    class="form-control" min="10" step="10"
                                    value="{{ old('total_boletos') }}" required>
                                <p class="help-block">Debe ser múltiplo de 10</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Número inicial <span class="text-danger">*</span></label>
                                <input type="number" name="start_number" id="start_number"
                                    class="form-control" min="1"
                                    value="{{ old('start_number', $nextStart) }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Prefijo de código <span class="text-danger">*</span></label>
                                <input type="text" name="code_prefix" id="code_prefix"
                                    class="form-control" maxlength="5"
                                    value="{{ old('code_prefix', 'C') }}" required>
                                <p class="help-block">Ej: C → C001, C002…</p>
                            </div>
                        </div>
                    </div>

                    <div id="preview-box" class="alert alert-info" style="display:none">
                        <i class="fa fa-info-circle"></i> <span id="preview"></span>
                    </div>

                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-magic"></i> Generar Carteras
                    </button>
                    <a href="{{ url(config('sorteos.models.cartera.resource_url')) }}" class="btn btn-default ml-2">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
(function () {
    var fields = ['total_boletos', 'start_number', 'code_prefix'];
    fields.forEach(function(id) {
        document.getElementById(id).addEventListener('input', updatePreview);
    });

    document.getElementById('sorteo_id').addEventListener('change', function () {
        var sid = this.value;
        if (!sid) return;
        fetch('{{ url("sorteos/carteras/generate") }}?sorteo_id=' + sid, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function(r) { return r.text(); }).then(function(html) {
            var parser = new DOMParser();
            var doc = parser.parseFromString(html, 'text/html');
            var val = doc.getElementById('start_number') ? doc.getElementById('start_number').value : null;
            if (val) document.getElementById('start_number').value = val;
            updatePreview();
        });
    });

    function updatePreview() {
        var total  = parseInt(document.getElementById('total_boletos').value) || 0;
        var start  = parseInt(document.getElementById('start_number').value) || 1;
        var prefix = (document.getElementById('code_prefix').value || 'C').toUpperCase();
        var num    = Math.ceil(total / 10);
        var pad    = Math.max(3, String(num).length);
        var last   = start + total - 1;
        var first  = prefix + String(1).padStart(pad, '0');
        var lstc   = prefix + String(num).padStart(pad, '0');
        var box    = document.getElementById('preview-box');
        if (num > 0) {
            document.getElementById('preview').textContent =
                num + ' carteras · boletos ' + start + '–' + last + ' · códigos ' + first + ' a ' + lstc;
            box.style.display = '';
        } else {
            box.style.display = 'none';
        }
    }

    updatePreview();
})();
</script>
@endpush
