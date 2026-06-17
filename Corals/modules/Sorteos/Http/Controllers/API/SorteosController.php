<?php

namespace Corals\Modules\Sorteos\Http\Controllers\API;

use Corals\Foundation\Http\Controllers\APIBaseController;
use Corals\Modules\Sorteos\DataTables\SorteosDataTable;
use Corals\Modules\Sorteos\Http\Requests\SorteoRequest;
use Corals\Modules\Sorteos\Models\Sorteo;
use Corals\Modules\Sorteos\Services\SorteoService;
use Corals\Modules\Sorteos\Transformers\API\SorteoPresenter;

class SorteosController extends APIBaseController
{
    protected $sorteoService;

    /**
     * SorteosController constructor.
     * @param SorteoService $sorteoService
     * @throws \Exception
     */
    public function __construct(SorteoService $sorteoService)
    {
        $this->sorteoService = $sorteoService;
        $this->sorteoService->setPresenter(new SorteoPresenter());

        parent::__construct();
    }

    /**
     * @param SorteoRequest $request
     * @param SorteosDataTable $dataTable
     * @return mixed
     * @throws \Exception
     */
    public function index(SorteoRequest $request, SorteosDataTable $dataTable)
    {
        $sorteos = $dataTable->query(new Sorteo());

        return $this->sorteoService->index($sorteos, $dataTable);
    }

    /**
     * @param SorteoRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(SorteoRequest $request)
    {
        try {
            $sorteo = $this->sorteoService->store($request, Sorteo::class);

            return apiResponse($this->sorteoService->getModelDetails(), trans('Corals::messages.success.created', ['item' => $sorteo->name]));
        } catch (\Exception $exception) {
            return apiExceptionResponse($exception);
        }
    }

    /**
     * @param SorteoRequest $request
     * @param Sorteo $sorteo
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(SorteoRequest $request, Sorteo $sorteo)
    {
        try {
            return apiResponse($this->sorteoService->getModelDetails($sorteo));
        } catch (\Exception $exception) {
            return apiExceptionResponse($exception);
        }
    }

    /**
     * @param SorteoRequest $request
     * @param Sorteo $sorteo
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(SorteoRequest $request, Sorteo $sorteo)
    {
        try {
            $this->sorteoService->update($request, $sorteo);

            return apiResponse($this->sorteoService->getModelDetails(), trans('Corals::messages.success.updated', ['item' => $sorteo->name]));
        } catch (\Exception $exception) {
            return apiExceptionResponse($exception);
        }
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

            return apiResponse([], trans('Corals::messages.success.deleted', ['item' => $sorteo->name]));
        } catch (\Exception $exception) {
            return apiExceptionResponse($exception);
        }
    }
}
