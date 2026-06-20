<?php

namespace Corals\Modules\Sorteos\DataTables;

use Corals\Foundation\DataTables\BaseDataTable;
use Corals\Modules\Sorteos\Models\Boleto;
use Corals\Modules\Sorteos\Transformers\BoletoTransformer;
use Yajra\DataTables\EloquentDataTable;

class BoletosDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        $this->setResourceUrl(config('sorteos.models.boleto.resource_url'));

        $dataTable = new EloquentDataTable($query);

        return $dataTable->setTransformer(new BoletoTransformer());
    }

    public function query(Boleto $model)
    {
        return $model->newQuery()->with(['cartera', 'sorteo']);
    }

    protected function getColumns()
    {
        return [
            'id'              => ['visible' => false],
            'digital_number'  => ['title' => trans('Sorteos::attributes.boleto.digital_number')],
            'physical_number' => ['title' => trans('Sorteos::attributes.boleto.physical_number')],
            'cartera'         => ['title' => trans('Sorteos::attributes.boleto.cartera_id'), 'orderable' => false, 'searchable' => false],
            'sorteo'          => ['title' => trans('Sorteos::attributes.boleto.sorteo_id'), 'orderable' => false, 'searchable' => false],
            'status'          => ['title' => trans('Sorteos::attributes.boleto.status')],
            'created_at'      => ['title' => trans('Corals::attributes.created_at')],
        ];
    }

    public function getFilters()
    {
        return [
            'digital_number' => [
                'title'     => trans('Sorteos::attributes.boleto.digital_number'),
                'class'     => 'col-md-3',
                'type'      => 'text',
                'condition' => '=',
                'active'    => true,
            ],
            'status'         => [
                'title'   => trans('Sorteos::attributes.boleto.status'),
                'class'   => 'col-md-3',
                'type'    => 'select',
                'options' => ['' => '— Todos —'] + collect(\Corals\Modules\Sorteos\Enums\BoletoStatus::cases())
                    ->mapWithKeys(fn($case) => [$case->value => $case->label()])
                    ->all(),
                'active'  => true,
            ],
        ];
    }

    protected function getBulkActions()
    {
        return [];
    }

    protected function getOptions()
    {
        return ['resource_url' => url(config('sorteos.models.boleto.resource_url'))];
    }
}
