<?php

namespace Corals\Modules\ClubPago\Http\Controllers;

use Corals\Foundation\Http\Controllers\PublicBaseController;
use Corals\Modules\ClubPago\Models\ClubPagoReference;
use Corals\Modules\Sorteos\Models\Order;
use Corals\Modules\Sorteos\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ClubPagoController extends PublicBaseController
{
    public function consultaReferencia(Request $request)
    {
        $status = $this->verificaHeader($request);
        if ($status === '401') {
            return response()->json(['codigo' => 1, 'message' => 'Token Inválido'], 401);
        }
        if ($status === '403') {
            return response()->json(['codigo' => 2, 'message' => 'Origen Desconocido'], 403);
        }

        $request->validate(['r' => 'required|string|max:100']);

        $referencia = $request->query('r');
        $ref = ClubPagoReference::where('reference', $referencia)->first();

        if (!$ref) {
            return $this->jsonResponse(3, 0, 0, 'Referencia Desconocida', $referencia);
        }

        if ($ref->status !== 'pending') {
            return $this->jsonResponse(13, 0, 0, 'Referencia Sin Adeudo o Cancelada', $referencia);
        }

        $monto = number_format($ref->amount * 100, 0, '.', '');
        return $this->jsonResponse(0, $monto, random_int(0, 1000000), 'Transacción Exitosa', $referencia);
    }

    public function pagoReferencia(Request $request)
    {
        $status = $this->verificaHeader($request);
        if ($status === '401') {
            return response()->json(['codigo' => 1, 'message' => 'Token Inválido'], 401);
        }
        if ($status === '403') {
            return response()->json(['codigo' => 2, 'message' => 'Origen Desconocido'], 403);
        }

        $request->validate([
            'referencia'  => 'required|string|max:100',
            'monto'       => 'required|numeric|min:1',
            'transaccion' => 'required|string|max:100',
        ]);

        $referencia = $request->input('referencia');
        $ref = ClubPagoReference::where('reference', $referencia)->first();

        if (!$ref) {
            return $this->jsonResponse(3, 0, 0, 'Referencia Desconocida', $referencia);
        }

        if ($ref->status !== 'pending') {
            return $this->jsonResponse(13, 0, 0, 'Referencia Sin Adeudo o Cancelada', $referencia);
        }

        $montoRecibido = (float) $request->input('monto') / 100;
        if ($montoRecibido != $ref->amount) {
            return $this->jsonResponse(30, 0, 0, 'Monto Inválido', $referencia);
        }

        $order = $ref->order;
        if (!$order) {
            return $this->jsonResponse(50, 0, 0, 'Error de Sistema. Hable con su Proveedor', $referencia);
        }

        app(OrderService::class)->confirmOrder($order);

        $autorizacion = random_int(10000000, 99999999);
        $transaccion  = $request->input('transaccion');

        $ref->update(['authorization' => $autorizacion, 'status' => 'paid']);

        $mensaje = 'Transacción Exitosa, Orden # ORD-' . sprintf('%06d', $order->id);
        return response($this->buildPayResponse(0, $ref->amount, $transaccion, $autorizacion, $mensaje, $referencia));
    }

    public function cancelaPago(Request $request)
    {
        $status = $this->verificaHeader($request);
        if ($status === '401') {
            return response()->json(['codigo' => 1, 'message' => 'Token Inválido'], 401);
        }
        if ($status === '403') {
            return response()->json(['codigo' => 2, 'message' => 'Origen Desconocido'], 403);
        }

        $request->validate([
            'referencia'   => 'required|string|max:100',
            'autorizacion' => 'required|string|max:50',
        ]);

        $referencia   = $request->input('referencia');
        $autorizacion = $request->input('autorizacion');
        $ref = ClubPagoReference::where('reference', $referencia)->first();

        if (!$ref) {
            return $this->jsonResponse(3, 0, 0, 'Referencia Desconocida', $referencia);
        }

        if ($ref->status !== 'paid') {
            return $this->jsonSimple(0, 'Cancelación Exitosa');
        }

        if (!hash_equals((string) $ref->authorization, (string) $autorizacion)) {
            return $this->jsonSimple(61, 'Cancelación Fallida');
        }

        $order = $ref->order;
        if ($order) {
            app(OrderService::class)->cancelOrder($order);
        }

        $ref->update(['status' => 'cancelled']);
        return $this->jsonSimple(0, 'Cancelación Exitosa');
    }

    public function generarReferencia(Order $order): string
    {
        $tokenResponse = $this->getClubPagoToken();
        if (!is_array($tokenResponse) || empty($tokenResponse['Token'])) {
            return json_encode(['error' => 'Token Inválido']);
        }

        $token       = $tokenResponse['Token'];
        $monto       = $order->total_amount;
        $userId      = \Auth::id();
        $name        = \Auth::user()->name . ' ' . \Auth::user()->last_name;
        $email       = \Auth::user()->email;
        $orderId     = 'ORD-' . sprintf('%06d', $order->id);
        $descripcion = '[' . \Settings::get('site_name') . '] [' . $orderId . ']';

        $response = $this->requestReference($userId, $name, $email, $token, $descripcion, $monto);
        $eval = json_decode($response, true);

        ClubPagoReference::updateOrCreate(
            ['reference' => $eval['Reference']],
            [
                'order_id'      => $order->id,
                'amount'        => $monto,
                'currency'      => '',
                'authorization' => '',
                'bar_code'      => $eval['BarCode'],
                'pay_format'    => $eval['PayFormat'],
                'message'       => $eval['Message'],
                'folio'         => $eval['Folio'],
                'date'          => $eval['Date'],
                'status'        => 'pending',
                'user_id'       => $userId,
            ]
        );

        event('notifications.clubpago.send_reference', [
            'user'              => \Auth::user(),
            'order_number'      => $orderId,
            'folio'             => $eval['Folio'],
            'fecha'             => $eval['Date'],
            'amount'            => $monto,
            'payment_reference' => $eval['Reference'],
            'pay_format'        => $eval['PayFormat'],
            'response'          => $response,
        ]);

        return $response;
    }

    public function getClubPagoToken(): array|string
    {
        $response = json_decode($this->authRequest(), true);
        if (!empty($response['Token'])) {
            return $response;
        }
        return ['error' => '401', 'message' => 'Token Inválido'];
    }

    private function authRequest(): string
    {
        [$url, $user, $pass] = $this->terminalCredentials('auth');

        return Http::withHeaders(['Content-Type' => 'application/json'])
            ->post($url, ['user' => $user, 'pswd' => $pass])
            ->body();
    }

    private function requestReference(int $userId, string $name, string $email, string $token, string $descripcion, float $monto): string
    {
        [$url] = $this->terminalCredentials('payformat');
        $dateStr = date('YmdHis');
        $account = str_pad($dateStr, 15, '0', STR_PAD_LEFT) . str_pad((string) $userId, 7, '0', STR_PAD_LEFT);

        return Http::withToken($token)
            ->post($url, [
                'Description'    => $descripcion,
                'Amount'         => $monto,
                'Account'        => $account,
                'CustomerEmail'  => $email,
                'CustomerName'   => $name,
                'ExpirationDate' => null,
            ])
            ->body();
    }

    private function terminalCredentials(string $endpoint): array
    {
        $sandbox = \Settings::get('payment_clubpago_sandbox_mode', 'true') === 'true';
        $prefix  = $sandbox ? 'payment_clubpago_sandbox' : 'payment_clubpago_live';

        return [
            \Settings::get("{$prefix}_url_{$endpoint}"),
            \Settings::get("{$prefix}_user"),
            \Settings::get("{$prefix}_password"),
        ];
    }

    private function verificaHeader(Request $request): string
    {
        $xOrigin   = base64_encode((string) \Settings::get('payment_clubpago_x_origin'));
        $userAgent = (string) \Settings::get('payment_clubpago_user_agent');

        if (!$request->hasHeader('X-Origin') || !$request->hasHeader('User-Agent')) {
            return '401';
        }

        if (!hash_equals($xOrigin, (string) $request->header('X-Origin'))) {
            return '401';
        }

        if (!hash_equals($userAgent, (string) $request->header('User-Agent'))) {
            return '403';
        }

        return '200';
    }

    private function jsonResponse(int $codigo, $monto, $transaccion, string $mensaje, string $referencia): \Illuminate\Http\Response
    {
        return response(json_encode([
            'codigo'      => $codigo,
            'monto'       => $monto,
            'transaccion' => $transaccion,
            'mensaje'     => $mensaje,
            'referencia'  => $referencia,
        ]));
    }

    private function buildPayResponse(int $codigo, $monto, $transaccion, int $autorizacion, string $mensaje, string $referencia): string
    {
        return json_encode([
            'codigo'           => $codigo,
            'monto'            => $monto,
            'transaccion'      => $transaccion,
            'autorizacion'     => $autorizacion,
            'mensaje'          => $mensaje,
            'notificacion_sms' => '',
            'mensaje_sms'      => '',
            'mensaje_ticket'   => '',
            'referencia'       => $referencia,
        ]);
    }

    private function jsonSimple(int $codigo, string $mensaje): \Illuminate\Http\Response
    {
        return response(json_encode(['codigo' => $codigo, 'mensaje' => $mensaje]));
    }
}
