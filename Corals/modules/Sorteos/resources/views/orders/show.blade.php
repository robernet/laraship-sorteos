@extends('layouts.crud.show')

@section('content_header')
    @component('components.content_header')
        @slot('page_title')
            {{ $title_singular }}
        @endslot
        @slot('breadcrumb')
            {{ Breadcrumbs::render('sorteos_order_show') }}
        @endslot
    @endcomponent
@endsection

@section('content')
    @component('components.box')
        @slot('box_title')
            {{ trans('Sorteos::module.order.title_singular') }}
            @if($order->status)
                <span class="badge {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span>
            @endif
        @endslot

        <div class="row">
            <div class="col-md-6">
                <table class="table table-striped">
                    <tr>
                        <th>{{ trans('Sorteos::attributes.order.sorteo_id') }}</th>
                        <td>{{ $order->sorteo?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.order.buyer_name') }}</th>
                        <td>{{ $order->buyer_name }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.order.buyer_email') }}</th>
                        <td>{{ $order->buyer_email }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.order.buyer_phone') }}</th>
                        <td>{{ $order->buyer_phone }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.order.payment_method') }}</th>
                        <td>{{ $order->payment_method?->label() ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Referencia / Folio</th>
                        <td>{{ $order->payment_reference ?: '—' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.order.total_amount') }}</th>
                        <td>${{ number_format($order->total_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.order.notes') }}</th>
                        <td>{{ $order->notes ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    @endcomponent

    @component('components.box')
        @slot('box_title')
            {{ trans('Sorteos::module.boleto.title') }} ({{ $order->items->count() }})
        @endslot

        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>{{ trans('Sorteos::attributes.boleto.digital_number') }}</th>
                    <th>{{ trans('Sorteos::attributes.boleto.physical_number') }}</th>
                    <th>{{ trans('Sorteos::attributes.cartera.code') }}</th>
                    <th>{{ trans('Sorteos::attributes.boleto.status') }}</th>
                    <th>{{ trans('Sorteos::attributes.order.price') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>#{{ $item->boleto?->digital_number }}</td>
                        <td>{{ $item->boleto?->physical_number }}</td>
                        <td>{{ $item->boleto?->cartera?->code ?? '-' }}</td>
                        <td>
                            @if($item->boleto?->status)
                                <span class="badge {{ $item->boleto->status->badgeClass() }}">{{ $item->boleto->status->label() }}</span>
                            @endif
                        </td>
                        <td>${{ number_format($item->price, 2) }}</td>
                        <td>
                            @if($item->boleto)
                                <a href="{{ route('sorteos.boletos.pdf', $item->boleto->hashed_id) }}"
                                   class="btn btn-xs btn-default" target="_blank">
                                    <i class="fa fa-file-pdf-o"></i> PDF
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endcomponent

    @php $emailHistory = $order->properties['email_sends'] ?? []; @endphp
    @if(count($emailHistory))
    @component('components.box')
        @slot('box_title')
            {{ trans('Sorteos::attributes.order.email_history') }}
        @endslot
        <table class="table table-sm table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ trans('Sorteos::attributes.order.sent_at') }}</th>
                    <th>Message ID</th>
                </tr>
            </thead>
            <tbody>
                @foreach($emailHistory as $i => $send)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($send['sent_at'])->format('d/m/Y H:i') }}</td>
                    <td><small class="text-muted">{{ $send['message_id'] ?? '-' }}</small></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endcomponent
    @endif
@endsection
