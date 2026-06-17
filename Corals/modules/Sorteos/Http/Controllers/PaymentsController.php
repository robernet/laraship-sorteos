<?php

namespace Corals\Modules\Sorteos\Http\Controllers;

use Corals\Foundation\Http\Controllers\BaseController;
use Corals\Modules\Sorteos\Models\Order;
use Corals\Modules\Sorteos\Services\ClubPagoService;
use Corals\Modules\Sorteos\Services\OrderService;
use Illuminate\Http\Request;

class PaymentsController extends BaseController
{
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
     * Receive and process ClubPago webhook notifications (public, no CSRF/auth).
     */
    public function webhook(Request $request)
    {
        if (!$this->clubPago->validateWebhookSignature($request)) {
            return response()->json(['error' => 'Invalid signature'], 401);
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
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['status' => 'ok']);
    }
}
