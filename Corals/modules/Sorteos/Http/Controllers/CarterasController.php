<?php

namespace Corals\Modules\Sorteos\Http\Controllers;

use Corals\Foundation\Http\Controllers\BaseController;
use Corals\Modules\Sorteos\DataTables\CarterasDataTable;
use Corals\Modules\Sorteos\Enums\CarteraStatus;
use Corals\Modules\Sorteos\Http\Requests\CarteraRequest;
use Corals\Modules\Sorteos\Models\Cartera;
use Corals\Modules\Sorteos\Models\Sorteo;
use Corals\Modules\Sorteos\Services\CarteraService;
use Illuminate\Http\Request;

class CarterasController extends BaseController
{
    protected $carteraService;

    public function __construct(CarteraService $carteraService)
    {
        $this->carteraService = $carteraService;

        $this->resource_url = config('sorteos.models.cartera.resource_url');

        $this->resource_model = new Cartera();

        $this->title = trans('Sorteos::module.cartera.title');
        $this->title_singular = trans('Sorteos::module.cartera.title_singular');

        parent::__construct();
    }

    public function index(CarteraRequest $request, CarterasDataTable $dataTable)
    {
        return $dataTable->render('Sorteos::carteras.index');
    }

    public function create(CarteraRequest $request)
    {
        $cartera       = new Cartera();
        $sorteos       = Sorteo::pluck('name', 'id');
        $colaboradores = \Corals\Modules\Sorteos\Models\Colaborador::where('status', 'active')->pluck('name', 'id')->prepend('— Sin asignar —', '')->all();
        $statusOptions = collect(\Corals\Modules\Sorteos\Enums\CarteraStatus::cases())->mapWithKeys(fn($c) => [$c->value => $c->label()])->all();

        $this->setViewSharedData([
            'title_singular' => trans('Corals::labels.create_title', ['title' => $this->title_singular]),
        ]);

        return view('Sorteos::carteras.create_edit')->with(compact('cartera', 'sorteos', 'colaboradores', 'statusOptions'));
    }

    public function store(CarteraRequest $request)
    {
        try {
            $cartera = $this->carteraService->store($request, Cartera::class);

            flash(trans('Corals::messages.success.created', ['item' => $this->title_singular]))->success();
        } catch (\Exception $exception) {
            log_exception($exception, Cartera::class, 'store');
        }

        return redirectTo(isset($cartera) ? $cartera->getShowURL() : $this->resource_url);
    }

    public function show(CarteraRequest $request, Cartera $cartera)
    {
        $cartera->load(['sorteo', 'boletos']);

        $this->setViewSharedData([
            'title_singular' => trans('Corals::labels.show_title', ['title' => $cartera->getIdentifier()]),
            'showModel'      => $cartera,
        ]);

        return view('Sorteos::carteras.show')->with(compact('cartera'));
    }

    public function edit(CarteraRequest $request, Cartera $cartera)
    {
        $sorteos       = Sorteo::pluck('name', 'id');
        $colaboradores = \Corals\Modules\Sorteos\Models\Colaborador::where('status', 'active')->pluck('name', 'id')->prepend('— Sin asignar —', '')->all();
        $statusOptions = collect(\Corals\Modules\Sorteos\Enums\CarteraStatus::cases())->mapWithKeys(fn($c) => [$c->value => $c->label()])->all();

        $this->setViewSharedData([
            'title_singular' => trans('Corals::labels.update_title', ['title' => $cartera->getIdentifier()]),
        ]);

        return view('Sorteos::carteras.create_edit')->with(compact('cartera', 'sorteos', 'colaboradores', 'statusOptions'));
    }

    public function update(CarteraRequest $request, Cartera $cartera)
    {
        if ($this->isLocked($cartera)) {
            $request->merge(array_intersect_key(
                $request->all(),
                array_flip(['colaborador_id', 'status', '_token', '_method'])
            ));
        }

        try {
            $this->carteraService->update($request, $cartera);

            flash(trans('Corals::messages.success.updated', ['item' => $this->title_singular]))->success();
        } catch (\Exception $exception) {
            log_exception($exception, Cartera::class, 'update');
        }

        return redirectTo($cartera->getShowURL());
    }

