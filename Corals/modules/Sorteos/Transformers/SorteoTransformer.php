<?php

namespace Corals\Modules\Sorteos\Transformers;

use Corals\Foundation\Transformers\BaseTransformer;
use Corals\Modules\Sorteos\Enums\SorteoStatus;
use Corals\Modules\Sorteos\Models\Sorteo;

class SorteoTransformer extends BaseTransformer
{
    public function __construct($extras = [])
    {
        $this->resource_url = config('sorteos.models.sorteo.resource_url');

        parent::__construct($extras);
    }

    /**
     * @param Sorteo $sorteo
     * @return array
     * @throws \Throwable
     */
    public function transform(Sorteo $sorteo)
    {
        $statusBadge = $sorteo->status
            ? '<span class="badge ' . $sorteo->status->badgeClass() . '">' . $sorteo->status->label() . '</span>'
            : '-';

        $isPublicIcon = $sorteo->is_public
            ? '<i class="fa fa-check text-success"></i>'
            : '<i class="fa fa-times text-danger"></i>';

        $transformedArray = [
            'id'           => $sorteo->id,
            'name'         => HtmlElement('a', ['href' => $sorteo->getShowURL()], $sorteo->name),
            'slug'         => $sorteo->slug,
            'status'       => $statusBadge,
            'ticket_price' => '$' . number_format((float) $sorteo->ticket_price, 2),
            'tiraje'       => $sorteo->tiraje ? number_format($sorteo->tiraje) : '-',
            'is_public'    => $isPublicIcon,
            'starts_at'    => format_date($sorteo->starts_at),
            'ends_at'      => format_date($sorteo->ends_at),
            'draw_date'    => format_date($sorteo->draw_date),
            'created_at'   => format_date($sorteo->created_at),
            'action'       => $this->actions($sorteo) . $this->statusActions($sorteo),
        ];

        return parent::transformResponse($transformedArray);
    }

    private function statusActions(Sorteo $sorteo): string
    {
        if (user()->cannot('update', $sorteo)) {
            return '';
        }

        $baseUrl = url(config('sorteos.models.sorteo.resource_url') . '/' . $sorteo->hashed_id . '/change-status');

        $buttons = '';

        if ($sorteo->status !== SorteoStatus::Active) {
            $buttons .= ' <a href="' . $baseUrl . '/active"'
                . ' data-action="post" data-table=".dataTableBuilder"'
                . ' class="btn btn-xs btn-success"'
                . ' title="Activar"><i class="fa fa-play"></i></a>';
        }

        if ($sorteo->status !== SorteoStatus::Paused) {
            $buttons .= ' <a href="' . $baseUrl . '/paused"'
                . ' data-action="post" data-table=".dataTableBuilder"'
                . ' class="btn btn-xs btn-warning"'
                . ' title="Pausar"><i class="fa fa-pause"></i></a>';
        }

        if ($sorteo->status !== SorteoStatus::Finished) {
            $buttons .= ' <a href="' . $baseUrl . '/finished"'
                . ' data-action="post" data-table=".dataTableBuilder"'
                . ' class="btn btn-xs btn-secondary"'
                . ' title="Finalizar"><i class="fa fa-stop"></i></a>';
        }

        return $buttons;
    }
}
