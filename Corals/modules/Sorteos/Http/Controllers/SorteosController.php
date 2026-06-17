<?php

namespace Corals\Modules\Sorteos\Http\Controllers;

use Corals\Foundation\Http\Controllers\BaseController;
use Corals\Modules\Sorteos\DataTables\SorteosDataTable;
use Corals\Modules\Sorteos\Enums\SorteoStatus;
use Corals\Modules\Sorteos\Http\Requests\SorteoRequest;
use Corals\Modules\Sorteos\Models\Sorteo;
use Corals\Modules\Sorteos\Services\SorteoService;

class SorteosController extends BaseController
{
    protected $sorteoService;

    public function __construct(SorteoService $sorteoService)
    {
        $this->sorteoService = $sorteoService;

        $this->resource_url = config('sorteos.models.sorteo.resource_url');

        $this->resource_model = new Sorteo();

        $this->title = trans('Sorteos::module.sorteo.title');
        $this->title_singular = trans('Sorteos::module.sorteo.title_singular');

        parent::__construct();
    }

    /**
     * @param SorteoRequest $request
     * @param SorteosDataTable $dataTable
     * @return mixed
     */
    public function index(SorteoRequest $request, SorteosDataTable $dataTable)
    {
        return $dataTable->render('Sorteos::sorteos.index');
    }

    /**
     * @param SorteoRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(SorteoRequest $request)
    {
        $sorteo = new Sorteo();

        $this->setViewSharedData([
            'title_singular' => trans('Corals::labels.create_title', ['title' => $this->title_singular]),
        ]);

        return view('Sorteos::sorteos.create_edit')->with(compact('sorteo'));
    }

    /**
     * @param SorteoRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(SorteoRequest $request)
    {
        try {
            $sorteo = $this->sorteoService->store($request, Sorteo::class);

            flash(trans('Corals::messages.success.created', ['item' => $this->title_singular]))->success();
        } catch (\Exception $exception) {
            log_exception($exception, Sorteo::class, 'store');
        }

        return redirectTo(isset($sorteo) ? $sorteo->getShowURL() : $this->resource_url);
    }

    /**
     * @param SorteoRequest $request
     * @param Sorteo $sorteo
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(SorteoRequest $request, Sorteo $sorteo)
    {
        $this->setViewSharedData([
            'title_singular' => trans('Corals::labels.show_title', ['title' => $sorteo->getIdentifier()]),
            'showModel' => $sorteo,
        ]);

        return view('Sorteos::sorteos.show')->with(compact('sorteo'));
    }

    /**
     * @param SorteoRequest $request
     * @param Sorteo $sorteo
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(SorteoRequest $request, Sorteo $sorteo)
    {
        $this->setViewSharedData([
            'title_singular' => trans('Corals::labels.update_title', ['title' => $sorteo->getIdentifier()]),
        ]);

        return view('Sorteos::sorteos.create_edit')->with(compact('sorteo'));
    }

    /**
     * @param SorteoRequest $request
     * @param Sorteo $sorteo
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(SorteoRequest $request, Sorteo $sorteo)
    {
        try {
            $this->sorteoService->update($request, $sorteo);

            flash(trans('Corals::messages.success.updated', ['item' => $this->title_singular]))->success();
        } catch (\Exception $exception) {
            log_exception($exception, Sorteo::class, 'update');
        }

        return redirectTo($sorteo->getShowURL());
    }

    /**
     * @param SorteoRequest $request
     * @param Sorteo $sorteo
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(SorteoRequest $request, Sorteo $sorteo)
    {
        try {
            $this->sorteoService->destroy($request, $sorteo);

            $message = [
                'level' => 'success',
                'message' => trans('Corals::messages.success.deleted', ['item' => $this->title_singular]),
            ];
        } catch (\Exception $exception) {
            log_exception($exception, Sorteo::class, 'destroy');
            $message = ['level' => 'error', 'message' => $exception->getMessage()];
        }

        return response()->json($message);
    }

    public function changeStatus(SorteoRequest $request, Sorteo $sorteo, string $status)
    {
        try {
            $newStatus = SorteoStatus::from($status);
            $sorteo->update(['status' => $newStatus]);
            \Actions::dispatch('sorteo.status_changed', [$sorteo, $newStatus]);
            $message = ['level' => 'success', 'message' => trans('Corals::messages.success.updated', ['item' => $this->title_singular])];
        } catch (\Exception $exception) {
            log_exception($exception, Sorteo::class, 'changeStatus');
            $message = ['level' => 'error', 'message' => $exception->getMessage()];
        }

        return response()->json($message);
    }
}
