<?php

namespace Corals\Modules\Sorteos\Http\Controllers;

use Corals\Foundation\Http\Controllers\BaseController;
use Corals\Modules\Sorteos\DataTables\ColaboradoresDataTable;
use Corals\Modules\Sorteos\Enums\CarteraStatus;
use Corals\Modules\Sorteos\Http\Requests\ColaboradorRequest;
use Corals\Modules\Sorteos\Models\Colaborador;
use Corals\Modules\Sorteos\Models\Cartera;
use Corals\Modules\Sorteos\Models\Sorteo;
use Corals\Modules\Sorteos\Services\ColaboradorService;
use Illuminate\Http\Request;

class ColaboradoresController extends BaseController
{
    public function __construct(protected ColaboradorService $colaboradorService)
    {
        $this->resource_url   = config('sorteos.models.colaborador.resource_url');
        $this->resource_model = new Colaborador();
        $this->title          = trans('Sorteos::module.colaborador.title');
        $this->title_singular = trans('Sorteos::module.colaborador.title_singular');

        parent::__construct();
    }

    public function index(ColaboradorRequest $request, ColaboradoresDataTable $dataTable)
    {
        return $dataTable->render('Sorteos::colaboradores.index');
    }

    public function create(ColaboradorRequest $request)
    {
        $colaborador = new Colaborador();

        $this->setViewSharedData([
            'title_singular' => trans('Corals::labels.create_title', ['title' => $this->title_singular]),
        ]);

        return view('Sorteos::colaboradores.create_edit')->with(compact('colaborador'));
    }

    public function store(ColaboradorRequest $request)
    {
        try {
            $colaborador = $this->colaboradorService->store($request, Colaborador::class);
            flash(trans('Corals::messages.success.created', ['item' => $this->title_singular]))->success();
        } catch (\Exception $e) {
            log_exception($e, Colaborador::class, 'store');
        }

        return redirectTo(isset($colaborador) ? $colaborador->getShowURL() : $this->resource_url);
    }

    public function show(ColaboradorRequest $request, Colaborador $colaborador)
    {
        $colaborador->load('carteras.sorteo', 'carteras.boletos');

        $sorteos            = Sorteo::pluck('name', 'id');
        $assignableCarteras = Cartera::whereNull('colaborador_id')
            ->where('status', CarteraStatus::Available->value)
            ->with('sorteo')
            ->orderBy('sorteo_id')
            ->orderBy('code')
            ->get()
            ->groupBy('sorteo_id');

        $this->setViewSharedData([
            'title_singular' => trans('Corals::labels.show_title', ['title' => $colaborador->getIdentifier()]),
            'showModel'      => $colaborador,
        ]);

        return view('Sorteos::colaboradores.show')->with(compact('colaborador', 'sorteos', 'assignableCarteras'));
    }

    public function assignCarteras(Request $request, Colaborador $colaborador)
    {
        $request->validate([
            'cartera_ids'   => 'required|array|min:1',
            'cartera_ids.*' => 'integer|exists:sorteos_carteras,id',
        ]);

        $count = Cartera::whereIn('id', $request->cartera_ids)
            ->update([
                'colaborador_id' => $colaborador->id,
                'status'         => CarteraStatus::Asignado->value,
                'asignado_at'    => now(),
                'updated_at'     => now(),
            ]);

        flash("{$count} cartera(s) asignada(s) a {$colaborador->name}.")->success();

        return redirect()->back();
    }

    public function edit(ColaboradorRequest $request, Colaborador $colaborador)
    {
        $this->setViewSharedData([
            'title_singular' => trans('Corals::labels.update_title', ['title' => $colaborador->getIdentifier()]),
        ]);

        return view('Sorteos::colaboradores.create_edit')->with(compact('colaborador'));
    }

    public function update(ColaboradorRequest $request, Colaborador $colaborador)
    {
        try {
            $this->colaboradorService->update($request, $colaborador);
            flash(trans('Corals::messages.success.updated', ['item' => $this->title_singular]))->success();
        } catch (\Exception $e) {
            log_exception($e, Colaborador::class, 'update');
        }

        return redirectTo($colaborador->getShowURL());
    }

    public function destroy(ColaboradorRequest $request, Colaborador $colaborador)
    {
        try {
            $this->colaboradorService->destroy($request, $colaborador);
            $message = ['level' => 'success', 'message' => trans('Corals::messages.success.deleted', ['item' => $this->title_singular])];
        } catch (\Exception $e) {
            log_exception($e, Colaborador::class, 'destroy');
            $message = ['level' => 'error', 'message' => $e->getMessage()];
        }

        return response()->json($message);
    }
}
