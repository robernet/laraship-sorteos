@extends('layouts.crud.create_edit')

@section('content_header')
    @component('components.content_header')
        @slot('page_title')
            {{ $title_singular }}
        @endslot
        @slot('breadcrumb')
            {{ Breadcrumbs::render('sorteos_asignado_create_edit') }}
        @endslot
    @endcomponent
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-12">
            @component('components.box')
                {!! CoralsForm::openForm($asignado) !!}

                <div class="row">
                    <div class="col-md-6">
                        {!! CoralsForm::text('name', 'Sorteos::attributes.asignado.name', true, $asignado->name) !!}
                    </div>
                    <div class="col-md-6">
                        {!! CoralsForm::email('email', 'Sorteos::attributes.asignado.email', false, $asignado->email) !!}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        {!! CoralsForm::text('phone', 'Sorteos::attributes.asignado.phone', false, $asignado->phone) !!}
                    </div>
                    <div class="col-md-4">
                        {!! CoralsForm::select('type', 'Sorteos::attributes.asignado.type', ['persona' => 'Persona', 'institucion' => 'Institución'], false, $asignado->type ?? 'persona') !!}
                    </div>
                    <div class="col-md-4">
                        {!! CoralsForm::select('status', 'Sorteos::attributes.asignado.status',
                            collect(\Corals\Modules\Sorteos\Enums\AsignadoStatus::cases())->mapWithKeys(fn($c)=>[$c->value=>$c->label()])->all(),
                            false, $asignado->status?->value ?? 'active') !!}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        {!! CoralsForm::textarea('notes', 'Sorteos::attributes.asignado.notes', false, $asignado->notes, ['rows' => 3]) !!}
                    </div>
                </div>

                {!! CoralsForm::customFields($asignado) !!}

                <div class="row">
                    <div class="col-md-12">
                        {!! CoralsForm::formButtons() !!}
                    </div>
                </div>

                {!! CoralsForm::closeForm($asignado) !!}
            @endcomponent
        </div>
    </div>
@endsection