    public function showGenerate(Request $request)
    {
        $sorteos   = Sorteo::pluck('name', 'id');
        $sorteoId  = $request->input('sorteo_id');
        $nextStart = $sorteoId ? $this->carteraService->getNextStartNumber((int) $sorteoId) : 1;

        $this->setViewSharedData(['title_singular' => 'Generar Carteras']);

        return view('Sorteos::carteras.generate')->with(compact('sorteos', 'sorteoId', 'nextStart'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'sorteo_id'     => 'required|integer|exists:sorteos_sorteos,id',
            'total_boletos' => 'required|integer|min:10',
            'start_number'  => 'required|integer|min:1',
            'code_prefix'   => 'required|string|max:5|alpha',
        ]);

        if ($request->integer('total_boletos') % 10 !== 0) {
            return back()->withErrors(['total_boletos' => 'El total debe ser múltiplo de 10.'])->withInput();
        }

        try {
            $result = $this->carteraService->generateForSorteo(
                (int) $request->sorteo_id,
                (int) $request->total_boletos,
                (int) $request->start_number,
                strtoupper($request->code_prefix)
            );

            flash("Se crearon {$result['created']} carteras ({$result['skipped']} omitidas).")->success();
        } catch (\Exception $e) {
            log_exception($e, Cartera::class, 'generate');
            flash($e->getMessage())->error();
        }

        return redirect(url($this->resource_url));
    }

    public function quickStatus(Request $request, Cartera $cartera)
    {
        $request->validate(['status' => 'required|string']);

        $status = CarteraStatus::from($request->status);

        if ($cartera->status === CarteraStatus::Sold) {
            if ($status !== CarteraStatus::Entregado) {
                return response()->json(['level' => 'error', 'message' => 'Solo se puede registrar la entrega de una cartera vendida.'], 422);
            }
            $cartera->update(['entregado_at' => now()]);
            $cartera->refresh();
            return response()->json([
                'level'        => 'success',
                'status'       => $cartera->status->value,
                'label'        => $cartera->status->label(),
                'badgeClass'   => $cartera->status->badgeClass(),
                'asignado_at'  => $cartera->asignado_at?->format('d/m/Y H:i'),
                'entregado_at' => $cartera->entregado_at?->format('d/m/Y H:i'),
            ]);
        }

        // Dates always recalculate on every transition — never frozen
        $data = match ($status) {
            CarteraStatus::Asignado  => ['status' => $status->value, 'asignado_at' => $cartera->asignado_at ?? now(), 'entregado_at' => null],
            CarteraStatus::Entregado => ['status' => $status->value, 'entregado_at' => now()],
            default                  => ['status' => $status->value, 'asignado_id' => null, 'asignado_at' => null, 'entregado_at' => null],
        };

        $cartera->update($data);
        $cartera->refresh();

        return response()->json([
            'level'        => 'success',
            'status'       => $status->value,
            'label'        => $status->label(),
            'badgeClass'   => $status->badgeClass(),
            'asignado_at'  => $cartera->asignado_at?->format('d/m/Y H:i'),
            'entregado_at' => $cartera->entregado_at?->format('d/m/Y H:i'),
        ]);
    }

    private function isLocked(Cartera $cartera): bool
    {
        return in_array($cartera->status, [CarteraStatus::Sold, CarteraStatus::Asignado, CarteraStatus::Entregado]);
    }

    public function downloadTemplate()
    {
        $csv = "code,physical_start,digital_start\nC001,1,1001\nC002,11,1011\n";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="carteras_template.csv"',
        ]);
    }

    public function importCsv(CarteraRequest $request)
    {
        $request->validate([
            'sorteo_id' => 'required|integer|exists:sorteos_sorteos,id',
            'csv_file'  => 'required|file|mimes:csv,txt|max:2048',
        ]);

        try {
            $result = $this->carteraService->importFromCsv(
                $request->file('csv_file'),
                (int) $request->input('sorteo_id')
            );

            flash(trans('Sorteos::messages.import_success', [
                'created' => $result['created'],
                'skipped' => $result['skipped'],
            ]))->success();
        } catch (\Exception $exception) {
            log_exception($exception, Cartera::class, 'importCsv');
        }

        return redirect(url($this->resource_url));
    }

    public function destroy(CarteraRequest $request, Cartera $cartera)
    {
        if ($this->isLocked($cartera)) {
            return response()->json(['level' => 'error', 'message' => 'No se puede eliminar una cartera activa o vendida.']);
        }

        try {
            $this->carteraService->destroy($request, $cartera);

            $message = [
                'level'   => 'success',
                'message' => trans('Corals::messages.success.deleted', ['item' => $this->title_singular]),
            ];
        } catch (\Exception $exception) {
            log_exception($exception, Cartera::class, 'destroy');
            $message = ['level' => 'error', 'message' => $exception->getMessage()];
        }

        return response()->json($message);
    }
}
