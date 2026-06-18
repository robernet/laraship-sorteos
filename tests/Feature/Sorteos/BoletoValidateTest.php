<?php

namespace Tests\Feature\Sorteos;

use Corals\Modules\Sorteos\Models\Boleto;
use Corals\Modules\Sorteos\Models\Cartera;
use Corals\Modules\Sorteos\Models\Sorteo;
use Corals\Modules\Sorteos\Services\BoletoDigitalService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BoletoValidateTest extends TestCase
{
    use DatabaseTransactions;

    private static int $base = 0;

    private function boletoWithToken(): array
    {
        self::$base += 40000;
        $b = self::$base;

        $sorteo  = Sorteo::create([
            'name' => 'Val Test', 'slug' => 'val-' . uniqid(),
            'ticket_price' => 100.00, 'status' => 'active', 'is_public' => true,
        ]);
        $cartera = Cartera::create([
            'sorteo_id' => $sorteo->id, 'code' => 'V' . $b,
            'physical_start' => $b + 1, 'physical_end' => $b + 10,
            'digital_start'  => $b + 1001, 'digital_end' => $b + 1010,
            'status' => 'available',
        ]);
        $boleto  = Boleto::create([
            'sorteo_id' => $sorteo->id, 'cartera_id' => $cartera->id,
            'physical_number' => $b + 1, 'digital_number' => $b + 1001,
            'status' => 'sold',
        ]);

        $token = app(BoletoDigitalService::class)->getOrCreateToken($boleto);

        return compact('boleto', 'token', 'sorteo');
    }

    public function test_valid_token_returns_200_and_shows_boleto_number(): void
    {
        ['boleto' => $boleto, 'token' => $token] = $this->boletoWithToken();

        $this->get(route('sorteos.boleto.validate', $token))
             ->assertStatus(200)
             ->assertSee((string) $boleto->digital_number);
    }

    public function test_invalid_token_does_not_show_valid_result(): void
    {
        $fakeToken = str_repeat('a', 64);

        $this->get(route('sorteos.boleto.validate', $fakeToken))
             ->assertStatus(200)
             ->assertDontSee('Válido');
    }
}
