<?php

namespace Corals\Modules\Sorteos\DataTables;

use Corals\Foundation\DataTables\BaseDataTable;
use Corals\Modules\Sorteos\Models\Asignado;
use Corals\Modules\Sorteos\Transformers\AsignadoTransformer;
use Yajra\DataTables\EloquentDataTable;

class AsignadosDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        $this->setResourceUrl(config('sorteos.models.asignado.resource_url'));

        $dataTable = new EloquentDataTable($query);

        return $dataTable->setTransformer(new AsignadoTransformer());
    }

    public function query(Asignado $model)
    {
        return $model->newQuery()->withCount('carteras');
    }

    protected function getColumns()
    {
        return [
            'id'             => ['visible' => false],
            'name'           => ['title' => trans('Sorteos::attributes.asignado.name')],
            'email'          => ['title' => trans('Sorteos::attributes.asignado.email')],
            'phone'          => ['title' => trans('Sorteos::attributes.asignado.phone')],
            'type'           => ['title' => trans('Sorteos::attributes.asignado.type')],
            'status'         => ['title' => trans('Sorteos::attributes.asignado.status')],
            'carteras_count' => ['title' => trans('Sorteos::attributes.asignado.carteras_count'), 'orderable' => false, 'searchable' => false],
            'created_at'     => ['title' => trans('Corals::attributes.created_at')],
        ];
    }

    public function getFilters()
    {
        return [
            'name'   => [
                'title'     => trans('Sorteos::attributes.asignado.name'),
                'class'     => 'col-md-4',
                'type'      => 'text',
                'condition' => 'like',
                'active'    => true,
            ],
            'status' => [
                'title'   => trans('Sorteos::attributes.asignado.status'),
                'class'   => 'col-md-3',
                'type'    => 'select',
                'options' => collect(\Corals\Modules\Sorteos\Enums\AsignadoStatus::cases())
                    ->mapWithKeys(fn($c) => [$c->value => $c->label()])
                    ->all(),
                'active'  => true,
            ],
        ];
    }

    protected function getBulkActions()
    {
        return [
            'delete' => [
                'title'        => trans('Corals::labels.delete'),
                'permission'   => 'Sorteos::asignado.delete',
                'confirmation' => trans('Corals::labels.confirmation.title'),
            ],
        ];
    }

    protected function getOptions()
    {
        return ['resource_url' => url(config('sorteos.models.asignado.resource_url'))];
    }
}
