<?php

namespace Tests\Feature\Sorteos;

use Corals\Modules\Sorteos\Models\Boleto;
use Corals\Modules\Sorteos\Models\Cartera;
use Corals\Modules\Sorteos\Models\Sorteo;
use Corals\Modules\Sorteos\Services\CarteraService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CarteraImportTest extends TestCase
{
    use DatabaseTransactions;

    private CarteraService $service;
    private Sorteo $sorteo;
    private static int $base = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CarteraService::class);
        self::$base   += 50000;
        $this->sorteo  = Sorteo::create([
            'name' => 'Import Test', 'slug' => 'imp-' . uniqid(),
            'ticket_price' => 100.00, 'status' => 'active', 'is_public' => true,
        ]);
    }

    private function csvFile(string $content): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'csv') . '.csv';
        file_put_contents($path, $content);
        return new UploadedFile($path, 'carteras.csv', 'text/csv', null, true);
    }

    public function test_import_creates_carteras_and_ten_boletos_each(): void
    {
        $b   = self::$base;
        $csv = "code,physical_start,digital_start\nA{$b}," . ($b + 1) . ',' . ($b + 1001) . "\nB{$b}," . ($b + 11) . ',' . ($b + 1011) . "\n";

        $result = $this->service->importFromCsv($this->csvFile($csv), $this->sorteo->id);

        $this->assertEquals(2, $result['created']);
        $this->assertEquals(0, $result['skipped']);
        $this->assertEquals(2, Cartera::where('sorteo_id', $this->sorteo->id)->count());
        $this->assertEquals(20, Boleto::where('sorteo_id', $this->sorteo->id)->count());
    }

    public function test_import_skips_duplicate_codes(): void
    {
        $b = self::$base;
        Cartera::create([
            'sorteo_id' => $this->sorteo->id, 'code' => "DUP{$b}",
            'physical_start' => $b + 1, 'physical_end' => $b + 10,
            'digital_start'  => $b + 1001, 'digital_end' => $b + 1010,
            'status' => 'available',
        ]);

        $csv    = "code,physical_start,digital_start\nDUP{$b}," . ($b + 1) . ',' . ($b + 1001) . "\n";
        $result = $this->service->importFromCsv($this->csvFile($csv), $this->sorteo->id);

        $this->assertEquals(0, $result['created']);
        $this->assertEquals(1, $result['skipped']);
    }

    public function test_import_skips_malformed_rows(): void
    {
        $b   = self::$base;
        $csv = "code,physical_start,digital_start\nONLY_TWO,1\n,1," . ($b + 1001) . "\nGOOD{$b}," . ($b + 1) . ',' . ($b + 1001) . "\n";

        $result = $this->service->importFromCsv($this->csvFile($csv), $this->sorteo->id);

        $this->assertEquals(1, $result['created']);
        $this->assertEquals(2, $result['skipped']);
    }

    public function test_import_generates_correct_boleto_number_range(): void
    {
        $b   = self::$base;
        $csv = "code,physical_start,digital_start\nRNG{$b}," . ($b + 1) . ',' . ($b + 1001) . "\n";

        $this->service->importFromCsv($this->csvFile($csv), $this->sorteo->id);

        $cartera = Cartera::where('sorteo_id', $this->sorteo->id)->first();
        $this->assertEquals(10, $cartera->boletos()->count());
        $this->assertDatabaseHas('sorteos_boletos', ['cartera_id' => $cartera->id, 'digital_number' => $b + 1001]);
        $this->assertDatabaseHas('sorteos_boletos', ['cartera_id' => $cartera->id, 'digital_number' => $b + 1010]);
    }
}
