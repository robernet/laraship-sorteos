<?php

namespace Tests\Feature\Sorteos;

use Corals\Modules\Sorteos\Models\Boleto;
use Corals\Modules\Sorteos\Models\Cartera;
use Corals\Modules\Sorteos\Models\Order;
use Corals\Modules\Sorteos\Models\Sorteo;
use Corals\Modules\Sorteos\Services\ClubPagoService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use DatabaseTransactions;

    private int $seq = 0;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    private function pendingOrderWithBoleto(): array
    {
        $base   = ++$this->seq * 20000;
        $sorteo = Sorteo::create([
            'name' => 'WH Test', 'slug' => 'wh-' . uniqid(),
            'ticket_price' => 50.00, 'status' => 'active', 'is_public' => true,
        ]);
        $cartera = Cartera::create([
            'sorteo_id' => $sorteo->id, 'code' => 'W' . $base,
            'physical_start' => $base + 1, 'physical_end' => $base + 10,
            'digital_start'  => $base + 1001, 'digital_end' => $base + 1010,
            'status' => 'available',
        ]);
        $boleto = Boleto::create([
            'sorteo_id' => $sorteo->id, 'cartera_id' => $cartera->id,
            'physical_number' => $base + 1, 'digital_number' => $base + 1001,
            'status' => 'reserved',
        ]);
        $order = Order::create([
            'sorteo_id' => $sorteo->id, 'buyer_name' => 'Test Buyer',
            'buyer_email' => 'buyer@test.com', 'buyer_phone' => '6441111111',
            'payment_method' => 'clubpago', 'status' => 'pending',
            'total_amount' => 50.00, 'payment_reference' => 'ITSON-REF-' . $base,
        ]);
        $order->items()->create(['boleto_id' => $boleto->id, 'price' => 50.00]);

        return compact('order', 'boleto');
    }

    public function test_confirmed_webhook_sets_order_confirmed_and_boleto_sold(): void
    {
        ['order' => $order, 'boleto' => $boleto] = $this->pendingOrderWithBoleto();

        $this->mock(ClubPagoService::class, function ($mock) use ($order) {
            $mock->shouldReceive('validateWebhookSignature')->once()->andReturn(true);
            $mock->shouldReceive('resolveOrderFromWebhook')->once()->andReturn($order);
            $mock->shouldReceive('isPaymentConfirmed')->once()->andReturn(true);
            $mock->shouldReceive('isPaymentRejected')->never();
        });

        $this->postJson(route('sorteos.webhook.clubpago'), [
            'reference' => $order->payment_reference,
            'status'    => 'paid',
        ])->assertStatus(200);

        $this->assertEquals('confirmed', $order->fresh()->status->value ?? $order->fresh()->status);
        $this->assertEquals('sold', $boleto->fresh()->status->value ?? $boleto->fresh()->status);
    }

    public function test_rejected_webhook_cancels_order_and_releases_boletos(): void
    {
        ['order' => $order, 'boleto' => $boleto] = $this->pendingOrderWithBoleto();

        $this->mock(ClubPagoService::class, function ($mock) use ($order) {
            $mock->shouldReceive('validateWebhookSignature')->once()->andReturn(true);
            $mock->shouldReceive('resolveOrderFromWebhook')->once()->andReturn($order);
            $mock->shouldReceive('isPaymentConfirmed')->once()->andReturn(false);
            $mock->shouldReceive('isPaymentRejected')->once()->andReturn(true);
        });

        $this->postJson(route('sorteos.webhook.clubpago'), [
            'reference' => $order->payment_reference,
            'status'    => 'rejected',
        ])->assertStatus(200);

        $this->assertEquals('cancelled', $order->fresh()->status->value ?? $order->fresh()->status);
        $this->assertEquals('available', $boleto->fresh()->status->value ?? $boleto->fresh()->status);
    }

    public function test_invalid_signature_returns_400(): void
    {
        $this->mock(ClubPagoService::class, function ($mock) {
            $mock->shouldReceive('validateWebhookSignature')->once()->andReturn(false);
        });

        $this->postJson(route('sorteos.webhook.clubpago'), ['reference' => 'any'])
             ->assertStatus(400);
    }
}
