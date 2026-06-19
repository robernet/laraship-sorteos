<?php

namespace Corals\Modules\Sorteos\Http\Controllers;

use Corals\Foundation\Http\Controllers\BaseController;
use Corals\Modules\Sorteos\DataTables\OrdersDataTable;
use Corals\Modules\Sorteos\Enums\BoletoStatus;
use Corals\Modules\Sorteos\Http\Requests\OrderRequest;
use Corals\Modules\Sorteos\Models\Asignado;
use Corals\Modules\Sorteos\Models\Boleto;
use Corals\Modules\Sorteos\Models\Cartera;
use Corals\Modules\Sorteos\Models\Order;
use Corals\Modules\Sorteos\Models\Sorteo;
use Corals\Modules\Sorteos\Services\BoletoDigitalService;
use Corals\Modules\Sorteos\Services\BrevoMailService;
use Corals\Modules\Sorteos\Services\OrderService;
use Illuminate\Http\Request;

class OrdersController extends BaseController
{
    protected $orderService;
    protected $boletoDigital;
    protected $brevo;

    public function __construct(OrderService $orderService, BoletoDigitalService $boletoDigital, BrevoMailService $brevo)
    {
        $this->orderService  = $orderService;
        $this->boletoDigital = $boletoDigital;
        $this->brevo         = $brevo;

        $this->resource_url = config('sorteos.models.order.resource_url');

        $this->resource_model = new Order();

        $this->title = trans('Sorteos::module.order.title');
        $this->title_singular = trans('Sorteos::module.order.title_singular');

        parent::__construct();
    }

    public function index(OrderRequest $request, OrdersDataTable $dataTable)
    {
        return $dataTable->render('Sorteos::orders.index');
    }

