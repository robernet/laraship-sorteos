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
        $colaboradores = ['' => '— Todos —'] + \Corals\Modules\Sorteos\Models\Colaborador::orderBy('name')
            ->pluck('name', 'id')
            ->all();

        $paymentOptions = ['' => '— Todos —'] + collect(\Corals\Modules\Sorteos\Enums\PaymentMethod::cases())
            ->mapWithKeys(fn($c) => [$c->value => $c->label()])->all();

        $statusOptions = ['' => '— Todos —'] + collect(\Corals\Modules\Sorteos\Enums\OrderStatus::cases())
            ->mapWithKeys(fn($c) => [$c->value => $c->label()])->all();

        return [
            'buyer_name'     => [
                'class'     => 'col-md-3',
                'type'      => 'text',
                'condition' => 'like',
                'active'    => true,
                'html'      => \CoralsForm::text('buyer_name', trans('Sorteos::attributes.order.buyer_name'), false, request('buyer_name'), ['class' => 'filter']),
            ],
            'buyer_email'    => [
                'class'     => 'col-md-3',
                'type'      => 'text',
                'condition' => 'like',
                'active'    => true,
                'html'      => \CoralsForm::text('buyer_email', trans('Sorteos::attributes.order.buyer_email'), false, request('buyer_email'), ['class' => 'filter']),
            ],
            'colaborador_id' => [
                'class'  => 'col-md-3',
                'type'   => 'select',
                'active' => true,
                'html'   => \CoralsForm::select('colaborador_id', trans('Sorteos::attributes.order.colaborador_id'), $colaboradores, false, request('colaborador_id'), ['class' => 'filter']),
            ],
            'payment_method' => [
                'class'  => 'col-md-3',
                'type'   => 'select',
                'active' => true,
                'html'   => \CoralsForm::select('payment_method', trans('Sorteos::attributes.order.payment_method'), $paymentOptions, false, request('payment_method'), ['class' => 'filter']),
            ],
            'status'         => [
                'class'  => 'col-md-3',
                'type'   => 'select',
                'active' => true,
                'html'   => \CoralsForm::select('status', trans('Sorteos::attributes.order.status'), $statusOptions, false, request('status'), ['class' => 'filter']),
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
