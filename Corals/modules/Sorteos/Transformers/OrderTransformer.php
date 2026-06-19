<?php

namespace Corals\Modules\Sorteos\Transformers;

use Corals\Foundation\Transformers\BaseTransformer;
use Corals\Modules\Sorteos\Enums\OrderStatus;
use Corals\Modules\Sorteos\Models\Order;

class OrderTransformer extends BaseTransformer
{
    public function __construct($extras = [])
    {
        $this->resource_url = config('sorteos.models.order.resource_url');

        parent::__construct($extras);
    }

    /**
     * @param Order $order
     * @return array
     * @throws \Throwable
     */
    public function transform(Order $order)
    {
        $statusBadge = $order->status
            ? '<span class="badge ' . $order->status->badgeClass() . '">' . $order->status->label() . '</span>'
            : '-';

        $transformedArray = [
            'id'             => $order->id,
            'buyer_name'     => HtmlElement('a', ['href' => $order->getShowURL()], $order->buyer_name),
            'buyer_email'    => $order->buyer_email,
            'buyer_phone'    => $order->buyer_phone,
            'sorteo'         => $order->sorteo?->name ?? '-',
            'payment_method' => $order->payment_method?->label() ?? '-',
            'status'         => $statusBadge,
            'total_amount'   => '$' . number_format($order->total_amount, 2),
            'items_count'    => $order->items_count ?? 0,
            'created_at'     => format_date($order->created_at),
            'action'         => $this->actions($order) . $this->statusActions($order),
        ];

        return parent::transformResponse($transformedArray);
    }

    private function statusActions(Order $order): string
    {
        if (user()->cannot('update', $order)) {
            return '';
        }

        $html = '';

        if ($order->isPending()) {
            $confirmUrl = url(config('sorteos.models.order.resource_url') . '/' . $order->hashed_id . '/confirm');
            $html .= HtmlElement('a', [
                'href'         => $confirmUrl,
                'data-action'  => 'post',
                'data-table'   => '.dataTableBuilder',
                'class'        => 'btn btn-xs btn-success',
                'title'        => trans('Sorteos::attributes.order.confirm'),
                'data-confirmation' => '¿Confirmar el pago de esta orden?',
            ], '<i class="fa fa-check"></i>');
        }

        if (!$order->isCancelled()) {
            $cancelUrl = url(config('sorteos.models.order.resource_url') . '/' . $order->hashed_id . '/cancel');
            $html .= HtmlElement('a', [
                'href'             => $cancelUrl,
                'data-action'      => 'post',
                'data-table'       => '.dataTableBuilder',
                'class'            => 'btn btn-xs btn-danger',
                'title'            => trans('Sorteos::attributes.order.cancel'),
                'data-confirmation' => '¿Cancelar esta orden?',
            ], '<i class="fa fa-times"></i>');
        }

        return $html;
    }
}
