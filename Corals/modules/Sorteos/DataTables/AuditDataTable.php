<?php

namespace Corals\Modules\Sorteos\DataTables;

use Corals\Foundation\DataTables\BaseDataTable;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\EloquentDataTable;

class AuditDataTable extends BaseDataTable
{
    protected $sorteos_subject_types = [
        'Corals\Modules\Sorteos\Models\Sorteo',
        'Corals\Modules\Sorteos\Models\Cartera',
        'Corals\Modules\Sorteos\Models\Boleto',
        'Corals\Modules\Sorteos\Models\Order',
        'Corals\Modules\Sorteos\Models\OrderItem',
    ];

    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('created_at', fn($row) => $row->created_at?->format('d/m/Y H:i:s'))
            ->editColumn('subject_type', fn($row) => class_basename($row->subject_type ?? ''))
            ->editColumn('causer_id', fn($row) => optional($row->causer)->name ?? 'Sistema')
            ->editColumn('description', fn($row) => ucfirst($row->description))
            ->editColumn('properties', function ($row) {
                $props = $row->properties ?? [];
                if (empty($props)) {
                    return '—';
                }
                $parts = [];
                if (!empty($props['attributes'])) {
                    $parts[] = '<strong>Nuevo:</strong> ' . implode(', ', array_map(
                        fn($k, $v) => "{$k}: " . (is_array($v) ? json_encode($v) : $v),
                        array_keys($props['attributes']),
                        $props['attributes']
                    ));
                }
                if (!empty($props['old'])) {
                    $parts[] = '<strong>Anterior:</strong> ' . implode(', ', array_map(
                        fn($k, $v) => "{$k}: " . (is_array($v) ? json_encode($v) : $v),
                        array_keys($props['old']),
                        $props['old']
                    ));
                }
                return '<small>' . implode('<br>', $parts) . '</small>';
            })
            ->rawColumns(['properties']);
    }

    public function query(): \Illuminate\Database\Eloquent\Builder
    {
        $query = Activity::with('causer')
            ->whereIn('subject_type', $this->sorteos_subject_types)
            ->select('activity_log.*');

        if (request()->filled('description')) {
            $query->where('description', 'like', '%' . request('description') . '%');
        }
        if (request()->filled('subject_type')) {
            $query->where('subject_type', request('subject_type'));
        }

        return $query;
    }

    public function html(): \Yajra\DataTables\Html\Builder
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc')
            ->parameters($this->getBuilderParameters());
    }

    protected function getColumns(): array
    {
        return [
            ['data' => 'created_at',   'title' => 'Fecha', 'width' => '140px'],
            ['data' => 'subject_type', 'title' => 'Modelo'],
            ['data' => 'subject_id',   'title' => 'ID'],
            ['data' => 'description',  'title' => 'Acción'],
            ['data' => 'causer_id',    'title' => 'Usuario'],
            ['data' => 'properties',   'title' => 'Cambios', 'orderable' => false, 'searchable' => false],
        ];
    }

    protected function getFilters(): array
    {
        return [
            'description'  => ['title' => 'Acción', 'class' => 'col-md-3', 'type' => 'text'],
            'subject_type' => ['title' => 'Modelo', 'class' => 'col-md-3', 'type' => 'select',
                'options' => [
                    'Corals\Modules\Sorteos\Models\Sorteo'    => 'Sorteo',
                    'Corals\Modules\Sorteos\Models\Cartera'   => 'Cartera',
                    'Corals\Modules\Sorteos\Models\Boleto'    => 'Boleto',
                    'Corals\Modules\Sorteos\Models\Order'     => 'Orden',
                    'Corals\Modules\Sorteos\Models\OrderItem' => 'Ítem de Orden',
                ]],
        ];
    }
}
