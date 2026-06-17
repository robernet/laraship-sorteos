<?php

namespace Corals\Modules\Sorteos\Services;

use Corals\Foundation\Services\BaseServiceClass;
use Corals\Modules\Sorteos\Enums\BoletoStatus;
use Corals\Modules\Sorteos\Enums\CarteraStatus;
use Corals\Modules\Sorteos\Models\Boleto;
use Corals\Modules\Sorteos\Models\Cartera;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class CarteraService extends BaseServiceClass
{
    const TICKETS_PER_WALLET = 10;

    protected function postStore(FormRequest $request, $cartera)
    {
        $this->generateBoletos($cartera);
    }

    private function generateBoletos(Cartera $cartera): void
    {
        $boletos = [];

        for ($i = 0; $i < self::TICKETS_PER_WALLET; $i++) {
            $boletos[] = [
                'sorteo_id'       => $cartera->sorteo_id,
                'cartera_id'      => $cartera->id,
                'physical_number' => $cartera->physical_start + $i,
                'digital_number'  => $cartera->digital_start + $i,
                'status'          => BoletoStatus::Available->value,
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }

        Boleto::insert($boletos);
    }

    public function importFromCsv(UploadedFile $file, int $sorteoId): array
    {
        $created = 0;
        $skipped = 0;
        $handle  = fopen($file->getRealPath(), 'r');

        $header = fgetcsv($handle); // skip header row

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 3) {
                $skipped++;
                continue;
            }

            [$code, $physicalStart, $digitalStart] = $row;
            $code          = trim($code);
            $physicalStart = (int) trim($physicalStart);
            $digitalStart  = (int) trim($digitalStart);

            if (empty($code) || $physicalStart < 1 || $digitalStart < 1) {
                $skipped++;
                continue;
            }

            // Skip duplicates (same sorteo + code)
            if (Cartera::where('sorteo_id', $sorteoId)->where('code', $code)->exists()) {
                $skipped++;
                continue;
            }

            $cartera = Cartera::create([
                'sorteo_id'      => $sorteoId,
                'code'           => $code,
                'physical_start' => $physicalStart,
                'physical_end'   => $physicalStart + self::TICKETS_PER_WALLET - 1,
                'digital_start'  => $digitalStart,
                'digital_end'    => $digitalStart + self::TICKETS_PER_WALLET - 1,
                'status'         => \Corals\Modules\Sorteos\Enums\CarteraStatus::Available->value,
            ]);

            $this->generateBoletos($cartera);
            $created++;
        }

        fclose($handle);

        return compact('created', 'skipped');
    }

    public function recalculateStatus(Cartera $cartera): void
    {
        $total = $cartera->boletos()->count();
        $taken = $cartera->boletos()
            ->whereIn('status', [BoletoStatus::Sold->value, BoletoStatus::Reserved->value])
            ->count();

        $newStatus = match(true) {
            $taken === 0      => CarteraStatus::Available,
            $taken === $total => CarteraStatus::Sold,
            default           => CarteraStatus::Partial,
        };

        $cartera->update(['status' => $newStatus]);
    }
}
