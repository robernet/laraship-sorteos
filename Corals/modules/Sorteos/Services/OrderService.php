<?php

namespace Corals\Modules\Sorteos\Services;

use Corals\Foundation\Services\BaseServiceClass;
use Corals\Modules\Sorteos\Enums\BoletoStatus;
use Corals\Modules\Sorteos\Enums\OrderStatus;
use Corals\Modules\Sorteos\Models\Boleto;
use Corals\Modules\Sorteos\Models\Order;
use Corals\Modules\Sorteos\Jobs\SendOrderConfirmationEmail;
use Illuminate\Foundation\Http\FormRequest;

class OrderService extends BaseServiceClass
{
    protected function postStore(FormRequest $request, $order): void
    {
        $this->attachBoletos($request, $order);
    }

    private function attachBoletos(FormRequest $request, Order $order): void
    {
        $boletoIds = $this->resolveBoletoIds($request, $order->sorteo_id);

        if (empty($boletoIds)) {
            return;
        }

        $boletos = Boleto::whereIn('id', $boletoIds)
            ->where('sorteo_id', $order->sorteo_id)
            ->where('status', BoletoStatus::Available)
            ->get();

        $ticketPrice = $order->sorteo->ticket_price;
        $items = [];

        foreach ($boletos as $boleto) {
            $items[] = [
                'order_id'   => $order->id,
                'boleto_id'  => $boleto->id,
                'price'      => $ticketPrice,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($items)) {
            \DB::table('sorteos_order_items')->insert($items);

            Boleto::whereIn('id', $boletos->pluck('id'))->update(['status' => BoletoStatus::Reserved->value]);

            $affectedCarteraIds = $boletos->pluck('cartera_id')->unique();
            $carteraService = new CarteraService();
            foreach ($affectedCarteraIds as $carteraId) {
                $cartera = \Corals\Modules\Sorteos\Models\Cartera::find($carteraId);
                if ($cartera) {
                    $carteraService->recalculateStatus($cartera);
                }
            }

            $order->update(['total_amount' => count($items) * $ticketPrice]);
        }
    }

    private function resolveBoletoIds(FormRequest $request, int $sorteoId): array
    {
        $ids = array_filter((array) $request->input('boleto_ids', []));

        // If a collaborator is set, scope all boleto resolution to their carteras only
        $asignadoCarteraIds = null;
        if ($request->filled('asignado_id')) {
            $asignadoCarteraIds = \Corals\Modules\Sorteos\Models\Cartera::where('asignado_id', $request->input('asignado_id'))
                ->where('sorteo_id', $sorteoId)
                ->pluck('id')
                ->toArray();
        }

        if ($request->filled('cartera_ids')) {
            $requestedIds = $request->input('cartera_ids');
            if ($asignadoCarteraIds !== null) {
                $requestedIds = array_intersect($requestedIds, $asignadoCarteraIds);
            }
            $carteraBoletos = Boleto::whereIn('cartera_id', $requestedIds)
                ->where('sorteo_id', $sorteoId)
                ->where('status', BoletoStatus::Available)
                ->pluck('id')
                ->toArray();
            $ids = array_merge($ids, $carteraBoletos);
        }

        if ($request->filled('boleto_ids_text')) {
            $digitalNumbers = array_filter(array_map('trim', explode(',', $request->input('boleto_ids_text'))));
            $query = Boleto::where('sorteo_id', $sorteoId)
                ->whereIn('digital_number', $digitalNumbers)
                ->where('status', BoletoStatus::Available);
            if ($asignadoCarteraIds !== null) {
                $query->whereIn('cartera_id', $asignadoCarteraIds);
            }
            $ids = array_merge($ids, $query->pluck('id')->toArray());
        }

        if ($request->filled('cartera_codes_text')) {
            $codes = array_filter(array_map('trim', explode(',', $request->input('cartera_codes_text'))));
            $carteraQuery = \Corals\Modules\Sorteos\Models\Cartera::where('sorteo_id', $sorteoId)
                ->whereIn('code', $codes);
            if ($asignadoCarteraIds !== null) {
                $carteraQuery->whereIn('id', $asignadoCarteraIds);
            }
            $carteraIds = $carteraQuery->pluck('id')->toArray();
            if (!empty($carteraIds)) {
                $codeBoletos = Boleto::whereIn('cartera_id', $carteraIds)
                    ->where('status', BoletoStatus::Available)
                    ->pluck('id')
                    ->toArray();
                $ids = array_merge($ids, $codeBoletos);
            }
        }

        // Scope any direct boleto_ids to the collaborator's carteras
        if ($asignadoCarteraIds !== null && !empty($ids)) {
            $validIds = Boleto::whereIn('id', $ids)
                ->whereIn('cartera_id', $asignadoCarteraIds)
                ->pluck('id')
                ->toArray();
            $ids = $validIds;
        }

        return array_unique($ids);
    }

    public function confirmOrder(Order $order): void
    {
        if (!$order->isPending()) {
            throw new \RuntimeException('Solo se pueden confirmar órdenes pendientes.');
        }

        $order->items()->with('boleto')->get()->each(function ($item) {
            $item->boleto->update(['status' => BoletoStatus::Sold->value]);
        });

        $order->update(['status' => OrderStatus::Confirmed]);

        $this->recalculateAffectedCarteras($order);

        \Actions::dispatch('order.confirmed', [$order]);

        // ponytail: sync keeps email delivery reliable without a queue worker; switch to dispatch() if a worker is configured
        SendOrderConfirmationEmail::dispatchSync($order);
    }

    public function cancelOrder(Order $order): void
    {
        if (!$order->isPending()) {
            throw new \RuntimeException('Solo se pueden cancelar órdenes pendientes.');
        }

        $order->items()->with('boleto')->get()->each(function ($item) {
            $item->boleto->update(['status' => BoletoStatus::Available->value]);
        });

        $order->update(['status' => OrderStatus::Cancelled]);

        $this->recalculateAffectedCarteras($order);

        \Actions::dispatch('order.cancelled', [$order]);
    }

    private function recalculateAffectedCarteras(Order $order): void
    {
        $carteraIds = $order->items()->with('boleto')
            ->get()
            ->pluck('boleto.cartera_id')
            ->unique();

        $carteraService = new CarteraService();

        foreach ($carteraIds as $carteraId) {
            $cartera = \Corals\Modules\Sorteos\Models\Cartera::find($carteraId);
            if ($cartera) {
                $carteraService->recalculateStatus($cartera);
            }
        }
    }
}
