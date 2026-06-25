@extends('layouts.crud.show')

@section('content_header')
    @component('components.content_header')
        @slot('page_title')
            {{ $title_singular }}
        @endslot
        @slot('breadcrumb')
            {{ Breadcrumbs::render('sorteos_boleto_show') }}
        @endslot
    @endcomponent
@endsection

@section('content')
    @component('components.box')
        <div class="row">
            <div class="col-md-6">
                <table class="table table-striped">
                    <tr>
                        <th>{{ trans('Sorteos::attributes.boleto.digital_number') }}</th>
                        <td>#{{ $boleto->digital_number }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.boleto.physical_number') }}</th>
                        <td>{{ $boleto->physical_number }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.boleto.cartera_id') }}</th>
                        <td>{{ $boleto->cartera?->code ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.boleto.sorteo_id') }}</th>
                        <td>{{ $boleto->sorteo?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.boleto.status') }}</th>
                        <td>
                            @if($boleto->status)
                                <span class="badge {{ $boleto->status->badgeClass() }}">{{ $boleto->status->label() }}</span>
                            @endif
                        </td>
                    </tr>
                    @php $order = $boleto->orderItems->first()?->order; @endphp
                    <tr>
                        <th>Comprador</th>
                        <td>
                            @if($order)
                                {{ $order->buyer_name }}<br>
                                <small class="text-muted">{{ $order->buyer_email }}</small><br>
                                <small class="text-muted">{{ $order->buyer_phone }}</small>
                                <a href="{{ url('sorteos/orders/' . $order->hashed_id) }}" class="btn btn-xs btn-default ml-2">
                                    <i class="fa fa-external-link"></i> Ver orden
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    @endcomponent
@endsection
