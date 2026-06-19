@extends('layouts.crud.show')

@section('content_header')
    @component('components.content_header')
        @slot('page_title')
            {{ $title_singular }}
        @endslot
        @slot('breadcrumb')
            {{ Breadcrumbs::render('sorteos_cartera_show') }}
        @endslot
    @endcomponent
@endsection

@section('content')
    @component('components.box')
        <div class="row">
            <div class="col-md-6">
                <table class="table table-striped">
                    <tr>
                        <th>{{ trans('Sorteos::attributes.cartera.code') }}</th>
                        <td>{{ $cartera->code }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.cartera.sorteo_id') }}</th>
                        <td>{{ $cartera->sorteo?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.cartera.asignado_id') }}</th>
                        <td>{{ $cartera->asignado?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.cartera.status') }}</th>
                        <td>
                            @if($cartera->status)
                                <span class="badge {{ $cartera->status->badgeClass() }}">{{ $cartera->status->label() }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.cartera.physical_start') }}</th>
                        <td>{{ $cartera->physical_start }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.cartera.physical_end') }}</th>
                        <td>{{ $cartera->physical_end }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.cartera.digital_start') }}</th>
                        <td>{{ $cartera->digital_start }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('Sorteos::attributes.cartera.digital_end') }}</th>
                        <td>{{ $cartera->digital_end }}</td>
                    </tr>
                </table>
            </div>
        </div>
    @endcomponent

    @component('components.box')
        @slot('box_title')
            {{ trans('Sorteos::module.boleto.title') }} ({{ $cartera->boletos->count() }})
        @endslot

        <table class="table table-striped table-hover table-condensed">
            <thead>
                <tr>
                    <th>{{ trans('Sorteos::attributes.boleto.digital_number') }}</th>
                    <th>{{ trans('Sorteos::attributes.boleto.physical_number') }}</th>
                    <th>{{ trans('Sorteos::attributes.boleto.status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($cartera->boletos->sortBy('digital_number') as $boleto)
                <tr>
                    <td>#{{ str_pad($boleto->digital_number, 5, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $boleto->physical_number }}</td>
                    <td>
                        @if($boleto->status)
                            <span class="badge {{ $boleto->status->badgeClass() }}">{{ $boleto->status->label() }}</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('sorteos.boletos.pdf', $boleto->hashed_id) }}"
                           class="btn btn-xs btn-default" target="_blank">
                            <i class="fa fa-file-pdf-o"></i> PDF
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endcomponent
@endsection
