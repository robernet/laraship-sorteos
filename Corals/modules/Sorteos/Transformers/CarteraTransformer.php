<?php

namespace Corals\Modules\Sorteos\Transformers;

use Corals\Foundation\Transformers\BaseTransformer;
use Corals\Modules\Sorteos\Models\Cartera;

class CarteraTransformer extends BaseTransformer
{
    public function __construct($extras = [])
    {
        $this->resource_url = config('sorteos.models.cartera.resource_url');

        parent::__construct($extras);
    }

    /**
     * @param Cartera $cartera
     * @return array
     * @throws \Throwable
     */
    public function transform(Cartera $cartera)
    {
        $statusBadge = $cartera->status
            ? '<span class="badge ' . $cartera->status->badgeClass() . '">' . $cartera->status->label() . '</span>'
            : '-';

        $transformedArray = [
            'id'             => $cartera->id,
            'code'           => HtmlElement('a', ['href' => $cartera->getShowURL()], $cartera->code),
            'sorteo'         => $cartera->sorteo?->name ?? '-',
            'status'         => $statusBadge,
            'physical_start' => $cartera->physical_start,
            'physical_end'   => $cartera->physical_end,
            'digital_start'  => $cartera->digital_start,
            'digital_end'    => $cartera->digital_end,
            'boletos_count'  => $cartera->boletos_count ?? 0,
            'created_at'     => format_date($cartera->created_at),
            'action'         => $this->actions($cartera),
        ];

        return parent::transformResponse($transformedArray);
    }
}