    public function create(OrderRequest $request)
    {
        $order = new Order();
        $sorteos = Sorteo::pluck('name', 'id');
        $asignados = Asignado::where('status', 'active')->orderBy('name')->pluck('name', 'id')->prepend('— Sin colaborador —', '');
        $paymentMethods = collect(\Corals\Modules\Sorteos\Enums\PaymentMethod::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->all();

        $this->setViewSharedData([
            'title_singular' => trans('Corals::labels.create_title', ['title' => $this->title_singular]),
        ]);

        return view('Sorteos::orders.create_edit')->with(compact('order', 'sorteos', 'asignados', 'paymentMethods'));
    }

    public function store(OrderRequest $request)
    {
        try {
            $order = $this->orderService->store($request, Order::class);

            flash(trans('Corals::messages.success.created', ['item' => $this->title_singular]))->success();
        } catch (\Exception $exception) {
            log_exception($exception, Order::class, 'store');
        }

        return redirectTo(isset($order) ? $order->getShowURL() : $this->resource_url);
    }

    public function show(OrderRequest $request, Order $order)
    {
        $order->load(['sorteo', 'items.boleto']);

        $this->setViewSharedData([
            'title_singular' => trans('Corals::labels.show_title', ['title' => $order->getIdentifier()]),
            'showModel'      => $order,
        ]);

        return view('Sorteos::orders.show')->with(compact('order'));
    }

    public function edit(OrderRequest $request, Order $order)
    {
        $order->load(['items.boleto.cartera']);
        $sorteos = Sorteo::pluck('name', 'id');
        $asignados = Asignado::where('status', 'active')->orderBy('name')->pluck('name', 'id')->prepend('— Sin colaborador —', '');
        $paymentMethods = collect(\Corals\Modules\Sorteos\Enums\PaymentMethod::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->all();

        $this->setViewSharedData([
            'title_singular' => trans('Corals::labels.update_title', ['title' => $order->getIdentifier()]),
        ]);

        return view('Sorteos::orders.create_edit')->with(compact('order', 'sorteos', 'asignados', 'paymentMethods'));
    }

    public function carterasByAsignado(Request $request)
    {
        $asignadoId = (int) $request->input('asignado_id');
        $sorteoId   = (int) $request->input('sorteo_id');

        if (!$asignadoId || !$sorteoId) {
            return response()->json(['carteras' => []]);
        }

        $carteras = Cartera::where('asignado_id', $asignadoId)
            ->where('sorteo_id', $sorteoId)
            ->whereNotIn('status', ['sold'])
            ->with(['boletos' => fn($q) => $q->where('status', BoletoStatus::Available)->orderBy('digital_number')])
            ->orderBy('code')
            ->get()
            ->map(fn($c) => [
                'id'      => $c->id,
                'code'    => $c->code,
                'boletos' => $c->boletos->map(fn($b) => ['id' => $b->id, 'number' => $b->digital_number]),
            ]);

        return response()->json(['carteras' => $carteras]);
    }

    public function update(OrderRequest $request, Order $order)
    {
        try {
            $this->orderService->update($request, $order);

            flash(trans('Corals::messages.success.updated', ['item' => $this->title_singular]))->success();
        } catch (\Exception $exception) {
            log_exception($exception, Order::class, 'update');
        }

        return redirectTo($order->getShowURL());
    }

    public function destroy(OrderRequest $request, Order $order)
    {
        try {
            $this->orderService->destroy($request, $order);

            $message = [
                'level'   => 'success',
                'message' => trans('Corals::messages.success.deleted', ['item' => $this->title_singular]),
            ];
        } catch (\Exception $exception) {
            log_exception($exception, Order::class, 'destroy');
            $message = ['level' => 'error', 'message' => $exception->getMessage()];
        }

        return response()->json($message);
    }

    public function confirmOrder(OrderRequest $request, Order $order)
    {
        try {
            $this->orderService->confirmOrder($order);
            $message = ['level' => 'success', 'message' => trans('Corals::messages.success.updated', ['item' => $this->title_singular])];
        } catch (\Exception $exception) {
            log_exception($exception, Order::class, 'confirmOrder');
            $message = ['level' => 'error', 'message' => $exception->getMessage()];
        }

        return response()->json($message);
    }

    public function cancelOrder(OrderRequest $request, Order $order)
    {
        try {
            $this->orderService->cancelOrder($order);
            flash('Orden cancelada correctamente.')->success();
        } catch (\Exception $exception) {
            log_exception($exception, Order::class, 'cancelOrder');
            flash($exception->getMessage())->error();
        }

        return redirect()->back();
    }

    public function updateReference(OrderRequest $request, Order $order)
    {
        try {
            if (!$order->isConfirmed()) {
                flash('Solo se puede corregir la referencia de órdenes confirmadas.')->warning();
                return redirect()->back();
            }
            $order->update(['payment_reference' => $request->input('payment_reference')]);
            flash('Referencia actualizada.')->success();
        } catch (\Exception $exception) {
            log_exception($exception, Order::class, 'updateReference');
            flash($exception->getMessage())->error();
        }

        return redirect()->back();
    }

    public function resendTickets(OrderRequest $request, Order $order)
    {
        try {
            if (!$this->brevo->isConfigured()) {
                throw new \RuntimeException('El servicio de correo Brevo no está configurado.');
            }

            $order->loadMissing(['sorteo', 'items.boleto.sorteo', 'items.boleto.cartera']);
            $result = $this->brevo->sendOrderConfirmation($order, $this->boletoDigital);

            $message = $result['sent']
                ? ['level' => 'success', 'message' => 'Correo reenviado a ' . $order->buyer_email]
                : ['level' => 'error',   'message' => $result['error']];
        } catch (\Exception $e) {
            log_exception($e, Order::class, 'resendTickets');
            $message = ['level' => 'error', 'message' => $e->getMessage()];
        }

        return response()->json($message);
    }

    public function downloadTickets(OrderRequest $request, Order $order)
    {
        $order->loadMissing(['items.boleto.sorteo', 'items.boleto.cartera']);

        $items = $order->items->filter(fn($i) => $i->boleto);

        if ($items->isEmpty()) {
            flash('Esta orden no tiene boletos.')->warning();
            return redirect()->back();
        }

        // Single boleto → return PDF directly
        if ($items->count() === 1) {
            $boleto   = $items->first()->boleto;
            $pdf      = $this->boletoDigital->pdfContent($boleto);
            $filename = 'boleto-' . str_pad($boleto->digital_number, 5, '0', STR_PAD_LEFT) . '.pdf';

            return response($pdf, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        // Multiple boletos → ZIP
        $zipPath = tempnam(sys_get_temp_dir(), 'boletos-') . '.zip';
        $zip     = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE);

        foreach ($items as $item) {
            $boleto   = $item->boleto;
            $pdf      = $this->boletoDigital->pdfContent($boleto);
            $filename = 'boleto-' . str_pad($boleto->digital_number, 5, '0', STR_PAD_LEFT) . '.pdf';
            $zip->addFromString($filename, $pdf);
        }

        $zip->close();

        return response()->download($zipPath, 'boletos-orden-' . $order->id . '.zip', [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }
}
