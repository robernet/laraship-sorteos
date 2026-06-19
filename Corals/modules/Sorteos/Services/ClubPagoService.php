<?php

namespace Corals\Modules\Sorteos\Services;

use Corals\Modules\ClubPago\Models\ClubPagoReference;
use Corals\Modules\Sorteos\Models\Order;
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

    public function initiatePayment(Order $order): ClubPagoReference
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

        $ref = ClubPagoReference::updateOrCreate(
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

        return $ref;
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
