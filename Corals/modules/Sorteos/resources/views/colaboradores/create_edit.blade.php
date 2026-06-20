@extends('layouts.crud.create_edit')

@section('content_header')
    @component('components.content_header')
        @slot('page_title')
            {{ $title_singular }}
        @endslot
        @slot('breadcrumb')
            {{ Breadcrumbs::render('sorteos_colaborador_create_edit') }}
        @endslot
    @endcomponent
@endsection

@section('content')
    @parent
    <div class="row">
        <div class="col-md-12">
            @component('components.box')
                {!! CoralsForm::openForm($colaborador) !!}

                <div class="row">
                    <div class="col-md-6">
                        {!! CoralsForm::text('name', 'Sorteos::attributes.colaborador.name', true, $colaborador->name) !!}
                    </div>
                    <div class="col-md-6">
                        {!! CoralsForm::email('email', 'Sorteos::attributes.colaborador.email', false, $colaborador->email) !!}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        {!! CoralsForm::text('phone', 'Sorteos::attributes.colaborador.phone', false, $colaborador->phone) !!}
                    </div>
                    <div class="col-md-4">
                        {!! CoralsForm::select('type', 'Sorteos::attributes.colaborador.type', ['persona' => 'Persona', 'institucion' => 'Institución'], false, $colaborador->type ?? 'persona') !!}
                    </div>
                    <div class="col-md-4">
                        {!! CoralsForm::select('status', 'Sorteos::attributes.colaborador.status',
                            collect(\Corals\Modules\Sorteos\Enums\ColaboradorStatus::cases())->mapWithKeys(fn($c)=>[$c->value=>$c->label()])->all(),
                            false, $colaborador->status?->value ?? 'active') !!}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        {!! CoralsForm::textarea('notes', 'Sorteos::attributes.colaborador.notes', false, $colaborador->notes, ['rows' => 3]) !!}
                    </div>
                </div>

                {!! CoralsForm::customFields($colaborador) !!}

                <div class="row">
                    <div class="col-md-12">
                        {!! CoralsForm::formButtons() !!}
                    </div>
                </div>

                {!! CoralsForm::closeForm($colaborador) !!}
            @endcomponent
        </div>
    </div>
@endsection
