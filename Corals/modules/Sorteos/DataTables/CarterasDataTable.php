<?php

namespace Corals\Modules\Sorteos\DataTables;

use Corals\Foundation\DataTables\BaseDataTable;
use Corals\Modules\Sorteos\Models\Cartera;
use Corals\Modules\Sorteos\Transformers\CarteraTransformer;
use Yajra\DataTables\EloquentDataTable;

class CarterasDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        $this->setResourceUrl(config('sorteos.models.cartera.resource_url'));

        $dataTable = new EloquentDataTable($query);

        return $dataTable->setTransformer(new CarteraTransformer());
    }

    public function query(Cartera $model)
    {
        return $model->newQuery()->with('sorteo')->withCount('boletos');
    }

    protected function getColumns()
    {
        return [
            'id'             => ['visible' => false],
            'code'           => ['title' => trans('Sorteos::attributes.cartera.code')],
            'sorteo'         => ['title' => trans('Sorteos::attributes.cartera.sorteo_id'), 'orderable' => false, 'searchable' => false],
            'status'         => ['title' => trans('Sorteos::attributes.cartera.status')],
            'physical_start' => ['title' => trans('Sorteos::attributes.cartera.physical_start')],
            'physical_end'   => ['title' => trans('Sorteos::attributes.cartera.physical_end')],
            'digital_start'  => ['title' => trans('Sorteos::attributes.cartera.digital_start')],
            'digital_end'    => ['title' => trans('Sorteos::attributes.cartera.digital_end')],
            'boletos_count'  => ['title' => trans('Sorteos::attributes.cartera.boletos_count'), 'orderable' => false, 'searchable' => false],
            'created_at'     => ['title' => trans('Corals::attributes.created_at')],
        ];
    }

    public function getFilters()
    {
        return [
            'code'   => [
                'title'     => trans('Sorteos::attributes.cartera.code'),
                'class'     => 'col-md-3',
                'type'      => 'text',
                'condition' => 'like',
                'active'    => true,
            ],
            'status' => [
                'title'   => trans('Sorteos::attributes.cartera.status'),
                'class'   => 'col-md-3',
                'type'    => 'select',
                'options' => collect(\Corals\Modules\Sorteos\Enums\CarteraStatus::cases())
                    ->mapWithKeys(fn($case) => [$case->value => $case->label()])
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
                'permission'   => 'Sorteos::cartera.delete',
                'confirmation' => trans('Corals::labels.confirmation.title'),
            ],
        ];
    }

    protected function getOptions()
    {
        return ['resource_url' => url(config('sorteos.models.cartera.resource_url'))];
    }
}
