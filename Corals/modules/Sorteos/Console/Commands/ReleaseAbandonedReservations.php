<?php

namespace Corals\Modules\Sorteos\Console\Commands;

use Corals\Modules\Sorteos\Enums\OrderStatus;
use Corals\Modules\Sorteos\Models\Boleto;
use Corals\Modules\Sorteos\Models\Order;
use Illuminate\Console\Command;

class ReleaseAbandonedReservations extends Command
{
    protected $signature   = 'sorteos:release-reservations {--minutes=30 : Minutes before a pending order is considered abandoned}';
    protected $description = 'Release boletos reserved by pending orders older than the timeout';

    public function handle(): int
    {
        $minutes = (int) $this->option('minutes');
        $cutoff  = now()->subMinutes($minutes);

        $stale = Order::with('items')
            ->where('status', OrderStatus::Pending)
            ->where('created_at', '<', $cutoff)
            ->get();

        if ($stale->isEmpty()) {
            $this->info('No abandoned reservations found.');
            return self::SUCCESS;
        }

        $released = 0;

        foreach ($stale as $order) {
            $boletoIds = $order->items->pluck('boleto_id')->filter();

            Boleto::whereIn('id', $boletoIds)
                ->where('status', 'reserved')
                ->update(['status' => 'available']);

            $order->update(['status' => OrderStatus::Cancelled]);

            $released += $boletoIds->count();
        }

        $this->info("Released {$released} boleto(s) from {$stale->count()} abandoned order(s).");
        return self::SUCCESS;
    }
}
