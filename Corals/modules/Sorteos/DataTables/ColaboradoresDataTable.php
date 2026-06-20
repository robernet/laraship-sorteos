<?php

namespace Corals\Modules\Sorteos\DataTables;

use Corals\Foundation\DataTables\BaseDataTable;
use Corals\Modules\Sorteos\Models\Colaborador;
use Corals\Modules\Sorteos\Transformers\ColaboradorTransformer;
use Yajra\DataTables\EloquentDataTable;

class ColaboradoresDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        $this->setResourceUrl(config('sorteos.models.colaborador.resource_url'));

        $dataTable = new EloquentDataTable($query);

        return $dataTable->setTransformer(new ColaboradorTransformer());
    }

    public function query(Colaborador $model)
    {
        return $model->newQuery()->withCount('carteras');
    }

    protected function getColumns()
    {
        return [
            'id'             => ['visible' => false],
            'name'           => ['title' => trans('Sorteos::attributes.colaborador.name')],
            'email'          => ['title' => trans('Sorteos::attributes.colaborador.email')],
            'phone'          => ['title' => trans('Sorteos::attributes.colaborador.phone')],
            'type'           => ['title' => trans('Sorteos::attributes.colaborador.type')],
            'status'         => ['title' => trans('Sorteos::attributes.colaborador.status')],
            'carteras_count' => ['title' => trans('Sorteos::attributes.colaborador.carteras_count'), 'orderable' => false, 'searchable' => false],
            'created_at'     => ['title' => trans('Corals::attributes.created_at')],
        ];
    }

    public function getFilters()
    {
        return [
            'name'   => [
                'class'     => 'col-md-4',
                'type'      => 'text',
                'condition' => 'like',
                'active'    => true,
                'html'      => \CoralsForm::text('name', trans('Sorteos::attributes.colaborador.name'), false, request('name'), ['class' => 'filter']),
            ],
            'status' => [
                'class'  => 'col-md-3',
                'type'   => 'select',
                'active' => true,
                'html'   => \CoralsForm::select('status', trans('Sorteos::attributes.colaborador.status'), ['' => '— Todos —'] + collect(\Corals\Modules\Sorteos\Enums\ColaboradorStatus::cases())->mapWithKeys(fn($c) => [$c->value => $c->label()])->all(), false, request('status'), ['class' => 'filter']),
            ],
        ];
    }

    protected function getBulkActions()
    {
        return [
            'delete' => [
                'title'        => trans('Corals::labels.delete'),
                'permission'   => 'Sorteos::colaborador.delete',
                'confirmation' => trans('Corals::labels.confirmation.title'),
            ],
        ];
    }

    protected function getOptions()
    {
        return ['resource_url' => url(config('sorteos.models.colaborador.resource_url'))];
    }
}
