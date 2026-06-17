<?php

namespace Corals\Modules\Sorteos\Jobs;

use Corals\Modules\Sorteos\Models\Order;
use Corals\Modules\Sorteos\Services\BoletoDigitalService;
use Corals\Modules\Sorteos\Services\BrevoMailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOrderConfirmationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(public readonly Order $order) {}

    public function handle(BrevoMailService $brevo, BoletoDigitalService $boletoDigital): void
    {
        if (!$brevo->isConfigured()) {
            Log::warning('Brevo not configured — skipping order confirmation email', [
                'order_id' => $this->order->id,
            ]);
            return;
        }

        $this->order->loadMissing(['sorteo', 'items.boleto.sorteo', 'items.boleto.cartera']);

        $result = $brevo->sendOrderConfirmation($this->order, $boletoDigital);

        if (!$result['sent']) {
            // Throwing causes the job to retry (up to $tries times with $backoff seconds between)
            throw new \RuntimeException('Brevo send failed: ' . ($result['error'] ?? 'unknown error'));
        }
    }
}
