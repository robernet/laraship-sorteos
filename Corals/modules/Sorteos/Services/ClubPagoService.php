<?php

namespace Corals\Modules\Sorteos\Services;

use Corals\Modules\ClubPago\Models\ClubPagoReference;
use Corals\Modules\Sorteos\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClubPagoService
{
    public function isConfigured(): bool
    {
        $prefix = $this->prefix();
        return !empty(\Settings::get("{$prefix}_url_auth"))
            && !empty(\Settings::get("{$prefix}_user"))
            && !empty(\Settings::get("{$prefix}_password"));
    }

    /**
     * Generate a ClubPago payment reference and return the key payment data.
     *
     * @return array{reference: string, payment_url: string, bar_code: string, folio: string, date: string}
     */
    public function initiatePayment(Order $order): array
    {
        $token = $this->getToken();

        $orderId     = 'ORD-' . sprintf('%06d', $order->id);
        $descripcion = '[' . \Settings::get('site_name', 'Sorteos ITSON') . '] [' . $orderId . ']';
        $account     = str_pad(date('YmdHis'), 15, '0', STR_PAD_LEFT)
                     . str_pad((string) $order->id, 7, '0', STR_PAD_LEFT);

        $url = \Settings::get($this->prefix() . '_url_payformat');

        $response = Http::withToken($token)->post($url, [
            'Description'    => $descripcion,
            'Amount'         => (float) $order->total_amount,
            'Account'        => $account,
            'CustomerEmail'  => $order->buyer_email,
            'CustomerName'   => $order->buyer_name,
            'ExpirationDate' => null,
        ]);

        $data = $response->json();

        if (empty($data['Reference'])) {
            Log::error('ClubPago reference generation failed', [
                'order_id' => $order->id,
                'status'   => $response->status(),
                'body'     => $response->body(),
            ]);
            throw new \RuntimeException('No se pudo generar la referencia de pago con ClubPago.');
        }

        ClubPagoReference::updateOrCreate(
            ['reference' => $data['Reference']],
            [
                'order_id'      => $order->id,
                'amount'        => $order->total_amount,
                'currency'      => 'MXN',
                'authorization' => '',
                'bar_code'      => $data['BarCode'] ?? '',
                'pay_format'    => $data['PayFormat'] ?? '',
                'message'       => $data['Message'] ?? '',
                'folio'         => $data['Folio'] ?? '',
                'date'          => $data['Date'] ?? '',
                'status'        => 'pending',
                'user_id'       => null,
            ]
        );

        $order->update(['payment_reference' => $data['Reference']]);

        return [
            'reference'   => $data['Reference'],
            'payment_url' => $data['PayFormat'] ?? '',
            'bar_code'    => $data['BarCode'] ?? '',
            'folio'       => $data['Folio'] ?? '',
            'date'        => $data['Date'] ?? '',
        ];
    }

    /**
     * Validate incoming webhook/terminal request authenticity.
     *
     * ClubPago terminals identify themselves via X-Origin (base64 of the configured
     * origin string) and a fixed User-Agent. Both headers must match the settings values.
     * hash_equals() is used to prevent timing-based header enumeration.
     */
    public function validateWebhookSignature(Request $request): bool
    {
        $expectedOrigin    = base64_encode((string) \Settings::get('payment_clubpago_x_origin', ''));
        $expectedUserAgent = (string) \Settings::get('payment_clubpago_user_agent', '');

        if (!$request->hasHeader('X-Origin') || !$request->hasHeader('User-Agent')) {
            return false;
        }

        if (!hash_equals($expectedOrigin, (string) $request->header('X-Origin'))) {
            return false;
        }

        if (!hash_equals($expectedUserAgent, (string) $request->header('User-Agent'))) {
            return false;
        }

        return true;
    }

    /**
     * Resolve the Order linked to an incoming webhook payload.
     *
     * Expects payload field 'referencia' — the ClubPago reference string.
     */
    public function resolveOrderFromWebhook(array $payload): ?Order
    {
        $referencia = $payload['referencia'] ?? null;
        if (!$referencia) {
            return null;
        }

        $ref = ClubPagoReference::where('reference', $referencia)->first();
        return $ref?->order;
    }

    /**
     * Return true when the payload signals a successful payment (codigo === 0).
     */
    public function isPaymentConfirmed(array $payload): bool
    {
        return isset($payload['codigo']) && (int) $payload['codigo'] === 0;
    }

    /**
     * Return true when the payload signals a failed or rejected payment (any non-zero codigo).
     */
    public function isPaymentRejected(array $payload): bool
    {
        return isset($payload['codigo']) && (int) $payload['codigo'] !== 0;
    }

    private function getToken(): string
    {
        $prefix = $this->prefix();
        $url    = \Settings::get("{$prefix}_url_auth");
        $user   = \Settings::get("{$prefix}_user");
        $pass   = \Settings::get("{$prefix}_password");

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->post($url, ['user' => $user, 'pswd' => $pass])
            ->json();

        if (empty($response['Token'])) {
            Log::error('ClubPago auth failed', ['response' => $response]);
            throw new \RuntimeException('No se pudo autenticar con ClubPago.');
        }

        return $response['Token'];
    }

    private function prefix(): string
    {
        $sandbox = \Settings::get('payment_clubpago_sandbox_mode', 'true') === 'true';
        return $sandbox ? 'payment_clubpago_sandbox' : 'payment_clubpago_live';
    }
}
