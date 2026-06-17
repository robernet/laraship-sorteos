<?php

namespace Corals\Modules\Sorteos\Http\Controllers;

use Corals\Foundation\Http\Controllers\BaseController;
use Corals\Modules\Sorteos\DataTables\AuditDataTable;
use Illuminate\Http\Request;

class AuditController extends BaseController
{
    public function __construct()
    {
        $this->resource_url   = 'sorteos/audit';
        $this->title          = 'Auditoría';
        $this->title_singular = 'Registro de Actividad';
        parent::__construct();
    }

    public function index(Request $request, AuditDataTable $dataTable)
    {
        $this->setViewSharedData(['title_singular' => 'Registro de Actividad — Sorteos']);

        return $dataTable->render('Sorteos::audit.index');
    }
}
