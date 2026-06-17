<?php

namespace Corals\Modules\Sorteos\Services;

use Corals\Modules\Sorteos\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClubPagoService
{
    private string $apiUrl;
    private string $merchantId;
    private string $secretKey;
    private string $apiKey;

    public function __construct()
    {
        $this->apiUrl     = \Settings::get('clubpago_api_url', 'https://api.clubpago.com.mx');
        $this->merchantId = \Settings::get('clubpago_merchant_id', '');
        $this->secretKey  = \Settings::get('clubpago_secret_key', '');
        $this->apiKey     = \Settings::get('clubpago_api_key', '');
    }

    public function isConfigured(): bool
    {
        return $this->merchantId && $this->secretKey && $this->apiKey;
    }

    /**
     * Create a payment reference in ClubPago and return the payment URL.
     */
    public function initiatePayment(Order $order, ?string $returnUrl = null): array
    {
        $reference = $this->generateReference($order);

        $payload = [
            'merchant_id'  => $this->merchantId,
            'reference'    => $reference,
            'amount'       => (float) $order->total_amount,
            'currency'     => 'MXN',
            'description'  => 'Sorteo ITSON — Orden #' . $order->id,
            'buyer_name'   => $order->buyer_name,
            'buyer_email'  => $order->buyer_email,
            'buyer_phone'  => $order->buyer_phone,
            'callback_url' => route('sorteos.webhook.clubpago'),
            'return_url'   => $returnUrl ?? url(config('sorteos.models.order.resource_url') . '/' . $order->hashed_id),
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ])->post($this->apiUrl . '/v1/payments', $payload);

        if ($response->failed()) {
            Log::error('ClubPago payment initiation failed', [
                'order_id' => $order->id,
                'status'   => $response->status(),
                'body'     => $response->body(),
            ]);
            throw new \RuntimeException('Error al iniciar el pago con ClubPago: ' . $response->json('message', 'Error desconocido'));
        }

        $data = $response->json();

        $order->update(['payment_reference' => $reference]);

        return [
            'reference'   => $reference,
            'payment_url' => $data['payment_url'],
        ];
    }

    /**
     * Validate the HMAC-SHA256 signature sent by ClubPago on webhooks.
     */
    public function validateWebhookSignature(Request $request): bool
    {
        $signature = $request->header('X-ClubPago-Signature');

        if (!$signature) {
            return false;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $this->secretKey);

        return hash_equals($expected, $signature);
    }

    /**
     * Process a confirmed webhook payload and return the matching Order.
     */
    public function resolveOrderFromWebhook(array $payload): ?Order
    {
        $reference = $payload['reference'] ?? null;

        if (!$reference) {
            return null;
        }

        return Order::where('payment_reference', $reference)->first();
    }

    /**
     * Determine if the webhook payload represents a successful payment.
     */
    public function isPaymentConfirmed(array $payload): bool
    {
        return ($payload['status'] ?? '') === 'paid';
    }

    /**
     * Determine if the webhook payload represents a rejected/failed payment.
     */
    public function isPaymentRejected(array $payload): bool
    {
        return in_array($payload['status'] ?? '', ['rejected', 'failed', 'cancelled']);
    }

    private function generateReference(Order $order): string
    {
        return 'ITSON-' . strtoupper(base_convert($order->id, 10, 36)) . '-' . now()->format('ymdHi');
    }
}
