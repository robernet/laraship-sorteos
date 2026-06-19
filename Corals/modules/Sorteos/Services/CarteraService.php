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

    protected function postStore(FormRequest $request, $additionalData)
    {
        $this->generateBoletos($this->model);
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

        $isProtected = in_array($cartera->status, [
            CarteraStatus::Asignado,
            CarteraStatus::Entregado,
        ]);

        if ($total > 0 && $taken === $total) {
            $cartera->update(['status' => CarteraStatus::Sold]);
        } elseif (!$isProtected) {
            $cartera->update(['status' => $taken === 0 ? CarteraStatus::Available : CarteraStatus::Partial]);
        }
    }

    public function generateForSorteo(int $sorteoId, int $totalBoletos, int $startNumber, string $prefix): array
    {
        $numCarteras = (int) ceil($totalBoletos / self::TICKETS_PER_WALLET);
        $created     = 0;
        $skipped     = 0;

        // Find highest existing sequence for this prefix so codes never restart at 001
        $existingMax = Cartera::where('sorteo_id', $sorteoId)
            ->where('code', 'like', $prefix . '%')
            ->get()
            ->map(fn($c) => (int) ltrim(substr($c->code, strlen($prefix)), '0') ?: 0)
            ->max() ?? 0;

        $padding = max(3, strlen((string) ($existingMax + $numCarteras)));

        for ($i = 1; $i <= $numCarteras; $i++) {
            $seq        = $existingMax + $i;
            $code       = $prefix . str_pad($seq, $padding, '0', STR_PAD_LEFT);
            $physStart  = $startNumber + (($i - 1) * self::TICKETS_PER_WALLET);
            $physEnd    = $physStart + self::TICKETS_PER_WALLET - 1;

            $codeExists = Cartera::where('sorteo_id', $sorteoId)->where('code', $code)->exists();
            $rangeExists = Cartera::where('sorteo_id', $sorteoId)
                ->where(function ($q) use ($physStart, $physEnd) {
                    $q->whereBetween('physical_start', [$physStart, $physEnd])
                      ->orWhereBetween('physical_end', [$physStart, $physEnd]);
                })->exists();

            if ($codeExists || $rangeExists) {
                $skipped++;
                continue;
            }

            $cartera = Cartera::create([
                'sorteo_id'      => $sorteoId,
                'code'           => $code,
                'physical_start' => $physStart,
                'physical_end'   => $physEnd,
                'digital_start'  => $physStart,
                'digital_end'    => $physEnd,
                'status'         => CarteraStatus::Available->value,
            ]);

            $this->generateBoletos($cartera);
            $created++;
        }

        return compact('created', 'skipped', 'numCarteras');
    }

    public function getNextStartNumber(int $sorteoId): int
    {
        $max = Cartera::where('sorteo_id', $sorteoId)->max('physical_end');
        return $max ? $max + 1 : 1;
    }
}
