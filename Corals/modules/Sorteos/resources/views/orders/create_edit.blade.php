@extends('layouts.crud.create_edit')

@section('content_header')
    @component('components.content_header')
        @slot('page_title')
            {{ $title_singular }}
        @endslot
        @slot('breadcrumb')
            {{ Breadcrumbs::render('sorteos_order_create_edit') }}
        @endslot
    @endcomponent
@endsection

@section('content')
    @parent
    <div class="row">

        {{-- Columna izquierda: campos del formulario --}}
        <div class="{{ $order->exists ? 'col-md-8' : 'col-md-12' }}">
            @component('components.box')
                {!! CoralsForm::openForm($order) !!}

                {{-- Sorteo y Colaborador --}}
                <div class="row">
                    <div class="col-md-6">
                        {!! CoralsForm::select('sorteo_id', 'Sorteos::attributes.order.sorteo_id', $sorteos, true, $order->sorteo_id, ['id' => 'sorteo_id']) !!}
                    </div>
                    <div class="col-md-6">
                        {!! CoralsForm::select('colaborador_id', 'Sorteos::attributes.order.colaborador_id', $colaboradores, false, $order->colaborador_id, ['id' => 'colaborador_id']) !!}
                    </div>
                </div>

                {{-- Datos del comprador --}}
                <div class="row">
                    <div class="col-md-4">
                        {!! CoralsForm::text('buyer_name', 'Sorteos::attributes.order.buyer_name', true, $order->buyer_name) !!}
                    </div>
                    <div class="col-md-4">
                        {!! CoralsForm::text('buyer_email', 'Sorteos::attributes.order.buyer_email', true, $order->buyer_email) !!}
                    </div>
                    <div class="col-md-4">
                        {!! CoralsForm::text('buyer_phone', 'Sorteos::attributes.order.buyer_phone', true, $order->buyer_phone) !!}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        {!! CoralsForm::text('buyer_city', 'Sorteos::attributes.order.buyer_city', false, $order->buyer_city) !!}
                    </div>
                    <div class="col-md-6">
                        {!! CoralsForm::text('buyer_state', 'Sorteos::attributes.order.buyer_state', false, $order->buyer_state) !!}
                    </div>
                </div>

                @if($order->exists)
                    {{-- Boletos: solo lectura --}}
                    <div class="row">
                        <div class="col-md-12">
                            <label>Boletos</label>
                            @if($order->items->isEmpty())
                                <p class="text-muted">Sin boletos registrados.</p>
                            @else
                                <table class="table table-sm table-bordered">
                                    <thead><tr><th>#</th><th>No. Digital</th><th>Cartera</th><th>Estado</th></tr></thead>
                                    <tbody>
                                        @foreach($order->items as $i => $item)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td>{{ $item->boleto?->digital_number ?? '—' }}</td>
                                                <td>{{ $item->boleto?->cartera?->code ?? '—' }}</td>
                                                <td>{{ $item->boleto?->status?->label() ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                @else
                    {{-- Selección dinámica cuando hay colaborador --}}
                    <div id="boleto-dynamic" style="display:none">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cartera</label>
                                    <select id="cartera-select" class="form-control">
                                        <option value="">— Seleccionar cartera —</option>
                                    </select>
                                    <small class="text-muted">Carteras asignadas al colaborador</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Boletos disponibles</label>
                                    <select id="boleto-select" name="boleto_ids[]" class="form-control" multiple size="6">
                                        <option value="" disabled>Selecciona una cartera primero</option>
                                    </select>
                                    <small class="text-muted">Ctrl+clic para seleccionar varios</small>
                                </div>
                            </div>
                        </div>
                        <div id="no-carteras-alert" class="alert alert-warning" style="display:none">
                            <i class="fa fa-exclamation-triangle"></i>
                            Este colaborador no tiene carteras asignadas para el sorteo seleccionado.
                        </div>
                    </div>

                    {{-- Selección manual sin colaborador --}}
                    <div id="boleto-manual">
                        <div class="row">
                            <div class="col-md-6">
                                {!! CoralsForm::textarea('boleto_ids_text', 'Sorteos::attributes.order.boleto_ids_text', false, null, ['rows' => 3, 'placeholder' => '1, 2, 3, 10, 25']) !!}
                                <small class="text-muted">{{ trans('Sorteos::attributes.order.boleto_ids_hint') }}</small>
                            </div>
                            <div class="col-md-6">
                                {!! CoralsForm::textarea('cartera_codes_text', 'Sorteos::attributes.order.cartera_codes_text', false, null, ['rows' => 3, 'placeholder' => 'A001, A002']) !!}
                                <small class="text-muted">{{ trans('Sorteos::attributes.order.cartera_codes_hint') }}</small>
                            </div>
                        </div>
                    </div>

                    <script>
                    (function () {
                        var apiUrl    = '{{ route('sorteos.orders.carteras-by-colaborador') }}';
                        var carteraEl = document.getElementById('cartera-select');
                        var boletoEl  = document.getElementById('boleto-select');
                        var dynamic   = document.getElementById('boleto-dynamic');
                        var manual    = document.getElementById('boleto-manual');
                        var noAlert   = document.getElementById('no-carteras-alert');
                        var carterasData = {};

                        function loadCarteras() {
                            var colaboradorId = document.getElementById('colaborador_id').value;
                            var sorteoId      = document.getElementById('sorteo_id').value;
                            if (!colaboradorId || !sorteoId) {
                                dynamic.style.display = 'none';
                                manual.style.display  = '';
                                return;
                            }
                            fetch(apiUrl + '?colaborador_id=' + colaboradorId + '&sorteo_id=' + sorteoId, {
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            })
                            .then(function(r) { return r.json(); })
                            .then(function(data) {
                                carterasData = {};
                                carteraEl.innerHTML = '<option value="">— Seleccionar cartera —</option>';
                                boletoEl.innerHTML  = '<option value="" disabled>Selecciona una cartera primero</option>';
                                if (!data.carteras || data.carteras.length === 0) {
                                    noAlert.style.display = '';
                                    dynamic.style.display = '';
                                    manual.style.display  = 'none';
                                    return;
                                }
                                noAlert.style.display = 'none';
                                data.carteras.forEach(function(c) {
                                    carterasData[c.id] = c.boletos;
                                    var opt = document.createElement('option');
                                    opt.value       = c.id;
                                    opt.textContent = c.code + ' (' + c.boletos.length + ' disponibles)';
                                    carteraEl.appendChild(opt);
                                });
                                dynamic.style.display = '';
                                manual.style.display  = 'none';
                            });
                        }

                        carteraEl.addEventListener('change', function () {
                            var cid = this.value;
                            boletoEl.innerHTML = '';
                            if (!cid || !carterasData[cid]) {
                                boletoEl.innerHTML = '<option value="" disabled>Selecciona una cartera primero</option>';
                                return;
                            }
                            carterasData[cid].forEach(function(b) {
                                var opt = document.createElement('option');
                                opt.value       = b.id;
                                opt.textContent = 'Boleto #' + b.number;
                                boletoEl.appendChild(opt);
                            });
                        });

                        document.getElementById('colaborador_id').addEventListener('change', loadCarteras);
                        document.getElementById('sorteo_id').addEventListener('change', loadCarteras);
                    })();
                    </script>
                @endif

                <div class="row">
                    <div class="col-md-12">
                        {!! CoralsForm::textarea('notes', 'Sorteos::attributes.order.notes', false, $order->notes, ['rows' => 2]) !!}
                    </div>
                </div>

                {!! CoralsForm::customFields($order) !!}

                <div class="row">
                    <div class="col-md-12">
                        {!! CoralsForm::formButtons() !!}
                    </div>
                </div>

                {!! CoralsForm::closeForm($order) !!}
            @endcomponent
        </div>

        {{-- Columna derecha: Pago + acciones (solo al editar) --}}
        @if($order->exists)
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <strong>Estado</strong>
                    @if($order->status)
                        <span class="badge {{ $order->status->badgeClass() }} float-right" style="font-size:.9em; margin-top:2px">
                            {{ $order->status->label() }}
                        </span>
                    @endif
                </div>
                <div class="card-body">

                    {{-- Sección Pago: campos asociados al formulario principal vía atributo form= --}}
                    <h6 class="text-muted text-uppercase mb-3"><i class="fa fa-credit-card"></i> Pago</h6>

                    <div class="form-group">
                        <label>{{ trans('Sorteos::attributes.order.payment_method') }}</label>
                        <select name="payment_method" form="order-edit-form" class="form-control">
                            @foreach($paymentMethods as $value => $label)
                                <option value="{{ $value }}" {{ $order->payment_method?->value === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>{{ trans('Sorteos::attributes.order.payment_reference') }}</label>
                        <input type="text" name="payment_reference" form="order-edit-form"
                               class="form-control" placeholder="Folio, No. transferencia, etc."
                               value="{{ $order->payment_reference }}">
                    </div>

                    <hr>

                    {{-- Botón Guardar (submit del form principal) --}}
                    <button type="submit" form="order-edit-form" class="btn btn-primary btn-block mb-3">
                        <i class="fa fa-save"></i> Guardar
                    </button>

                    @if($order->isPending())
                        <button type="button" class="btn btn-success btn-block mb-2"
                                onclick="orderAction('{{ route('sorteos.orders.confirm', $order->hashed_id) }}', '¿Confirmar el pago de esta orden?')">
                            <i class="fa fa-check"></i> Confirmar Pago
                        </button>
                        <button type="button" class="btn btn-danger btn-block"
                                onclick="orderAction('{{ route('sorteos.orders.cancel', $order->hashed_id) }}', '¿Cancelar esta orden? Los boletos quedarán disponibles nuevamente.')">
                            <i class="fa fa-times"></i> Cancelar Orden
                        </button>
                    @endif

                    @if($order->isConfirmed())
                        <button class="btn btn-info btn-block"
                                data-action="post"
                                data-request-data='{"_token":"{{ csrf_token() }}"}'
                                data-href="{{ route('sorteos.orders.resend', $order->hashed_id) }}">
                            <i class="fa fa-envelope"></i> Reenviar Email
                        </button>
                    @endif

                </div>
            </div>
        </div>

        <script>
        // Give the main form a known ID so we can reference it from the right column
        document.addEventListener('DOMContentLoaded', function () {
            var forms = document.querySelectorAll('form');
            for (var i = 0; i < forms.length; i++) {
                if (forms[i].action.indexOf('/orders/') !== -1) {
                    forms[i].id = 'order-edit-form';
                    break;
                }
            }
        });

        // Submit the main form to a different action URL (confirm / cancel)
        function orderAction(url, msg) {
            if (!confirm(msg)) { return; }
            var form = document.getElementById('order-edit-form');
            // Remove _method spoofing so the request goes as plain POST
            var m = form.querySelector('input[name="_method"]');
            if (m) { m.parentNode.removeChild(m); }
            form.action = url;
            form.submit();
        }
        </script>
        @endif

    </div>
@endsection
