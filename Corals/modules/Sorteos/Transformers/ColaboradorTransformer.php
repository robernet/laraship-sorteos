<?php

namespace Corals\Modules\Sorteos\Transformers;

use Corals\Foundation\Transformers\BaseTransformer;
use Corals\Modules\Sorteos\Models\Colaborador;

class ColaboradorTransformer extends BaseTransformer
{
    public function __construct($extras = [])
    {
        $this->resource_url = config('sorteos.models.colaborador.resource_url');

        parent::__construct($extras);
    }

    public function transform(Colaborador $colaborador)
    {
        $statusBadge = $colaborador->status
            ? '<span class="badge ' . $colaborador->status->badgeClass() . '">' . $colaborador->status->label() . '</span>'
            : '-';

        $typeLabel = $colaborador->type === 'institucion' ? 'Institución' : 'Persona';

        $transformedArray = [
            'id'             => $colaborador->id,
            'name'           => HtmlElement('a', ['href' => $colaborador->getShowURL()], $colaborador->name),
            'email'          => $colaborador->email ?? '-',
            'phone'          => $colaborador->phone ?? '-',
            'type'           => $typeLabel,
            'status'         => $statusBadge,
            'carteras_count' => $colaborador->carteras_count ?? 0,
            'created_at'     => format_date($colaborador->created_at),
            'action'         => $this->actions($colaborador),
        ];

        return parent::transformResponse($transformedArray);
    }
}
