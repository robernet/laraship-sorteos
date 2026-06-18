<?php

namespace Tests\Feature\Sorteos;

use Corals\Modules\Sorteos\Models\Boleto;
use Corals\Modules\Sorteos\Models\Cartera;
use Corals\Modules\Sorteos\Models\Order;
use Corals\Modules\Sorteos\Models\Sorteo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReleaseReservationsTest extends TestCase
{
    use DatabaseTransactions;

    private int $seq = 0;

    private function orderWithReservedBoleto(string $status = 'pending', int $ageMinutes = 60): array
    {
        $base    = ++$this->seq * 30000;
        $sorteo  = Sorteo::create([
            'name' => 'RR Test', 'slug' => 'rr-' . uniqid(),
            'ticket_price' => 100.00, 'status' => 'active', 'is_public' => true,
        ]);
        $cartera = Cartera::create([
            'sorteo_id' => $sorteo->id, 'code' => 'R' . $base,
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
            'sorteo_id' => $sorteo->id, 'buyer_name' => 'Stale Buyer',
            'buyer_email' => 'stale@test.com', 'buyer_phone' => '6440000000',
            'payment_method' => 'clubpago', 'status' => $status, 'total_amount' => 100.00,
        ]);
        $order->items()->create(['boleto_id' => $boleto->id, 'price' => 100.00]);
        $order->update(['created_at' => now()->subMinutes($ageMinutes)]);

        return compact('order', 'boleto');
    }

    public function test_releases_boletos_from_stale_pending_orders(): void
    {
        ['order' => $order, 'boleto' => $boleto] = $this->orderWithReservedBoleto('pending', 60);

        $this->artisan('sorteos:release-reservations', ['--minutes' => 30])
             ->assertExitCode(0);

        $this->assertEquals('available', $boleto->fresh()->status->value ?? $boleto->fresh()->status);
        $this->assertEquals('cancelled', $order->fresh()->status->value ?? $order->fresh()->status);
    }

    public function test_does_not_release_recent_pending_orders(): void
    {
        ['order' => $order, 'boleto' => $boleto] = $this->orderWithReservedBoleto('pending', 10);

        $this->artisan('sorteos:release-reservations', ['--minutes' => 30])
             ->assertExitCode(0);

        $this->assertEquals('reserved', $boleto->fresh()->status->value ?? $boleto->fresh()->status);
        $this->assertEquals('pending', $order->fresh()->status->value ?? $order->fresh()->status);
    }

    public function test_does_not_touch_confirmed_orders(): void
    {
        ['order' => $order, 'boleto' => $boleto] = $this->orderWithReservedBoleto('confirmed', 120);
        $boleto->update(['status' => 'sold']);

        $this->artisan('sorteos:release-reservations', ['--minutes' => 30])
             ->assertExitCode(0);

        $this->assertEquals('sold', $boleto->fresh()->status->value ?? $boleto->fresh()->status);
        $this->assertEquals('confirmed', $order->fresh()->status->value ?? $order->fresh()->status);
    }

    public function test_custom_minutes_option_is_respected(): void
    {
        // Order aged 5 min, threshold 3 min → should be released
        ['order' => $order, 'boleto' => $boleto] = $this->orderWithReservedBoleto('pending', 5);

        $this->artisan('sorteos:release-reservations', ['--minutes' => 3])
             ->assertExitCode(0);

        $this->assertEquals('available', $boleto->fresh()->status->value ?? $boleto->fresh()->status);
    }
}
