<?php

namespace Corals\Modules\Sorteos\Transformers;

use Corals\Foundation\Transformers\BaseTransformer;
use Corals\Modules\Sorteos\Models\Asignado;

class AsignadoTransformer extends BaseTransformer
{
    public function __construct($extras = [])
    {
        $this->resource_url = config('sorteos.models.asignado.resource_url');

        parent::__construct($extras);
    }

    public function transform(Asignado $asignado)
    {
        $statusBadge = $asignado->status
            ? '<span class="badge ' . $asignado->status->badgeClass() . '">' . $asignado->status->label() . '</span>'
            : '-';

        $typeLabel = $asignado->type === 'institucion' ? 'Institución' : 'Persona';

        $transformedArray = [
            'id'             => $asignado->id,
            'name'           => HtmlElement('a', ['href' => $asignado->getShowURL()], $asignado->name),
            'email'          => $asignado->email ?? '-',
            'phone'          => $asignado->phone ?? '-',
            'type'           => $typeLabel,
            'status'         => $statusBadge,
            'carteras_count' => $asignado->carteras_count ?? 0,
            'created_at'     => format_date($asignado->created_at),
            'action'         => $this->actions($asignado),
        ];

        return parent::transformResponse($transformedArray);
    }
}
