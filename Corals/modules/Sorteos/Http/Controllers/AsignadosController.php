<?php

namespace Corals\Modules\Sorteos\Http\Controllers;

use Corals\Foundation\Http\Controllers\BaseController;
use Corals\Modules\Sorteos\DataTables\AsignadosDataTable;
use Corals\Modules\Sorteos\Enums\CarteraStatus;
use Corals\Modules\Sorteos\Http\Requests\AsignadoRequest;
use Corals\Modules\Sorteos\Models\Asignado;
use Corals\Modules\Sorteos\Models\Cartera;
use Corals\Modules\Sorteos\Models\Sorteo;
use Corals\Modules\Sorteos\Services\AsignadoService;
use Illuminate\Http\Request;

class AsignadosController extends BaseController
{
    public function __construct(protected AsignadoService $asignadoService)
    {
        $this->resource_url   = config('sorteos.models.asignado.resource_url');
        $this->resource_model = new Asignado();
        $this->title          = trans('Sorteos::module.asignado.title');
        $this->title_singular = trans('Sorteos::module.asignado.title_singular');

        parent::__construct();
    }

    public function index(AsignadoRequest $request, AsignadosDataTable $dataTable)
    {
        return $dataTable->render('Sorteos::asignados.index');
    }

    public function create(AsignadoRequest $request)
    {
        $asignado = new Asignado();

        $this->setViewSharedData([
            'title_singular' => trans('Corals::labels.create_title', ['title' => $this->title_singular]),
        ]);

        return view('Sorteos::asignados.create_edit')->with(compact('asignado'));
    }

    public function store(AsignadoRequest $request)
    {
        try {
            $asignado = $this->asignadoService->store($request, Asignado::class);
            flash(trans('Corals::messages.success.created', ['item' => $this->title_singular]))->success();
        } catch (\Exception $e) {
            log_exception($e, Asignado::class, 'store');
        }

        return redirectTo(isset($asignado) ? $asignado->getShowURL() : $this->resource_url);
    }

    public function show(AsignadoRequest $request, Asignado $asignado)
    {
        $asignado->load('carteras.sorteo', 'carteras.boletos');

        $sorteos           = Sorteo::pluck('name', 'id');
        $assignableCarteras = Cartera::whereNull('asignado_id')
            ->where('status', CarteraStatus::Available->value)
            ->with('sorteo')
            ->orderBy('sorteo_id')
            ->orderBy('code')
            ->get()
            ->groupBy('sorteo_id');

        $this->setViewSharedData([
            'title_singular' => trans('Corals::labels.show_title', ['title' => $asignado->getIdentifier()]),
            'showModel'      => $asignado,
        ]);

        return view('Sorteos::asignados.show')->with(compact('asignado', 'sorteos', 'assignableCarteras'));
    }

    public function assignCarteras(Request $request, Asignado $asignado)
    {
        $request->validate([
            'cartera_ids'   => 'required|array|min:1',
            'cartera_ids.*' => 'integer|exists:sorteos_carteras,id',
        ]);

        $count = Cartera::whereIn('id', $request->cartera_ids)
            ->update([
                'asignado_id' => $asignado->id,
                'status'      => CarteraStatus::Asignado->value,
                'asignado_at' => now(),
                'updated_at'  => now(),
            ]);

        flash("{$count} cartera(s) asignada(s) a {$asignado->name}.")->success();

        return redirect()->back();
    }

    public function edit(AsignadoRequest $request, Asignado $asignado)
    {
        $this->setViewSharedData([
            'title_singular' => trans('Corals::labels.update_title', ['title' => $asignado->getIdentifier()]),
        ]);

        return view('Sorteos::asignados.create_edit')->with(compact('asignado'));
    }

    public function update(AsignadoRequest $request, Asignado $asignado)
    {
        try {
            $this->asignadoService->update($request, $asignado);
            flash(trans('Corals::messages.success.updated', ['item' => $this->title_singular]))->success();
        } catch (\Exception $e) {
            log_exception($e, Asignado::class, 'update');
        }

        return redirectTo($asignado->getShowURL());
    }

    public function destroy(AsignadoRequest $request, Asignado $asignado)
    {
        try {
            $this->asignadoService->destroy($request, $asignado);
            $message = ['level' => 'success', 'message' => trans('Corals::messages.success.deleted', ['item' => $this->title_singular])];
        } catch (\Exception $e) {
            log_exception($e, Asignado::class, 'destroy');
            $message = ['level' => 'error', 'message' => $e->getMessage()];
        }

        return response()->json($message);
    }
}
