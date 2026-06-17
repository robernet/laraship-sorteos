@extends('layouts.crud.create_edit')

@section('content_header')
    @component('components.content_header')
        @slot('page_title')
            {{ $title_singular }}
        @endslot
        @slot('breadcrumb')
            {{ Breadcrumbs::render('sorteos_cartera_create_edit') }}
        @endslot
    @endcomponent
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-12">
            @component('components.box')
                {!! CoralsForm::openForm($cartera) !!}

                <div class="row">
                    <div class="col-md-6">
                        {!! CoralsForm::select('sorteo_id', 'Sorteos::attributes.cartera.sorteo_id', $sorteos, true, $cartera->sorteo_id) !!}
                    </div>
                    <div class="col-md-6">
                        {!! CoralsForm::text('code', 'Sorteos::attributes.cartera.code', true, $cartera->code) !!}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        {!! CoralsForm::number('physical_start', 'Sorteos::attributes.cartera.physical_start', true, $cartera->physical_start) !!}
                    </div>
                    <div class="col-md-3">
                        {!! CoralsForm::number('digital_start', 'Sorteos::attributes.cartera.digital_start', true, $cartera->digital_start) !!}
                    </div>
                </div>

                {!! CoralsForm::customFields($cartera) !!}

                <div class="row">
                    <div class="col-md-12">
                        {!! CoralsForm::formButtons() !!}
                    </div>
                </div>

                {!! CoralsForm::closeForm($cartera) !!}
            @endcomponent
        </div>
    </div>
@endsection
