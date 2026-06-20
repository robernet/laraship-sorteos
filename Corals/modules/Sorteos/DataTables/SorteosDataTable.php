<?php

namespace Corals\Modules\Sorteos\DataTables;

use Corals\Foundation\DataTables\BaseDataTable;
use Corals\Modules\Sorteos\Models\Sorteo;
use Corals\Modules\Sorteos\Transformers\SorteoTransformer;
use Yajra\DataTables\EloquentDataTable;

class SorteosDataTable extends BaseDataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $this->setResourceUrl(config('sorteos.models.sorteo.resource_url'));

        $dataTable = new EloquentDataTable($query);

        return $dataTable->setTransformer(new SorteoTransformer());
    }

    /**
     * Get query source of dataTable.
     * @param Sorteo $model
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function query(Sorteo $model)
    {
        return $model->newQuery();
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            'id'           => ['visible' => false],
            'name'         => ['title' => trans('Sorteos::attributes.sorteo.name')],
            'slug'         => ['title' => trans('Sorteos::attributes.sorteo.slug')],
            'status'       => ['title' => trans('Sorteos::attributes.sorteo.status')],
            'ticket_price' => ['title' => trans('Sorteos::attributes.sorteo.ticket_price')],
            'is_public'    => [
                'title'      => trans('Sorteos::attributes.sorteo.is_public'),
                'orderable'  => false,
                'searchable' => false,
            ],
            'starts_at'    => ['title' => trans('Sorteos::attributes.sorteo.starts_at')],
            'ends_at'      => ['title' => trans('Sorteos::attributes.sorteo.ends_at')],
            'draw_date'    => ['title' => trans('Sorteos::attributes.sorteo.draw_date')],
            'created_at'   => ['title' => trans('Corals::attributes.created_at')],
        ];
    }

    public function getFilters()
    {
        return [
            'name'      => [
                'title'     => trans('Sorteos::attributes.sorteo.name'),
                'class'     => 'col-md-3',
                'type'      => 'text',
                'condition' => 'like',
                'active'    => true,
            ],
            'status'    => [
                'title'   => trans('Sorteos::attributes.sorteo.status'),
                'class'   => 'col-md-3',
                'type'    => 'select',
                'options' => ['' => '— Todos —'] + collect(\Corals\Modules\Sorteos\Enums\SorteoStatus::cases())
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
                'permission'   => 'Sorteos::sorteo.delete',
                'confirmation' => trans('Corals::labels.confirmation.title'),
            ],
        ];
    }

    protected function getOptions()
    {
        return ['resource_url' => url(config('sorteos.models.sorteo.resource_url'))];
    }
}
