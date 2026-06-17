@extends('layouts.crud.index')

@section('content_header')
    @component('components.content_header')
        @slot('page_title') {{ $title_singular }} @endslot
        @slot('breadcrumb') {{ Breadcrumbs::render('sorteos_audit') }} @endslot
    @endcomponent
@endsection

@section('content')
    @component('components.box')
        @slot('box_title') Registro de Actividad — Módulo Sorteos @endslot

        {!! $dataTable->table(['id' => 'sorteos-audit-table', 'class' => 'table table-striped table-hover']) !!}
    @endcomponent
@endsection

@push('js')
    {!! $dataTable->scripts() !!}
@endpush
