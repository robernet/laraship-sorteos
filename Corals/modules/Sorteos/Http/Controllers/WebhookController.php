<?php

namespace Corals\Modules\Sorteos\Http\Controllers;

use Corals\Modules\Sorteos\Models\Order;
use Corals\Modules\Sorteos\Services\ClubPagoService;
use Corals\Modules\Sorteos\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WebhookController extends Controller
{
    public function __construct(
        private ClubPagoService $clubPago,
        private OrderService $orderService
    ) {}

    public function handle(Request $request)
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
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['status' => 'ok']);
    }
}
