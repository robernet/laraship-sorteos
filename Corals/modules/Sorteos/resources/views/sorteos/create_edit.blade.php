@extends('layouts.crud.create_edit')

@section('content_header')
    @component('components.content_header')
        @slot('page_title')
            {{ $title_singular }}
        @endslot
        @slot('breadcrumb')
            {{ Breadcrumbs::render('sorteos_sorteo_create_edit') }}
        @endslot
    @endcomponent
@endsection

@section('content')
    @parent
    @php
        $statusOptions = collect(\Corals\Modules\Sorteos\Enums\SorteoStatus::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->all();
    @endphp
    <div class="row">
        <div class="col-md-12">
            @component('components.box')
                {!! CoralsForm::openForm($sorteo) !!}

                {{-- Nombre y Slug --}}
                <div class="row">
                    <div class="col-md-8">
                        {!! CoralsForm::text('name', 'Sorteos::attributes.sorteo.name', true, $sorteo->name) !!}
                    </div>
                    <div class="col-md-4">
                        {!! CoralsForm::text('slug', 'Sorteos::attributes.sorteo.slug', true, $sorteo->slug) !!}
                    </div>
                </div>

                {{-- Descripción general --}}
                <div class="row">
                    <div class="col-md-12">
                        {!! CoralsForm::textarea('description', 'Sorteos::attributes.sorteo.description', false, $sorteo->description, ['rows' => 3]) !!}
                    </div>
                </div>

                {{-- Descripción del premio --}}
                <div class="row">
                    <div class="col-md-12">
                        {!! CoralsForm::textarea('prize_description', 'Sorteos::attributes.sorteo.prize_description', false, $sorteo->prize_description, ['rows' => 3]) !!}
                    </div>
                </div>

                {{-- Precio, estado, imagen, visibilidad --}}
                <div class="row">
                    <div class="col-md-3">
                        {!! CoralsForm::number('ticket_price', 'Sorteos::attributes.sorteo.ticket_price', true, $sorteo->ticket_price) !!}
                    </div>
                    <div class="col-md-3">
                        {!! CoralsForm::select('status', 'Sorteos::attributes.sorteo.status', $statusOptions, true, $sorteo->status?->value) !!}
                    </div>
                    <div class="col-md-3">
                        {!! CoralsForm::text('cover_image', 'Sorteos::attributes.sorteo.cover_image', false, $sorteo->cover_image) !!}
                    </div>
                    <div class="col-md-3">
                        {!! CoralsForm::checkbox('is_public', 'Sorteos::attributes.sorteo.is_public', $sorteo->is_public) !!}
                    </div>
                </div>

                {{-- Fechas --}}
                <div class="row">
                    <div class="col-md-4">
                        {!! CoralsForm::date('starts_at', 'Sorteos::attributes.sorteo.starts_at', false, $sorteo->starts_at?->format('Y-m-d')) !!}
                    </div>
                    <div class="col-md-4">
                        {!! CoralsForm::date('ends_at', 'Sorteos::attributes.sorteo.ends_at', false, $sorteo->ends_at?->format('Y-m-d')) !!}
                    </div>
                    <div class="col-md-4">
                        {!! CoralsForm::date('draw_date', 'Sorteos::attributes.sorteo.draw_date', false, $sorteo->draw_date?->format('Y-m-d')) !!}
                    </div>
                </div>

                {!! CoralsForm::customFields($sorteo) !!}

                <div class="row">
                    <div class="col-md-12">
                        {!! CoralsForm::formButtons() !!}
                    </div>
                </div>
                {!! CoralsForm::closeForm($sorteo) !!}
            @endcomponent
        </div>
    </div>
@endsection

@section('js')
@endsection
