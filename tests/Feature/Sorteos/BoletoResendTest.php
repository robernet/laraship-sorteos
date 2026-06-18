<?php

namespace Tests\Feature\Sorteos;

use Corals\Modules\Sorteos\Models\Boleto;
use Corals\Modules\Sorteos\Models\Cartera;
use Corals\Modules\Sorteos\Models\Order;
use Corals\Modules\Sorteos\Models\Sorteo;
use Corals\Modules\Sorteos\Services\BrevoMailService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BoletoResendTest extends TestCase
{
    use DatabaseTransactions;

    private static int $base = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
    }

    private function confirmedOrder(string $email = 'buyer@test.com'): Order
    {
        self::$base += 60000;
        $b = self::$base;

        $sorteo  = Sorteo::create([
            'name' => 'RS Test', 'slug' => 'rs-' . uniqid(),
            'ticket_price' => 100.00, 'status' => 'active', 'is_public' => true,
        ]);
        $cartera = Cartera::create([
            'sorteo_id' => $sorteo->id, 'code' => 'RS' . $b,
            'physical_start' => $b + 1, 'physical_end' => $b + 10,
            'digital_start'  => $b + 1001, 'digital_end' => $b + 1010,
            'status' => 'sold',
        ]);
        $boleto  = Boleto::create([
            'sorteo_id' => $sorteo->id, 'cartera_id' => $cartera->id,
            'physical_number' => $b + 1, 'digital_number' => $b + 1001,
            'status' => 'sold',
        ]);
        $order = Order::create([
            'sorteo_id' => $sorteo->id, 'buyer_name' => 'Test Buyer',
            'buyer_email' => $email, 'buyer_phone' => '6440000000',
            'payment_method' => 'clubpago', 'status' => 'confirmed', 'total_amount' => 100.00,
        ]);
        $order->items()->create(['boleto_id' => $boleto->id, 'price' => 100.00]);

        return $order;
    }

    public function test_resend_form_returns_200(): void
    {
        $this->get(route('sorteos.boletos.resend-form'))
             ->assertStatus(200);
    }

    public function test_resend_with_unknown_email_shows_error(): void
    {
        $this->post(route('sorteos.boletos.resend-email'), ['email' => 'nobody@nowhere.com'])
             ->assertSessionHas('resend_error');
    }

    public function test_resend_sends_email_for_confirmed_order(): void
    {
        $this->confirmedOrder('resend@test.com');

        $this->mock(BrevoMailService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('sendOrderConfirmation')
                 ->once()
                 ->andReturn(['sent' => true, 'message_id' => 'msg-test', 'error' => null]);
        });

        $this->post(route('sorteos.boletos.resend-email'), ['email' => 'resend@test.com'])
             ->assertSessionHas('resend_ok', true)
             ->assertSessionHas('resend_count', 1);
    }

    public function test_resend_requires_valid_email_format(): void
    {
        $this->post(route('sorteos.boletos.resend-email'), ['email' => 'not-an-email'])
             ->assertSessionHasErrors(['email']);
    }

    public function test_resend_skips_when_brevo_not_configured(): void
    {
        $this->confirmedOrder('nobrevo@test.com');

        $this->mock(BrevoMailService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(false);
            $mock->shouldReceive('sendOrderConfirmation')->never();
        });

        $this->post(route('sorteos.boletos.resend-email'), ['email' => 'nobrevo@test.com'])
             ->assertSessionHas('resend_error');
    }
}
