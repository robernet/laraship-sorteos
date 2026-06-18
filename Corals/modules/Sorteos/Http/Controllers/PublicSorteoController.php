<?php

namespace Corals\Modules\Sorteos\Http\Controllers;

use Corals\Modules\Sorteos\Models\Boleto;
use Corals\Modules\Sorteos\Models\Cartera;
use Corals\Modules\Sorteos\Models\Order;
use Corals\Modules\Sorteos\Models\OrderItem;
use Corals\Modules\Sorteos\Models\Sorteo;
use Corals\Modules\Sorteos\Services\CarteraService;
use Corals\Modules\Sorteos\Services\ClubPagoService;
use Illuminate\Http\Request;

class PublicSorteoController extends \Corals\Foundation\Http\Controllers\PublicBaseController
{
    public function __construct(private ClubPagoService $clubPago)
    {
        parent::__construct();
    }

    public function index()
    {
        $sorteos = Sorteo::where('status', 'active')->latest()->get();
        return view('Sorteos::public.sorteos_list', compact('sorteos'));
    }

    public function show(string $slug)
    {
        $sorteo    = Sorteo::where('slug', $slug)->available()->firstOrFail();
        $available = $sorteo->boletos()->where('status', 'available')->count();

        return view('Sorteos::public.sorteo', compact('sorteo', 'available'));
    }

    public function checkout(Request $request, string $slug)
    {
        $sorteo = Sorteo::where('slug', $slug)->available()->firstOrFail();

        $request->validate([
            'buyer_name'     => 'required|string|max:255',
            'buyer_email'    => 'required|email|max:255',
            'buyer_phone'    => 'required|string|max:50',
            'buyer_city'     => 'nullable|string|max:100',
            'buyer_state'    => 'nullable|string|max:100',
            'quantity'       => 'required_without:ticket_numbers|nullable|integer|min:1|max:20',
            'ticket_numbers' => 'required_without:quantity|nullable|string|max:500',
        ]);

        if ($request->filled('ticket_numbers')) {
            $numbers = array_filter(array_map('trim', explode(',', $request->input('ticket_numbers'))));
            $boletos = Boleto::where('sorteo_id', $sorteo->id)
                ->whereIn('digital_number', $numbers)
                ->where('status', 'available')
                ->get();
        } else {
            $boletos = Boleto::where('sorteo_id', $sorteo->id)
                ->where('status', 'available')
                ->inRandomOrder()
                ->limit((int) $request->input('quantity', 1))
                ->get();
        }

        if ($boletos->isEmpty()) {
            return back()->withInput()->with('error', 'No hay boletos disponibles para la selección indicada.');
        }

        $order = Order::create([
            'sorteo_id'      => $sorteo->id,
            'buyer_name'     => $request->input('buyer_name'),
            'buyer_email'    => $request->input('buyer_email'),
            'buyer_phone'    => $request->input('buyer_phone'),
            'buyer_city'     => $request->input('buyer_city'),
            'buyer_state'    => $request->input('buyer_state'),
            'payment_method' => 'clubpago',
            'status'         => 'pending',
            'total_amount'   => $boletos->count() * $sorteo->ticket_price,
        ]);

        $items = $boletos->map(fn($b) => [
            'order_id'   => $order->id,
            'boleto_id'  => $b->id,
            'price'      => $sorteo->ticket_price,
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        OrderItem::insert($items);

        Boleto::whereIn('id', $boletos->pluck('id'))->update(['status' => 'reserved']);

        $carteraService = app(CarteraService::class);
        $boletos->pluck('cartera_id')->unique()->filter()->each(function ($carteraId) use ($carteraService) {
            $cartera = Cartera::find($carteraId);
            if ($cartera) {
                $carteraService->recalculateStatus($cartera);
            }
        });

        try {
            $returnUrl = route('sorteos.public.order', $order->hashed_id);
            $result    = $this->clubPago->initiatePayment($order, $returnUrl);

            return redirect()->away($result['payment_url']);
        } catch (\Exception $e) {
            Boleto::whereIn('id', $boletos->pluck('id'))->update(['status' => 'available']);
            $order->forceDelete();

            return back()->withInput()->with('error', 'Error al iniciar el pago. Por favor intenta de nuevo.');
        }
    }

    public function orderStatus(string $hashedId)
    {
        $order = Order::with(['sorteo', 'items.boleto'])->find(hashids_decode($hashedId));

        return view('Sorteos::public.order_status', compact('order'));
    }
}
