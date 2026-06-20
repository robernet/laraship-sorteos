@extends('layouts.crud.show')

@section('content_header')
    @component('components.content_header')
        @slot('page_title')
            {{ $title_singular }}
        @endslot
        @slot('breadcrumb')
            {{ Breadcrumbs::render('sorteos_colaborador_show') }}
        @endslot
    @endcomponent
@endsection

@section('content')
    @component('components.box')
        <div class="row">
            <div class="col-md-6">
                <table class="table table-striped">
                    <tr>
                        <th>{{ trans('Sorteos::attributes.colaborador.name') }}</th>
                        <td>{{ $colaborador->name }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.colaborador.email') }}</th>
                        <td>{{ $colaborador->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.colaborador.phone') }}</th>
                        <td>{{ $colaborador->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.colaborador.type') }}</th>
                        <td>{{ $colaborador->type === 'institucion' ? 'Institución' : 'Persona' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.colaborador.status') }}</th>
                        <td>
                            @if($colaborador->status)
                                <span class="badge {{ $colaborador->status->badgeClass() }}">{{ $colaborador->status->label() }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.colaborador.notes') }}</th>
                        <td>{{ $colaborador->notes ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                @php
                    $total     = $colaborador->carteras->count();
                    $asignadas = $colaborador->carteras->filter(fn($c) => $c->status?->value === 'asignado')->count();
                    $entregadas = $colaborador->carteras->filter(fn($c) => $c->status?->value === 'entregado')->count();
                    $vendidas  = $colaborador->carteras->filter(fn($c) => $c->status?->value === 'sold')->count();
                @endphp
                <table class="table table-bordered text-center">
                    <thead class="bg-light">
                        <tr>
                            <th>Total Carteras</th>
                            <th>Asignadas</th>
                            <th>Entregadas</th>
                            <th>Vendidas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>{{ $total }}</strong></td>
                            <td><span class="badge badge-info">{{ $asignadas }}</span></td>
                            <td><span class="badge badge-primary">{{ $entregadas }}</span></td>
                            <td><span class="badge badge-danger">{{ $vendidas }}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endcomponent

    @component('components.box')
        @slot('box_title')
            {{ trans('Sorteos::module.cartera.title') }} ({{ $colaborador->carteras->count() }})
        @endslot
        @slot('box_actions')
            @if($assignableCarteras->isNotEmpty())
            <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#assignModal">
                <i class="fa fa-plus"></i> Asignar Carteras
            </button>
            @endif
        @endslot

        @if($colaborador->carteras->isNotEmpty())
        <div class="row" style="margin-bottom:12px">
            <div class="col-md-5">
                <input type="text" id="searchCode" class="form-control input-sm" placeholder="Buscar por código...">
            </div>
            <div class="col-md-4">
                <select id="filterStatus" class="form-control input-sm">
                    <option value="">— Todos los estados —</option>
                    <option value="asignado">Asignado</option>
                    <option value="entregado">Entregado</option>
                    <option value="active">Activa</option>
                    <option value="available">Disponible</option>
                    <option value="sold">Vendida</option>
                </select>
            </div>
            <div class="col-md-3 text-right">
                <span id="carteras-count" class="text-muted" style="line-height:30px;font-size:12px"></span>
            </div>
        </div>
        <div style="overflow-x:auto">
        <table class="table table-striped table-hover table-condensed" id="carterasTable">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Sorteo</th>
                    <th>Boletos</th>
                    <th>Estado</th>
                    <th>Asignado el</th>
                    <th>Entregado el</th>
                    <th>Vendidos</th>
                    <th style="width:160px">Cambiar estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($colaborador->carteras->sortBy('code') as $cartera)
                <tr data-id="{{ $cartera->hashed_id }}" data-code="{{ strtolower($cartera->code) }}" data-status="{{ $cartera->status?->value }}">
                    <td><strong>{{ $cartera->code }}</strong></td>
                    <td>{{ $cartera->sorteo?->name ?? '-' }}</td>
                    <td>{{ $cartera->physical_start }}–{{ $cartera->physical_end }}</td>
                    <td>
                        <span class="status-badge badge {{ $cartera->status?->badgeClass() }}">
                            {{ $cartera->status?->label() }}
                        </span>
                    </td>
                    <td class="date-asignado">{{ $cartera->asignado_at?->format('d/m/Y') ?? '-' }}</td>
                    <td class="date-entregado">{{ $cartera->entregado_at?->format('d/m/Y') ?? '-' }}</td>
                    <td>
                        @php
                            $sold  = $cartera->boletos->whereIn('status', [\Corals\Modules\Sorteos\Enums\BoletoStatus::Sold, \Corals\Modules\Sorteos\Enums\BoletoStatus::Reserved])->count();
                            $total = $cartera->boletos->count();
                        @endphp
                        {{ $sold }}/{{ $total }}
                    </td>
                    <td>
                        @php
                            $transitions = match($cartera->status?->value) {
                                'available'  => ['asignado' => 'Asignar'],
                                'asignado'   => ['entregado' => 'Marcar Entregada', 'available' => 'Revertir a Disponible'],
                                'entregado'  => ['asignado' => 'Revertir a Asignado'],
                                'sold'       => ['entregado' => 'Registrar Entrega'],
                                default      => [],
                            };
                        @endphp
                        @if($transitions)
                        <select class="form-control input-sm status-changer"
                                style="display:inline-block;width:auto;max-width:140px"
                                data-id="{{ $cartera->hashed_id }}"
                                data-code="{{ $cartera->code }}"
                                data-current="{{ $cartera->status?->label() }}">
                            <option value="">— Cambiar —</option>
                            @foreach($transitions as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @else
                        <span class="text-muted" style="font-size:11px">—</span>
                        @endif
                        <a href="{{ $cartera->getShowURL() }}" class="btn btn-xs btn-default" title="Ver detalle">
                            <i class="fa fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @else
        <p class="text-muted">No hay carteras asignadas aún.</p>
        @endif
    @endcomponent

    @if($assignableCarteras->isNotEmpty())
    <div class="modal fade" id="assignModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('sorteos.colaboradores.assign-carteras', $colaborador->hashed_id) }}">
                    @csrf
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Asignar Carteras a {{ $colaborador->name }}</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Filtrar por Sorteo</label>
                            <select id="sorteoFilter" class="form-control">
                                <option value="">— Todos los sorteos —</option>
                                @foreach($sorteos as $id => $name)
                                    @if($assignableCarteras->has($id))
                                    <option value="{{ $id }}">{{ $name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="checkbox" style="margin-bottom:6px">
                                <label><input type="checkbox" id="selectAll"> <strong>Seleccionar todas las visibles</strong></label>
                            </div>
                            <div style="max-height:350px;overflow-y:auto;border:1px solid #ddd;border-radius:4px;padding:8px">
                                @foreach($assignableCarteras as $sorteoId => $carteras)
                                <div class="sorteo-group" data-sorteo="{{ $sorteoId }}">
                                    <p class="text-muted" style="margin:6px 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:.5px">
                                        {{ $carteras->first()->sorteo?->name ?? 'Sin sorteo' }}
                                    </p>
                                    @foreach($carteras as $cartera)
                                    <div class="checkbox" style="margin:2px 0">
                                        <label>
                                            <input type="checkbox" name="cartera_ids[]" value="{{ $cartera->id }}" class="cartera-check">
                                            <strong>{{ $cartera->code }}</strong>
                                            <span class="text-muted">· boletos {{ $cartera->physical_start }}–{{ $cartera->physical_end }}</span>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <span id="selectedCount" class="text-muted pull-left" style="line-height:34px">0 seleccionadas</span>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Asignar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
    (function(){
        var filter    = document.getElementById('sorteoFilter');
        var selectAll = document.getElementById('selectAll');
        var counter   = document.getElementById('selectedCount');

        function updateCount() {
            var n = document.querySelectorAll('.cartera-check:checked').length;
            counter.textContent = n + ' seleccionada' + (n !== 1 ? 's' : '');
        }

        filter.addEventListener('change', function(){
            var val = this.value;
            document.querySelectorAll('.sorteo-group').forEach(function(g){
                g.style.display = (!val || g.dataset.sorteo === val) ? '' : 'none';
            });
            selectAll.checked = false;
            updateCount();
        });

        selectAll.addEventListener('change', function(){
            document.querySelectorAll('.sorteo-group:not([style*="none"]) .cartera-check').forEach(function(c){
                c.checked = selectAll.checked;
            });
            updateCount();
        });

        document.querySelectorAll('.cartera-check').forEach(function(c){
            c.addEventListener('change', updateCount);
        });
    })();
    </script>
    @endif
{{-- Single confirmation modal for all status changes --}}
<div class="modal fade" id="confirmStatusModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle text-warning"></i> Confirmar cambio de estado</h4>
            </div>
            <div class="modal-body">
                <p id="confirmMsg" style="font-size:14px;margin:0"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" id="cancelStatusBtn">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmStatusBtn"><i class="fa fa-check"></i> Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    // Search + filter
    var searchInput  = document.getElementById('searchCode');
    var statusSelect = document.getElementById('filterStatus');
    var countSpan    = document.getElementById('carteras-count');

    function filterTable() {
        var code   = searchInput ? searchInput.value.toLowerCase() : '';
        var status = statusSelect ? statusSelect.value : '';
        var rows   = document.querySelectorAll('#carterasTable tbody tr');
        var visible = 0;
        rows.forEach(function(row) {
            var show = (!code   || row.dataset.code.indexOf(code) !== -1)
                    && (!status || row.dataset.status === status);
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        if (countSpan) countSpan.textContent = visible + ' de ' + rows.length + ' carteras';
    }
    if (searchInput)  searchInput.addEventListener('input', filterTable);
    if (statusSelect) statusSelect.addEventListener('change', filterTable);
    filterTable();

    // Status change with confirmation
    var csrf         = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var pendingSelect = null;
    var pendingId     = null;
    var pendingStatus = null;

    var statusLabels = {
        active: 'Activa', available: 'Disponible',
        asignado: 'Asignado', entregado: 'Entregado', sold: 'Vendida'
    };

    document.addEventListener('change', function(e) {
        var sel = e.target.closest('.status-changer');
        if (!sel || !sel.value) return;

        pendingSelect = sel;
        pendingId     = sel.dataset.id;
        pendingStatus = sel.value;

        var code    = sel.dataset.code;
        var current = sel.dataset.current;
        var newLabel = sel.options[sel.selectedIndex].text;

        document.getElementById('confirmMsg').innerHTML =
            '¿Cambiar cartera <strong>' + code + '</strong> de <em>' + current + '</em> a <strong>' + newLabel + '</strong>?';

        $('#confirmStatusModal').modal('show');
    });

    document.getElementById('cancelStatusBtn').addEventListener('click', function() {
        if (pendingSelect) { pendingSelect.value = ''; pendingSelect = null; }
    });

    document.getElementById('confirmStatusModal').addEventListener('hidden.bs.modal', function() {
        if (pendingSelect) { pendingSelect.value = ''; pendingSelect = null; }
    });

    document.getElementById('confirmStatusBtn').addEventListener('click', function() {
        if (!pendingId || !pendingStatus) return;
        var btn = this;
        btn.disabled = true;
        btn.textContent = 'Guardando...';

        fetch('/sorteos/carteras/' + pendingId + '/quick-status', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
            body: JSON.stringify({status: pendingStatus})
        })
        .then(function(r){ return r.json(); })
        .then(function(data){
            if (data.level === 'success') {
                window.location.reload();
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-check"></i> Confirmar';
                if (pendingSelect) { pendingSelect.value = ''; }
                alert('Error al cambiar el estado.');
            }
        })
        .catch(function(){
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-check"></i> Confirmar';
            if (pendingSelect) { pendingSelect.value = ''; }
        });

        $('#confirmStatusModal').modal('hide');
        pendingSelect = null;
    });
})();
</script>
@endsection
