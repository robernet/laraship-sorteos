<?php

namespace Corals\Modules\Sorteos\Http\Controllers;

use Corals\Foundation\Http\Controllers\BaseController;
use Corals\Modules\Sorteos\Models\Order;
use Corals\Modules\Sorteos\Services\ClubPagoService;
use Corals\Modules\Sorteos\Services\OrderService;
use Illuminate\Http\Request;

class PaymentsController extends BaseController
{
    protected $corals_middleware_except = ['webhook'];

    public function __construct(
        private ClubPagoService $clubPago,
        private OrderService $orderService
    ) {
        $this->resource_url = config('sorteos.models.order.resource_url');
        parent::__construct();
    }

    /**
     * Initiate a ClubPago payment for the given order and redirect the admin to the payment URL.
     */
    public function initiate(Request $request, Order $order)
    {
        try {
            $result = $this->clubPago->initiatePayment($order);
            return redirect()->away($result['payment_url']);
        } catch (\Exception $e) {
            log_exception($e, Order::class, 'initiatePayment');
            flash($e->getMessage())->error();
            return redirect()->back();
        }
    }

    /**
     * Record a manual payment (cash/transfer) or initiate ClubPago, then confirm.
     */
    public function recordPayment(\Illuminate\Http\Request $request, Order $order)
    {
        if (!$order->isPending()) {
            flash('Solo se pueden pagar órdenes pendientes.')->error();
            return redirect()->back();
        }

        $method    = $request->input('payment_method', $order->payment_method?->value);
        $reference = $request->input('payment_reference');

        $order->update([
            'payment_method'    => $method,
            'payment_reference' => $reference,
        ]);

        if ($method === 'clubpago') {
            try {
                $result = $this->clubPago->initiatePayment($order);
                return redirect()->away($result['payment_url']);
            } catch (\Exception $e) {
                log_exception($e, Order::class, 'initiatePayment');
                flash($e->getMessage())->error();
                return redirect()->back();
            }
        }

        try {
            $this->orderService->confirmOrder($order);
            flash('Pago registrado y orden confirmada.')->success();
        } catch (\Exception $e) {
            log_exception($e, Order::class, 'recordPayment');
            flash($e->getMessage())->error();
        }

        return redirect()->back();
    }

    /**
     * Receive and process ClubPago webhook notifications (public, no CSRF/auth).
     */
    public function webhook(Request $request)
    {
        if (!$this->clubPago->validateWebhookSignature($request)) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $payload = $request->json()->all();
        $order   = $this->clubPago->resolveOrderFromWebhook($payload);

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        try {
            if ($this->clubPago->isPaymentConfirmed($payload) && $order->isPending()) {
                $this->orderService->confirmOrder($order);
            } elseif ($this->clubPago->isPaymentRejected($payload) && $order->isPending()) {
                $this->orderService->cancelOrder($order);
            }
        } catch (\Exception $e) {
            log_exception($e, Order::class, 'webhook');
            return response()->json(['error' => 'Internal server error'], 500);
        }

        return response()->json(['status' => 'ok']);
    }
}
