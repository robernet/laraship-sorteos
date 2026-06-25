<?php

namespace Corals\Modules\Sorteos\Http\Controllers;

use Corals\Foundation\Http\Controllers\BaseController;
use Corals\Modules\Sorteos\DataTables\BoletosDataTable;
use Corals\Modules\Sorteos\Http\Requests\BoletoRequest;
use Corals\Modules\Sorteos\Models\Boleto;
use Corals\Modules\Sorteos\Models\Order;
use Corals\Modules\Sorteos\Services\BoletoDigitalService;
use Corals\Modules\Sorteos\Services\BoletoService;
use Corals\Modules\Sorteos\Services\BrevoMailService;
use Illuminate\Http\Request;

class BoletosController extends BaseController
{
    protected $corals_middleware_except = ['validateTicket', 'resendForm', 'resendByEmail'];

    public function __construct(
        protected BoletoService $boletoService,
        protected BoletoDigitalService $boletoDigital
    ) {
        $this->resource_url = config('sorteos.models.boleto.resource_url');
        $this->resource_model = new Boleto();
        $this->title = trans('Sorteos::module.boleto.title');
        $this->title_singular = trans('Sorteos::module.boleto.title_singular');

        parent::__construct();
    }

    public function index(BoletoRequest $request, BoletosDataTable $dataTable)
    {
        return $dataTable->render('Sorteos::boletos.index', ['hideCreate' => true]);
    }

    public function show(BoletoRequest $request, Boleto $boleto)
    {
        $this->setViewSharedData([
            'title_singular' => trans('Corals::labels.show_title', ['title' => $boleto->getIdentifier()]),
            'showModel'      => $boleto,
        ]);

        $boleto->load(['orderItems.order', 'cartera', 'sorteo']);

        return view('Sorteos::boletos.show')->with(compact('boleto'));
    }

    public function download(BoletoRequest $request, Boleto $boleto)
    {
        $boleto->loadMissing(['sorteo', 'cartera']);
        $pdf = $this->boletoDigital->pdfContent($boleto);
        $filename = 'boleto-' . str_pad($boleto->digital_number, 5, '0', STR_PAD_LEFT) . '.pdf';

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function validateTicket(Request $request, string $token)
    {
        $boleto = $this->boletoDigital->findByToken($token);

        if ($boleto) {
            $boleto->loadMissing(['sorteo', 'cartera']);
        }

        return view('Sorteos::boletos.validate', compact('boleto'));
    }

    public function resendForm()
    {
        return view('Sorteos::boletos.resend');
    }

    public function resendByEmail(Request $request)
    {
        $request->validate(['email' => 'required|email|max:255']);

        $email  = strtolower(trim($request->input('email')));
        $brevo  = app(BrevoMailService::class);
        $orders = Order::where('buyer_email', $email)
            ->where('status', 'confirmed')
            ->with(['sorteo', 'items.boleto.sorteo', 'items.boleto.cartera'])
            ->get();

        if ($orders->isEmpty()) {
            return back()->with('resend_error', 'No encontramos órdenes confirmadas para ese correo.');
        }

        if (!$brevo->isConfigured()) {
            return back()->with('resend_error', 'El servicio de correo no está configurado. Contacta a soporte.');
        }

        // ponytail: rate-limit via last send timestamp stored per order; 5 min cooldown
        $cooldown  = now()->subMinutes(5);
        $sent      = 0;
        $throttled = 0;

        foreach ($orders as $order) {
            $sends = $order->properties['email_sends'] ?? [];
            $last  = !empty($sends) ? \Carbon\Carbon::parse(end($sends)['sent_at']) : null;

            if ($last && $last->gt($cooldown)) {
                $throttled++;
                continue;
            }

            $result = $brevo->sendOrderConfirmation($order, $this->boletoDigital);
            if ($result['sent']) {
                $sent++;
            }
        }

        if ($throttled > 0 && $sent === 0) {
            return back()->with('resend_error', 'Por favor espera unos minutos antes de volver a solicitar el reenvío.');
        }

        return back()->with('resend_ok', true)->with('resend_count', $sent)->with('resend_email', $email);
    }
}
