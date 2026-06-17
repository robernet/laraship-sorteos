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
        <div class="col-md-12">
            @component('components.box')
                {!! CoralsForm::openForm($order) !!}

                {{-- Sorteo y método de pago --}}
                <div class="row">
                    <div class="col-md-6">
                        {!! CoralsForm::select('sorteo_id', 'Sorteos::attributes.order.sorteo_id', $sorteos, true, $order->sorteo_id) !!}
                    </div>
                    <div class="col-md-6">
                        {!! CoralsForm::select('payment_method', 'Sorteos::attributes.order.payment_method', $paymentMethods, true, $order->payment_method?->value) !!}
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
                    {{-- Edit: show assigned boletos, no re-assignment --}}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                {{ trans('Sorteos::attributes.order.boletos_locked') }}
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Create: boleto selection --}}
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
    </div>
@endsection
