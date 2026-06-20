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
            'hashed_id'      => HtmlElement('a', ['href' => $order->getShowURL()], '#' . $order->hashed_id),
            'buyer_name'     => $order->buyer_name,
            'buyer_email'    => $order->buyer_email,
            'buyer_phone'    => $order->buyer_phone,
            'sorteo'         => $order->sorteo?->name ?? '-',
            'colaborador'    => $order->colaborador
                ? HtmlElement('a', ['href' => url('sorteos/colaboradores/' . $order->colaborador->hashed_id)], $order->colaborador->name)
                : '—',
            'payment_method' => $order->payment_method?->label() ?? '-',
            'status'         => $statusBadge,
            'total_amount'   => '$' . number_format($order->total_amount, 2),
            'items_count'    => $order->items_count ?? 0,
            'created_at'     => format_date($order->created_at),
            'action'         => $this->actions($order),
        ];

        return parent::transformResponse($transformedArray);
    }
}
