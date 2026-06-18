<?php

namespace Tests\Feature\Sorteos;

use Corals\Modules\Sorteos\Models\Boleto;
use Corals\Modules\Sorteos\Models\Cartera;
use Corals\Modules\Sorteos\Models\Order;
use Corals\Modules\Sorteos\Models\Sorteo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PublicSorteoTest extends TestCase
{
    use DatabaseTransactions;

    private int $seq = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
    }

    private function sorteo(array $attrs = []): Sorteo
    {
        return Sorteo::create(array_merge([
            'name'         => 'Sorteo Test',
            'slug'         => 'test-' . uniqid(),
            'ticket_price' => 100.00,
            'status'       => 'active',
            'is_public'    => true,
        ], $attrs));
    }

    private function withBoletos(Sorteo $sorteo, int $count = 3): Cartera
    {
        $base    = ++$this->seq * 10000;
        $cartera = Cartera::create([
            'sorteo_id'      => $sorteo->id,
            'code'           => 'C' . $base,
            'physical_start' => $base + 1,
            'physical_end'   => $base + 10,
            'digital_start'  => $base + 1001,
            'digital_end'    => $base + 1010,
            'status'         => 'available',
        ]);

        for ($i = 0; $i < $count; $i++) {
            Boleto::create([
                'sorteo_id'       => $sorteo->id,
                'cartera_id'      => $cartera->id,
                'physical_number' => $base + 1 + $i,
                'digital_number'  => $base + 1001 + $i,
                'status'          => 'available',
            ]);
        }

        return $cartera;
    }

    // ── show ──────────────────────────────────────────────────────────────────

    public function test_show_returns_200_for_active_public_sorteo(): void
    {
        $sorteo = $this->sorteo();

        $this->get(route('sorteos.public.show', $sorteo->slug))
             ->assertStatus(200)
             ->assertSee($sorteo->name);
    }

    public function test_show_returns_404_for_non_public_sorteo(): void
    {
        $sorteo = $this->sorteo(['is_public' => false]);

        $this->get(route('sorteos.public.show', $sorteo->slug))
             ->assertStatus(404);
    }

    public function test_show_returns_404_for_inactive_sorteo(): void
    {
        $sorteo = $this->sorteo(['status' => 'paused']);

        $this->get(route('sorteos.public.show', $sorteo->slug))
             ->assertStatus(404);
    }

    // ── checkout ──────────────────────────────────────────────────────────────

    public function test_checkout_creates_order_and_reserves_boletos(): void
    {
        Http::fake(['https://api.clubpago.com.mx/*' => Http::response(['payment_url' => 'https://pay.test/abc'], 200)]);

        $sorteo = $this->sorteo();
        $this->withBoletos($sorteo, 3);

        $this->post(route('sorteos.public.checkout', $sorteo->slug), [
            'buyer_name'  => 'Ana García',
            'buyer_email' => 'ana@test.com',
            'buyer_phone' => '6441234567',
            'quantity'    => 2,
        ])->assertRedirect('https://pay.test/abc');

        $order = Order::where('buyer_email', 'ana@test.com')->first();
        $this->assertNotNull($order);
        $this->assertEquals(200.00, (float) $order->total_amount);
        $this->assertEquals(2, $order->items()->count());
        $this->assertEquals(2, Boleto::where('sorteo_id', $sorteo->id)->where('status', 'reserved')->count());
    }

    public function test_checkout_with_specific_numbers_reserves_those_boletos(): void
    {
        Http::fake(['https://api.clubpago.com.mx/*' => Http::response(['payment_url' => 'https://pay.test/xyz'], 200)]);

        $sorteo  = $this->sorteo();
        $cartera = $this->withBoletos($sorteo, 3);
        $boleto  = $cartera->boletos()->where('status', 'available')->first();

        $this->post(route('sorteos.public.checkout', $sorteo->slug), [
            'buyer_name'     => 'Luis Pérez',
            'buyer_email'    => 'luis@test.com',
            'buyer_phone'    => '6449876543',
            'ticket_numbers' => (string) $boleto->digital_number,
        ])->assertRedirect('https://pay.test/xyz');

        $this->assertDatabaseHas('sorteos_boletos', ['id' => $boleto->id, 'status' => 'reserved']);
    }

    public function test_checkout_with_unavailable_numbers_shows_error_and_creates_no_order(): void
    {
        $sorteo  = $this->sorteo();
        $cartera = $this->withBoletos($sorteo, 1);
        $boleto  = $cartera->boletos()->first();
        $boleto->update(['status' => 'sold']);

        $this->post(route('sorteos.public.checkout', $sorteo->slug), [
            'buyer_name'     => 'Test User',
            'buyer_email'    => 'test@test.com',
            'buyer_phone'    => '6440000000',
            'ticket_numbers' => (string) $boleto->digital_number,
        ])->assertSessionHas('error');

        $this->assertDatabaseMissing('sorteos_orders', ['buyer_email' => 'test@test.com']);
    }

    public function test_checkout_rollbacks_boletos_when_clubpago_fails(): void
    {
        Http::fake(['api.clubpago.com.mx/*' => Http::response(['message' => 'Error'], 500)]);

        $sorteo = $this->sorteo();
        $this->withBoletos($sorteo, 2);

        $this->post(route('sorteos.public.checkout', $sorteo->slug), [
            'buyer_name'  => 'Carlos Soto',
            'buyer_email' => 'carlos@test.com',
            'buyer_phone' => '6440000001',
            'quantity'    => 1,
        ])->assertSessionHas('error');

        $this->assertDatabaseMissing('sorteos_orders', ['buyer_email' => 'carlos@test.com']);
        $this->assertEquals(0, Boleto::where('sorteo_id', $sorteo->id)->where('status', 'reserved')->count());
    }

    public function test_checkout_validates_required_fields(): void
    {
        $sorteo = $this->sorteo();

        $this->post(route('sorteos.public.checkout', $sorteo->slug), [])
             ->assertSessionHasErrors(['buyer_name', 'buyer_email', 'buyer_phone']);
    }

    // ── order status ──────────────────────────────────────────────────────────

    public function test_order_status_shows_pending_state(): void
    {
        $sorteo = $this->sorteo();
        $order  = Order::create([
            'sorteo_id'      => $sorteo->id,
            'buyer_name'     => 'Rosa Medina',
            'buyer_email'    => 'rosa@test.com',
            'buyer_phone'    => '6440000002',
            'payment_method' => 'clubpago',
            'status'         => 'pending',
            'total_amount'   => 100.00,
        ]);

        $this->get(route('sorteos.public.order', $order->hashed_id))
             ->assertStatus(200)
             ->assertSee('Pendiente de pago')
             ->assertSee('Rosa Medina');
    }

    public function test_order_status_shows_confirmed_state(): void
    {
        $sorteo = $this->sorteo();
        $order  = Order::create([
            'sorteo_id'      => $sorteo->id,
            'buyer_name'     => 'Pedro Ríos',
            'buyer_email'    => 'pedro@test.com',
            'buyer_phone'    => '6440000003',
            'payment_method' => 'clubpago',
            'status'         => 'confirmed',
            'total_amount'   => 100.00,
        ]);

        $this->get(route('sorteos.public.order', $order->hashed_id))
             ->assertStatus(200)
             ->assertSee('Pago confirmado');
    }

    public function test_order_status_shows_not_found_for_invalid_hash(): void
    {
        $this->get(route('sorteos.public.order', 'invalidhash00000'))
             ->assertStatus(200)
             ->assertSee('No se encontró la orden');
    }
}
