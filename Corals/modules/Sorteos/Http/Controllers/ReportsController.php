<?php

namespace Corals\Modules\Sorteos\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Corals\Foundation\Http\Controllers\BaseController;
use Corals\Modules\Sorteos\Models\Sorteo;
use Corals\Modules\Sorteos\Services\ReportService;
use Illuminate\Http\Request;
use League\Csv\Writer;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportsController extends BaseController
{
    public function __construct(protected ReportService $reports)
    {
        $this->resource_url    = 'sorteos/reports';
        $this->title           = 'Reportes';
        $this->title_singular  = 'Reporte';
        parent::__construct();
    }

    public function index(Request $request)
    {
        $sorteoId = $request->integer('sorteo_id') ?: null;
        $kpis     = $this->reports->kpiSummary($sorteoId);
        $sorteos  = Sorteo::orderByDesc('id')->pluck('name', 'id');

        $this->setViewSharedData(['title_singular' => 'Dashboard de Reportes']);

        return view('Sorteos::reports.index', compact('kpis', 'sorteos', 'sorteoId'));
    }

    public function sales(Request $request)
    {
        [$from, $to, $period, $sorteoId] = $this->parseFilters($request);

        $data    = $this->reports->salesByPeriod($period, $from, $to, $sorteoId);
        $sorteos = Sorteo::orderByDesc('id')->pluck('name', 'id');

        $this->setViewSharedData(['title_singular' => 'Reporte de Ventas']);

        $export = $request->input('export');

        if ($export === 'csv') {
            return $this->exportSalesCsv($data, $from, $to);
        }

        if ($export === 'pdf') {
            return $this->exportSalesPdf($data, $from, $to, $period);
        }

        if ($export === 'xlsx') {
            $rows = array_map(null, $data['labels'], $data['orders'], $data['revenue'], $data['tickets']);
            $rows = array_map(fn($r) => [$r[0], (int)$r[1], (float)$r[2], (int)$r[3]], $rows);
            return $this->buildXlsx(
                ['Período', 'Órdenes', 'Ingresos (MXN)', 'Boletos Vendidos'],
                $rows,
                'reporte-ventas-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.xlsx'
            );
        }

        return view('Sorteos::reports.sales', compact('data', 'sorteos', 'sorteoId', 'period', 'from', 'to'));
    }

    public function buyers(Request $request)
    {
        $search   = $request->input('search');
        $sorteoId = $request->integer('sorteo_id') ?: null;
        $buyers   = $this->reports->buyerHistory($search, $sorteoId);
        $sorteos  = Sorteo::orderByDesc('id')->pluck('name', 'id');

        $this->setViewSharedData(['title_singular' => 'Reporte de Compradores']);

        $export = $request->input('export');

        if ($export === 'csv') {
            return $this->exportBuyersCsv($this->reports->buyerHistory($search, $sorteoId, 10000)->items());
        }

        if ($export === 'xlsx') {
            $all  = $this->reports->buyerHistory($search, $sorteoId, 10000)->items();
            $rows = array_map(fn($b) => [
                $b->buyer_name, $b->buyer_email, $b->buyer_phone,
                (int) $b->order_count, (float) $b->total_spent,
                $b->last_purchase, (int) $b->sorteos_count,
            ], $all);
            return $this->buildXlsx(
                ['Nombre', 'Email', 'Teléfono', 'Órdenes', 'Total Gastado (MXN)', 'Último Pedido', 'Sorteos'],
                $rows,
                'reporte-compradores-' . now()->format('Ymd') . '.xlsx'
            );
        }

        return view('Sorteos::reports.buyers', compact('buyers', 'sorteos', 'sorteoId', 'search'));
    }

    public function paymentMethods(Request $request)
    {
        $sorteoId = $request->integer('sorteo_id') ?: null;
        $data     = $this->reports->paymentMethodBreakdown($sorteoId);
        $sorteos  = Sorteo::orderByDesc('id')->pluck('name', 'id');

        $this->setViewSharedData(['title_singular' => 'Reporte por Método de Pago']);

        $export = $request->input('export');

        if ($export === 'csv') {
            return $this->exportPaymentsCsv($data);
        }

        if ($export === 'xlsx') {
            $rows = $data->map(fn($r) => [$r->payment_method, (int) $r->count, (float) $r->revenue])->toArray();
            return $this->buildXlsx(
                ['Método de Pago', 'Órdenes', 'Ingresos (MXN)'],
                $rows,
                'reporte-pagos-' . now()->format('Ymd') . '.xlsx'
            );
        }

        return view('Sorteos::reports.payment_methods', compact('data', 'sorteos', 'sorteoId'));
    }

    public function geographic(Request $request)
    {
        $sorteoId = $request->integer('sorteo_id') ?: null;
        $data     = $this->reports->geographicBreakdown($sorteoId);
        $sorteos  = Sorteo::orderByDesc('id')->pluck('name', 'id');

        $this->setViewSharedData(['title_singular' => 'Reporte Geográfico']);

        $export = $request->input('export');

        if ($export === 'csv') {
            return $this->exportGeographicCsv($data);
        }

        if ($export === 'xlsx') {
            $rows = $data->map(fn($r) => [$r->state, $r->city, (int) $r->orders_count, (float) $r->revenue])->toArray();
            return $this->buildXlsx(
                ['Estado / Región', 'Ciudad', 'Órdenes', 'Ingresos (MXN)'],
                $rows,
                'reporte-geografico-' . now()->format('Ymd') . '.xlsx'
            );
        }

        return view('Sorteos::reports.geographic', compact('data', 'sorteos', 'sorteoId'));
    }

    // ── XLSX export ───────────────────────────────────────────────────────────

    private function buildXlsx(array $headers, array $rows, string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $sheet->fromArray([$headers, ...$rows], null, 'A1');

        // Bold header row
        $sheet->getStyle('1')->getFont()->setBold(true);

        // Auto-size columns
        foreach (range('A', chr(ord('A') + count($headers) - 1)) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    // ── CSV exports ───────────────────────────────────────────────────────────

    private function exportSalesCsv(array $data, Carbon $from, Carbon $to): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $rows = array_map(null, $data['labels'], $data['orders'], $data['revenue'], $data['tickets']);

        return response()->streamDownload(function () use ($rows) {
            $csv = Writer::createFromFileObject(new \SplTempFileObject());
            $csv->insertOne(['Período', 'Órdenes', 'Ingresos (MXN)', 'Boletos Vendidos']);
            foreach ($rows as [$label, $orders, $revenue, $tickets]) {
                $csv->insertOne([$label, $orders, number_format($revenue, 2, '.', ''), $tickets]);
            }
            echo $csv->toString();
        }, 'reporte-ventas-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function exportBuyersCsv(array $buyers): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () use ($buyers) {
            $csv = Writer::createFromFileObject(new \SplTempFileObject());
            $csv->insertOne(['Nombre', 'Email', 'Teléfono', 'Órdenes', 'Total Gastado (MXN)', 'Último Pedido', 'Sorteos']);
            foreach ($buyers as $b) {
                $csv->insertOne([
                    $b->buyer_name, $b->buyer_email, $b->buyer_phone,
                    $b->order_count, number_format($b->total_spent, 2, '.', ''),
                    $b->last_purchase, $b->sorteos_count,
                ]);
            }
            echo $csv->toString();
        }, 'reporte-compradores-' . now()->format('Ymd') . '.csv', ['Content-Type' => 'text/csv']);
    }

    private function exportPaymentsCsv(\Illuminate\Support\Collection $data): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () use ($data) {
            $csv = Writer::createFromFileObject(new \SplTempFileObject());
            $csv->insertOne(['Método de Pago', 'Órdenes', 'Ingresos (MXN)']);
            foreach ($data as $row) {
                $csv->insertOne([$row->payment_method, $row->count, number_format($row->revenue, 2, '.', '')]);
            }
            echo $csv->toString();
        }, 'reporte-pagos-' . now()->format('Ymd') . '.csv', ['Content-Type' => 'text/csv']);
    }

    private function exportGeographicCsv(\Illuminate\Support\Collection $data): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () use ($data) {
            $csv = Writer::createFromFileObject(new \SplTempFileObject());
            $csv->insertOne(['Estado / Región', 'Ciudad', 'Órdenes', 'Ingresos (MXN)']);
            foreach ($data as $row) {
                $csv->insertOne([$row->state, $row->city, $row->orders_count, number_format($row->revenue, 2, '.', '')]);
            }
            echo $csv->toString();
        }, 'reporte-geografico-' . now()->format('Ymd') . '.csv', ['Content-Type' => 'text/csv']);
    }

    // ── PDF export ────────────────────────────────────────────────────────────

    private function exportSalesPdf(array $data, Carbon $from, Carbon $to, string $period): \Illuminate\Http\Response
    {
        $pdf = Pdf::loadView('Sorteos::reports.pdf_sales', compact('data', 'from', 'to', 'period'))
            ->setPaper('letter', 'landscape');

        return $pdf->download('reporte-ventas-' . $from->format('Ymd') . '.pdf');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function parseFilters(Request $request): array
    {
        $from     = Carbon::parse($request->input('from', now()->startOfMonth()->toDateString()));
        $to       = Carbon::parse($request->input('to', now()->toDateString()));
        $period   = $request->input('period', 'day');
        $sorteoId = $request->integer('sorteo_id') ?: null;

        return [$from, $to, $period, $sorteoId];
    }
}
