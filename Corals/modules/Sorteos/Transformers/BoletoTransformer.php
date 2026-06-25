<?php

namespace Corals\Modules\Sorteos\Transformers;

use Corals\Foundation\Transformers\BaseTransformer;
use Corals\Modules\Sorteos\Models\Boleto;

class BoletoTransformer extends BaseTransformer
{
    public function __construct($extras = [])
    {
        $this->resource_url = config('sorteos.models.boleto.resource_url');

        parent::__construct($extras);
    }

    /**
     * @param Boleto $boleto
     * @return array
     * @throws \Throwable
     */
    public function transform(Boleto $boleto)
    {
        $statusBadge = $boleto->status
            ? '<span class="badge ' . $boleto->status->badgeClass() . '">' . $boleto->status->label() . '</span>'
            : '-';

        $transformedArray = [
            'id'              => $boleto->id,
            'digital_number'  => HtmlElement('a', ['href' => $boleto->getShowURL()], '#' . $boleto->digital_number),
            'physical_number' => $boleto->physical_number,
            'cartera'         => $boleto->cartera?->code ?? '-',
            'sorteo'          => $boleto->sorteo?->name ?? '-',
            'status'          => $statusBadge,
            'buyer'           => (function () use ($boleto) {
                $order = $boleto->orderItems->first()?->order;
                if (!$order) { return '-'; }
                return HtmlElement('a', ['href' => url('sorteos/orders/' . $order->hashed_id)], e($order->buyer_name));
            })(),
            'created_at'      => format_date($boleto->created_at),
            'action'          => $this->actions($boleto),
        ];

        return parent::transformResponse($transformedArray);
    }
}
