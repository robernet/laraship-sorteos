<?php

namespace Corals\Modules\Sorteos\DataTables;

use Corals\Foundation\DataTables\BaseDataTable;
use Corals\Modules\Sorteos\Models\Order;
use Corals\Modules\Sorteos\Transformers\OrderTransformer;
use Yajra\DataTables\EloquentDataTable;

class OrdersDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        $this->setResourceUrl(config('sorteos.models.order.resource_url'));

        $dataTable = new EloquentDataTable($query);

        return $dataTable->setTransformer(new OrderTransformer());
    }

    public function query(Order $model)
    {
        return $model->newQuery()->with(['sorteo', 'colaborador'])->withCount('items');
    }

    protected function getColumns()
    {
        return [
            'id'             => ['visible' => false],
            'hashed_id'      => ['title' => '#Orden', 'orderable' => false, 'searchable' => false],
            'buyer_name'     => ['title' => trans('Sorteos::attributes.order.buyer_name')],
            'buyer_email'    => ['title' => trans('Sorteos::attributes.order.buyer_email')],
            'buyer_phone'    => ['title' => trans('Sorteos::attributes.order.buyer_phone')],
            'sorteo'         => ['title' => trans('Sorteos::attributes.order.sorteo_id'), 'orderable' => false, 'searchable' => false],
            'colaborador'    => ['title' => trans('Sorteos::attributes.order.colaborador_id'), 'orderable' => false, 'searchable' => false],
            'payment_method' => ['title' => trans('Sorteos::attributes.order.payment_method'), 'orderable' => false, 'searchable' => false],
            'status'         => ['title' => trans('Sorteos::attributes.order.status')],
            'total_amount'   => ['title' => trans('Sorteos::attributes.order.total_amount')],
            'items_count'    => ['title' => trans('Sorteos::attributes.order.items_count'), 'orderable' => false, 'searchable' => false],
            'created_at'     => ['title' => trans('Corals::attributes.created_at')],
        ];
    }

    public function getFilters()
    {
        return [
            'buyer_name'  => [
                'title'     => trans('Sorteos::attributes.order.buyer_name'),
                'class'     => 'col-md-3',
                'type'      => 'text',
                'condition' => 'like',
                'active'    => true,
            ],
            'buyer_email' => [
                'title'     => trans('Sorteos::attributes.order.buyer_email'),
                'class'     => 'col-md-3',
                'type'      => 'text',
                'condition' => 'like',
                'active'    => true,
            ],
            'status'      => [
                'title'   => trans('Sorteos::attributes.order.status'),
                'class'   => 'col-md-3',
                'type'    => 'select',
                'options' => ['' => '— Todos —'] + collect(\Corals\Modules\Sorteos\Enums\OrderStatus::cases())
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
                'permission'   => 'Sorteos::order.delete',
                'confirmation' => trans('Corals::labels.confirmation.title'),
            ],
        ];
    }

    protected function getOptions()
    {
        return ['resource_url' => url(config('sorteos.models.order.resource_url'))];
    }
}
