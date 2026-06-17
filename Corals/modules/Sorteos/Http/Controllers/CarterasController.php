<?php

namespace Corals\Modules\Sorteos\Http\Controllers;

use Corals\Foundation\Http\Controllers\BaseController;
use Corals\Modules\Sorteos\DataTables\CarterasDataTable;
use Corals\Modules\Sorteos\Http\Requests\CarteraRequest;
use Corals\Modules\Sorteos\Models\Cartera;
use Corals\Modules\Sorteos\Models\Sorteo;
use Corals\Modules\Sorteos\Services\CarteraService;

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
        $cartera = new Cartera();
        $sorteos = Sorteo::pluck('name', 'id');

        $this->setViewSharedData([
            'title_singular' => trans('Corals::labels.create_title', ['title' => $this->title_singular]),
        ]);

        return view('Sorteos::carteras.create_edit')->with(compact('cartera', 'sorteos'));
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
        $sorteos = Sorteo::pluck('name', 'id');

        $this->setViewSharedData([
            'title_singular' => trans('Corals::labels.update_title', ['title' => $cartera->getIdentifier()]),
        ]);

        return view('Sorteos::carteras.create_edit')->with(compact('cartera', 'sorteos'));
    }

    public function update(CarteraRequest $request, Cartera $cartera)
    {
        try {
            $this->carteraService->update($request, $cartera);

            flash(trans('Corals::messages.success.updated', ['item' => $this->title_singular]))->success();
        } catch (\Exception $exception) {
            log_exception($exception, Cartera::class, 'update');
        }

        return redirectTo($cartera->getShowURL());
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
