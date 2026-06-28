@extends('layouts.crud.create_edit')

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
    @parent
    @component('components.box')
        {!! CoralsForm::openForm($boleto) !!}

        <div class="row">
            <div class="col-md-4">
                {!! CoralsForm::select('status', 'Sorteos::attributes.boleto.status', $statuses, true, $boleto->status?->value) !!}
            </div>
            <div class="col-md-4">
                {!! CoralsForm::number('physical_number', 'Sorteos::attributes.boleto.physical_number', true, $boleto->physical_number, ['min' => 1]) !!}
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>{{ trans('Sorteos::attributes.boleto.digital_number') }}</label>
                    <p class="form-control-static"><strong>#{{ $boleto->digital_number }}</strong> <small class="text-muted">(no editable)</small></p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                {!! CoralsForm::formButtons() !!}
            </div>
        </div>

        {!! CoralsForm::closeForm($boleto) !!}
    @endcomponent
@endsection
